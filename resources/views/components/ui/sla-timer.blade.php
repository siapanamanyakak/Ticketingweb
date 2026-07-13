@props(['ticket'])

@php
    $slaService = app(\App\Services\SlaService::class);
    $slaRecord  = $ticket->slaRecord;

    $isPaused   = $ticket->slaPauses()->whereNull('resumed_at')->exists();
    $isResolved = in_array($ticket->status, ['resolved', 'closed']);
    $hasResponded = !is_null($ticket->first_response_at);
    $isOutsideHours = !$slaService->isWorkingHours();

    // Fase: response atau resolution
    $phase      = $hasResponded ? 'resolution' : 'response';
    $phaseLabel = $phase === 'response' ? 'Response' : 'Resolution';

    // Sisa menit kerja aktif (integer, bukan kalender)
    $remainingMinutes = 0;
    $totalSlaMinutes  = 0;
    $percentage       = 100;
    $isBreached       = false;

    if ($slaRecord && !$isResolved) {
        $remainingMinutes = $slaService->getRemainingWorkingMinutes($ticket, $phase);
        $totalSlaMinutes  = $slaService->getTotalSlaMinutes($ticket, $phase);
        $percentage       = $totalSlaMinutes > 0
            ? round(($remainingMinutes / $totalSlaMinutes) * 100)
            : 0;

        // Cek breach
        $deadline   = $phase === 'response'
            ? $slaRecord->response_deadline
            : $slaRecord->resolution_deadline;
        $isBreached = $slaRecord->resolution_breached
            || ($deadline && now()->gt($deadline));
    }

    // ATURAN 4: Class berbasis persentase waktu kerja murni
    $timerClass = match(true) {
        $isResolved                => $slaRecord?->resolution_breached ? 'breached' : 'on-time',
        $isPaused || $isOutsideHours => 'paused',
        $isBreached                => 'breached',
        $percentage <= 15          => 'danger',
        $percentage <= 50          => 'warning',
        default                    => 'on-time',
    };

    // Label pause
    $pauseLabel = $isOutsideHours && !$isPaused
        ? 'SLA Paused (Outside Hours)'
        : 'SLA Paused (Pending)';
@endphp

@if($slaRecord)
    <div class="sla-timer {{ $timerClass }}"
         id="slaTimer{{ $ticket->id }}"
         data-paused="{{ ($isPaused || $isOutsideHours) ? 'true' : 'false' }}"
         data-resolved="{{ $isResolved ? 'true' : 'false' }}"
         data-breached="{{ $isBreached ? 'true' : 'false' }}"
         data-phase="{{ $phase }}"
         data-remaining="{{ $remainingMinutes }}"
         data-total="{{ $totalSlaMinutes }}"
         data-percentage="{{ $percentage }}">

        @if($isResolved)
            {{-- Resolved: tampilkan hasil SLA --}}
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>
                @if($slaRecord->resolution_breached)
                    SLA Breached
                @else
                    SLA Fulfilled
                @endif
            </span>

        @elseif($isPaused || $isOutsideHours)
            {{-- ATURAN 4: Paused — teks tegas, JS berhenti --}}
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ $pauseLabel }}</span>

        @elseif($isBreached)
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ $phaseLabel }} Breached!</span>

        @else
            {{-- Active countdown --}}
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="sla-countdown"
                  data-remaining="{{ $remainingMinutes }}"
                  data-total="{{ $totalSlaMinutes }}"
                  data-label="{{ $phaseLabel }}"
                  data-timer-id="{{ $ticket->id }}">
                {{ $phaseLabel }}: {{ $remainingMinutes }}m
            </span>
        @endif
    </div>

    @once
        @push('scripts')
        <script>
        // ── ATURAN 4: JS hanya kurangi integer — bukan hitung dari deadline kalender ──
        const slaTimers = {};

        function formatMinutes(totalMins) {
            if (totalMins <= 0) return '0m';
            const days  = Math.floor(totalMins / (60 * 24));
            const hours = Math.floor((totalMins % (60 * 24)) / 60);
            const mins  = totalMins % 60;
            let parts = [];
            if (days  > 0) parts.push(`${days}d`);
            if (hours > 0) parts.push(`${hours}h`);
            if (mins  > 0) parts.push(`${mins}m`);
            return parts.join(' ') || '0m';
        }

        function getTimerClass(percentage, isBreached) {
            if (isBreached || percentage <= 0) return 'sla-timer breached';
            if (percentage <= 15)              return 'sla-timer danger';
            if (percentage <= 50)              return 'sla-timer warning';
            return 'sla-timer on-time';
        }

        function tickSlaTimer(el) {
            const timer      = el.closest('.sla-timer');
            const paused     = timer.dataset.paused === 'true';
            const resolved   = timer.dataset.resolved === 'true';
            const label      = el.dataset.label;
            const total      = parseInt(el.dataset.total) || 1;
            const timerId    = el.dataset.timerId;

            // ATURAN 4: Hentikan JS kalau paused atau resolved
            if (paused || resolved) return;

            // Ambil sisa menit dari state JS (bukan dari dataset terus)
            if (!slaTimers[timerId]) {
                slaTimers[timerId] = parseInt(el.dataset.remaining) || 0;
            }

            // Kurangi 1 menit setiap tick
            slaTimers[timerId] = Math.max(0, slaTimers[timerId] - 1);
            const remaining  = slaTimers[timerId];
            const percentage = Math.round((remaining / total) * 100);

            if (remaining <= 0) {
                timer.className = 'sla-timer breached';
                timer.innerHTML = `
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:14px;height:14px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>${label} Breached!</span>
                `;
                return;
            }

            // Update display
            el.textContent = `${label}: ${formatMinutes(remaining)}`;

            // Update class berbasis persentase
            timer.className = getTimerClass(percentage, false);
        }

        function initSlaTimers() {
            document.querySelectorAll('.sla-countdown').forEach(el => {
                const timerId = el.dataset.timerId;
                // Inisialisasi state dari data attribute PHP
                if (!slaTimers[timerId]) {
                    slaTimers[timerId] = parseInt(el.dataset.remaining) || 0;
                }
                // Render awal
                const total      = parseInt(el.dataset.total) || 1;
                const remaining  = slaTimers[timerId];
                const percentage = Math.round((remaining / total) * 100);
                const label      = el.dataset.label;
                el.textContent   = `${label}: ${formatMinutes(remaining)}`;

                const timer = el.closest('.sla-timer');
                timer.className = getTimerClass(percentage, timer.dataset.breached === 'true');
            });
        }

        // Jalankan tick setiap 60 detik
        initSlaTimers();
        setInterval(() => {
            document.querySelectorAll('.sla-countdown').forEach(el => tickSlaTimer(el));
        }, 60000);
        </script>
        @endpush
    @endonce
@endif
