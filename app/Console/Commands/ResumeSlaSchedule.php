<?php

namespace App\Console\Commands;

use App\Models\Sla;
use App\Models\SlaPause;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\SlaWarningNotification;
use App\Services\SlaService;
use Illuminate\Console\Command;

class ResumeSlaSchedule extends Command
{
    protected $signature   = 'sla:resume-schedule';
    protected $description = 'Auto-resume SLA dan kirim warning notifikasi';

    public function handle(SlaService $slaService): void
    {
        $this->resumeOutsideWorkingHours($slaService);
        $this->checkSlaWarnings();
    }

    private function resumeOutsideWorkingHours(SlaService $slaService): void
    {
        if (!$slaService->isWorkingHours()) {
            $this->info('Bukan jam kerja, skip resume.');
            return;
        }

        $activePauses = SlaPause::whereNull('resumed_at')
            ->where('reason', 'outside_working_hours')
            ->with('ticket')
            ->get();

        if ($activePauses->isEmpty()) {
            $this->info('Tidak ada SLA yang perlu di-resume.');
            return;
        }

        foreach ($activePauses as $pause) {
            $ticket = $pause->ticket;

            if (in_array($ticket->status, ['resolved', 'closed'])) {
                $pause->update([
                    'resumed_at'       => now(),
                    'duration_minutes' => $pause->paused_at->diffInMinutes(now()),
                ]);
                continue;
            }

            $slaService->resumeSla($ticket);
            $this->info("SLA resumed: {$ticket->ticket_number}");
        }
    }

    private function checkSlaWarnings(): void
    {
        // Ambil semua tiket aktif yang punya SLA record
        $tickets = Ticket::whereNotIn('status', ['resolved', 'closed'])
            ->whereHas('slaRecord')
            ->with(['slaRecord', 'priority'])
            ->get();

        $recipients = User::whereIn('role', ['it_support', 'it_supervisor'])
            ->where('is_active', true)
            ->get();

        foreach ($tickets as $ticket) {
            $slaRecord = $ticket->slaRecord;
            $sla       = Sla::whereHas('priority', fn($q) => $q->where('id', $ticket->priority_id))->first();

            if (!$sla) continue;

            // Cek apakah SLA sedang dijeda
            $isPaused = $ticket->slaPauses()->whereNull('resumed_at')->exists();
            if ($isPaused) continue;

            // ── Response Warning ──────────────────────
            if (!$slaRecord->response_met_at && $slaRecord->response_deadline) {
                $warningMinutes  = $sla->response_time * 0.10;
                $remainingMinutes = now()->diffInMinutes($slaRecord->response_deadline, false);

                // Sisa waktu dalam window warning (positif = belum breach)
                if ($remainingMinutes > 0 && $remainingMinutes <= $warningMinutes) {
                    // Cek apakah warning sudah pernah dikirim (pakai notifications table)
                    $alreadySent = $recipients->first()?->notifications()
                        ->where('data->type', 'sla_warning_response')
                        ->where('data->ticket_id', $ticket->id)
                        ->exists();

                    if (!$alreadySent) {
                        foreach ($recipients as $recipient) {
                            $recipient->notify(new SlaWarningNotification($ticket, 'response'));
                        }
                        $this->info("SLA response warning sent: {$ticket->ticket_number}");
                    }
                }
            }

            // ── Resolution Warning ────────────────────
            if (!$slaRecord->resolution_met_at && $slaRecord->resolution_deadline) {
                $warningMinutes   = $sla->resolution_time * 0.10;
                $remainingMinutes = now()->diffInMinutes($slaRecord->resolution_deadline, false);

                if ($remainingMinutes > 0 && $remainingMinutes <= $warningMinutes) {
                    $alreadySent = $recipients->first()?->notifications()
                        ->where('data->type', 'sla_warning_resolution')
                        ->where('data->ticket_id', $ticket->id)
                        ->exists();

                    if (!$alreadySent) {
                        foreach ($recipients as $recipient) {
                            $recipient->notify(new SlaWarningNotification($ticket, 'resolution'));
                        }
                        $this->info("SLA resolution warning sent: {$ticket->ticket_number}");
                    }
                }
            }
        }
    }
}
