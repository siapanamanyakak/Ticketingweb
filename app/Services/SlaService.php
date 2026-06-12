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
    // ── Hitung deadline dengan working hours ──────
    public function calculateDeadline(Carbon $startTime, int $targetMinutes): Carbon
    {
        $workSchedules  = WorkSchedule::where('is_working_day', true)->get()->keyBy('day_of_week');
        $remainingMinutes = $targetMinutes;
        $current        = $startTime->copy();

        while ($remainingMinutes > 0) {
            $dayOfWeek = $current->dayOfWeek;
            $schedule  = $workSchedules->get($dayOfWeek);

            // Bukan hari kerja
            if (!$schedule) {
                $current->addDay()->startOfDay();
                continue;
            }

            $workStart = $current->copy()->setTimeFromTimeString($schedule->start_time);
            $workEnd   = $current->copy()->setTimeFromTimeString($schedule->end_time);

            // Sebelum jam kerja
            if ($current->lt($workStart)) {
                $current = $workStart->copy();
            }

            // Sesudah jam kerja
            if ($current->gte($workEnd)) {
                $current->addDay()->startOfDay();
                continue;
            }

            $minutesLeftToday = $current->diffInMinutes($workEnd);

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
    public function createSlaRecord(Ticket $ticket): SlaRecord
    {
        $sla = \App\Models\Sla::whereHas('priority', fn($q) => $q->where('id', $ticket->priority_id))->firstOrFail();

        $startTime = now();
        $isWorking = $this->isWorkingHours();

        if (!$isWorking) {
            $startTime = $this->getNextWorkingTime();
        }

        // Response deadline dihitung dari created_at
        $responseDeadline = $this->calculateDeadline($startTime->copy(), $sla->response_time);

        // Resolution deadline BELUM dihitung sekarang
        // Akan dihitung ulang saat first_response_at terisi (status → in_progress)
        // Sementara set dari created_at + response + resolution
        $resolutionDeadline = $this->calculateDeadline($startTime->copy(), $sla->response_time + $sla->resolution_time);

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
        // Cek apakah sudah ada pause yang aktif
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

    // ── Resume SLA ────────────────────────────────
    public function resumeSla(Ticket $ticket): void
    {
        $activePause = SlaPause::where('ticket_id', $ticket->id)
                            ->whereNull('resumed_at')
                            ->first();

        if (!$activePause) return;

        $duration = $activePause->paused_at->diffInMinutes(now());

        $activePause->update([
            'resumed_at'       => now(),
            'duration_minutes' => $duration,
        ]);

        $slaRecord = $ticket->slaRecord;
        if (!$slaRecord) return;

        // Update total paused minutes
        $newTotalPaused = $slaRecord->total_paused_minutes + $duration;

        // Extend deadline sebesar durasi pause — ini yang penting!
        // Jangan recalculate dari awal, cukup tambahkan durasi pause ke deadline existing
        $newResolutionDeadline = $slaRecord->resolution_deadline->copy()->addMinutes($duration);

        // Response deadline hanya di-extend kalau belum direspon
        $newResponseDeadline = $slaRecord->response_met_at
            ? $slaRecord->response_deadline
            : $slaRecord->response_deadline->copy()->addMinutes($duration);

        // PENTING: Jangan ubah breach status yang sudah true
        // Kalau sudah breach sebelum pause, tetap breach
        $slaRecord->update([
            'total_paused_minutes' => $newTotalPaused,
            'resolution_deadline'  => $newResolutionDeadline,
            'response_deadline'    => $newResponseDeadline,
            // resolution_breached TIDAK diubah di sini
        ]);
    }

    public function recalculateSla(Ticket $ticket): void
{
    $slaRecord = $ticket->slaRecord;
    if (!$slaRecord) return;

    $sla = \App\Models\Sla::whereHas('priority', fn($q) => $q->where('id', $ticket->priority_id))->first();
    if (!$sla) return;

    // Hitung ulang dari waktu tiket dibuat + total paused minutes
    $startTime = $ticket->created_at->copy();

    // Kalau sudah ada response, recalculate resolution dari first_response_at
    $resolutionStart = $ticket->first_response_at ?? $ticket->created_at;

    // Tambahkan total paused minutes ke deadline
    $newResolutionDeadline = $this->calculateDeadline(
        $resolutionStart->copy(),
        $sla->resolution_time
    );

    // Tambahkan total paused minutes
    if ($slaRecord->total_paused_minutes > 0) {
        $newResolutionDeadline->addMinutes($slaRecord->total_paused_minutes);
    }

    $newResponseDeadline = $this->calculateDeadline(
        $ticket->created_at->copy(),
        $sla->response_time
    );

    $slaRecord->update([
        'response_deadline'   => $slaRecord->response_met_at ? $slaRecord->response_deadline : $newResponseDeadline,
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

            if ($i === 0 && $current->between($workStart, $workEnd)) {
                return $current;
            }

            if ($i === 0 && $current->lt($workStart)) {
                return $workStart;
            }

            if ($i > 0) {
                return $workStart;
            }
        }

        return $current;
    }

    // ── Cek & update breach status ────────────────
    public function checkBreach(Ticket $ticket): void
    {
        $slaRecord = $ticket->slaRecord;
        if (!$slaRecord) return;

        $now = now();

        if (!$slaRecord->response_breached && !$slaRecord->response_met_at && $now->gt($slaRecord->response_deadline)) {
            $slaRecord->update(['response_breached' => true]);
        }

        if (!$slaRecord->resolution_breached && !in_array($ticket->status, ['resolved', 'closed']) && $now->gt($slaRecord->resolution_deadline)) {
            $slaRecord->update(['resolution_breached' => true]);
        }
    }

    // ── Ambil sisa waktu SLA ──────────────────────
    public function getRemainingTime(Ticket $ticket): array
    {
        $slaRecord = $ticket->slaRecord;
        if (!$slaRecord) return [];

        $now      = now();
        $deadline = $slaRecord->resolution_deadline;
        $diff     = $now->diff($deadline);

        return [
            'is_breached' => $now->gt($deadline),
            'is_paused'   => SlaPause::where('ticket_id', $ticket->id)->whereNull('resumed_at')->exists(),
            'deadline'    => $deadline,
            'hours'       => ($diff->days * 24) + $diff->h,
            'minutes'     => $diff->i,
        ];
    }
}
