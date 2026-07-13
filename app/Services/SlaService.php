<?php

namespace App\Services;

use App\Models\Sla;
use App\Models\SlaPause;
use App\Models\SlaRecord;
use App\Models\Ticket;
use App\Models\WorkSchedule;
use Carbon\Carbon;

class SlaService
{
    // ── ATURAN 1: Hitung menit kerja bersih antara dua waktu ──
    // Anti-jam kalender — hanya hitung menit dalam rentang jam operasional
    public function getWorkingMinutesBetween(Carbon $start, Carbon $end): int
    {
        if ($end->lte($start)) return 0;

        $workSchedules = WorkSchedule::where('is_working_day', true)
                                     ->get()
                                     ->keyBy('day_of_week');

        $total   = 0;
        $current = $start->copy();

        while ($current->lt($end)) {
            $dayOfWeek = $current->dayOfWeek;
            $schedule  = $workSchedules->get($dayOfWeek);

            // Bukan hari kerja — lompat ke hari berikutnya
            if (!$schedule) {
                $current->addDay()->startOfDay();
                continue;
            }

            $workStart = $current->copy()->setTimeFromTimeString($schedule->start_time);
            $workEnd   = $current->copy()->setTimeFromTimeString($schedule->end_time);

            // Posisi current sebelum jam kerja dimulai
            if ($current->lt($workStart)) {
                $current = $workStart->copy();
            }

            // Posisi current sudah melewati jam kerja hari ini
            if ($current->gte($workEnd)) {
                $current->addDay()->startOfDay();
                continue;
            }

            // Batas akhir hari ini: mana yang lebih dulu antara $end dan $workEnd
            $effectiveEnd = $end->lt($workEnd) ? $end : $workEnd;

            $minutesThisSegment = (int) $current->diffInMinutes($effectiveEnd);
            $total             += $minutesThisSegment;

            // Lanjut ke hari berikutnya
            $current = $workEnd->copy()->addSecond()->startOfDay();
            $current->addDay()->startOfDay();
        }

        return max(0, $total);
    }

    // ── Hitung deadline dengan working hours ──────
    public function calculateDeadline(Carbon $startTime, int $targetMinutes): Carbon
    {
        $workSchedules    = WorkSchedule::where('is_working_day', true)->get()->keyBy('day_of_week');
        $remainingMinutes = $targetMinutes;
        $current          = $startTime->copy();

        while ($remainingMinutes > 0) {
            $dayOfWeek = $current->dayOfWeek;
            $schedule  = $workSchedules->get($dayOfWeek);

            if (!$schedule) {
                $current->addDay()->startOfDay();
                continue;
            }

            $workStart = $current->copy()->setTimeFromTimeString($schedule->start_time);
            $workEnd   = $current->copy()->setTimeFromTimeString($schedule->end_time);

            if ($current->lt($workStart)) {
                $current = $workStart->copy();
            }

            if ($current->gte($workEnd)) {
                $current->addDay()->startOfDay();
                continue;
            }

            $minutesLeftToday = (int) $current->diffInMinutes($workEnd);

            if ($remainingMinutes <= $minutesLeftToday) {
                $current->addMinutes($remainingMinutes);
                $remainingMinutes = 0;
            } else {
                $remainingMinutes -= $minutesLeftToday;
                $current->addDay()->startOfDay();
            }
        }

        return $current;
    }

    // ── Cek apakah sekarang jam kerja ─────────────
    public function isWorkingHours(): bool
    {
        $now      = now();
        $schedule = WorkSchedule::where('day_of_week', $now->dayOfWeek)
                                ->where('is_working_day', true)
                                ->first();

        if (!$schedule) return false;

        $start = $now->copy()->setTimeFromTimeString($schedule->start_time);
        $end   = $now->copy()->setTimeFromTimeString($schedule->end_time);

        return $now->between($start, $end);
    }

    // ── Buat SLA record saat tiket dibuat ─────────
    // FONDASI UTAMA: response & resolution dihitung INDEPENDEN dari created_at
    public function createSlaRecord(Ticket $ticket): SlaRecord
    {
        $sla = Sla::whereHas('priority', fn($q) => $q->where('id', $ticket->priority_id))
                  ->firstOrFail();

        $isWorking = $this->isWorkingHours();
        $anchor    = $isWorking ? now() : $this->getNextWorkingTime();

        // Keduanya dihitung dari ANCHOR (created_at efektif) — INDEPENDEN
        $responseDeadline   = $this->calculateDeadline($anchor->copy(), $sla->response_time);
        $resolutionDeadline = $this->calculateDeadline($anchor->copy(), $sla->resolution_time);

        $record = SlaRecord::create([
            'ticket_id'            => $ticket->id,
            'response_deadline'    => $responseDeadline,
            'resolution_deadline'  => $resolutionDeadline,
            'total_paused_minutes' => 0,
            'response_breached'    => false,
            'resolution_breached'  => false,
        ]);

        if (!$isWorking) {
            SlaPause::create([
                'ticket_id' => $ticket->id,
                'reason'    => 'outside_working_hours',
                'paused_at' => now(),
            ]);
        }

        return $record;
    }

    // ── Pause SLA ─────────────────────────────────
    public function pauseSla(Ticket $ticket, string $reason): void
    {
        $activePause = SlaPause::where('ticket_id', $ticket->id)
                               ->whereNull('resumed_at')
                               ->first();

        if (!$activePause) {
            SlaPause::create([
                'ticket_id' => $ticket->id,
                'reason'    => $reason,
                'paused_at' => now(),
            ]);
        }
    }

    // ── Resume SLA — ATURAN 1: Gunakan getWorkingMinutesBetween ──
    public function resumeSla(Ticket $ticket): void
    {
        $activePause = SlaPause::where('ticket_id', $ticket->id)
                               ->whereNull('resumed_at')
                               ->first();

        if (!$activePause) return;

        // Hitung durasi pause dalam menit kerja BERSIH
        $workingPauseDuration = $this->getWorkingMinutesBetween(
            $activePause->paused_at,
            now()
        );

        $activePause->update([
            'resumed_at'       => now(),
            'duration_minutes' => $workingPauseDuration,
        ]);

        $slaRecord = $ticket->slaRecord;
        if (!$slaRecord) return;

        // Akumulasi total_paused_minutes dengan menit kerja bersih
        $newTotalPaused = $slaRecord->total_paused_minutes + $workingPauseDuration;

        // Extend deadline sebesar durasi menit kerja bersih
        $newResolutionDeadline = $this->calculateDeadline(
            $slaRecord->resolution_deadline->copy(),
            $workingPauseDuration
        );

        $newResponseDeadline = $slaRecord->response_met_at
            ? $slaRecord->response_deadline
            : $this->calculateDeadline(
                $slaRecord->response_deadline->copy(),
                $workingPauseDuration
            );

        // Breach sebelum pause TETAP breach — tidak di-reset
        $slaRecord->update([
            'total_paused_minutes' => $newTotalPaused,
            'resolution_deadline'  => $newResolutionDeadline,
            'response_deadline'    => $newResponseDeadline,
        ]);
    }

    // ── Recalculate SLA saat priority berubah ─────
    public function recalculateSla(Ticket $ticket): void
    {
        $slaRecord = $ticket->slaRecord;
        if (!$slaRecord) return;

        $sla = Sla::whereHas('priority', fn($q) => $q->where('id', $ticket->priority_id))->first();
        if (!$sla) return;

        // Anchor murni dari created_at — BUKAN first_response_at
        $anchor = $ticket->created_at->copy();

        // Hitung ulang dari nol dengan SLA baru
        $newResponseDeadline   = $this->calculateDeadline($anchor->copy(), $sla->response_time);
        $newResolutionDeadline = $this->calculateDeadline($anchor->copy(), $sla->resolution_time);

        // Kompensasi total pause yang sudah terjadi
        if ($slaRecord->total_paused_minutes > 0) {
            $newResponseDeadline   = $this->calculateDeadline(
                $newResponseDeadline->copy(),
                $slaRecord->total_paused_minutes
            );
            $newResolutionDeadline = $this->calculateDeadline(
                $newResolutionDeadline->copy(),
                $slaRecord->total_paused_minutes
            );
        }

        $slaRecord->update([
            // Response deadline hanya diupdate kalau belum direspon
            'response_deadline'   => $slaRecord->response_met_at
                ? $slaRecord->response_deadline
                : $newResponseDeadline,
            'resolution_deadline' => $newResolutionDeadline,
        ]);
    }

    // ── Cari jam kerja berikutnya ─────────────────
    public function getNextWorkingTime(): Carbon
    {
        $current   = now();
        $schedules = WorkSchedule::where('is_working_day', true)->get()->keyBy('day_of_week');

        for ($i = 0; $i < 7; $i++) {
            $day      = $current->copy()->addDays($i);
            $schedule = $schedules->get($day->dayOfWeek);

            if (!$schedule) continue;

            $workStart = $day->copy()->setTimeFromTimeString($schedule->start_time);
            $workEnd   = $day->copy()->setTimeFromTimeString($schedule->end_time);

            if ($i === 0 && $current->between($workStart, $workEnd)) return $current;
            if ($i === 0 && $current->lt($workStart)) return $workStart;
            if ($i > 0) return $workStart;
        }

        return $current;
    }

    // ── Hitung sisa menit kerja aktif ─────────────
    public function getRemainingWorkingMinutes(Ticket $ticket, string $phase = 'resolution'): int
    {
        $slaRecord = $ticket->slaRecord;
        if (!$slaRecord) return 0;

        $deadline = $phase === 'response'
            ? $slaRecord->response_deadline
            : $slaRecord->resolution_deadline;

        if (!$deadline || now()->gte($deadline)) return 0;

        return $this->getWorkingMinutesBetween(now(), $deadline);
    }

    // ── Total menit SLA untuk fase tertentu ───────
    public function getTotalSlaMinutes(Ticket $ticket, string $phase = 'resolution'): int
    {
        $sla = Sla::whereHas('priority', fn($q) => $q->where('id', $ticket->priority_id))->first();
        if (!$sla) return 0;

        return $phase === 'response' ? $sla->response_time : $sla->resolution_time;
    }
}
