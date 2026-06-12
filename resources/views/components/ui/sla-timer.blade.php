@props(['ticket'])

@php
    $slaRecord  = $ticket->slaRecord;
    $isPaused   = $ticket->slaPauses()->whereNull('resumed_at')->exists();
    $isResolved = in_array($ticket->status, ['resolved', 'closed']);
    $hasResponded = !is_null($ticket->first_response_at);

    // Tentukan fase: response atau resolution
    $phase    = $hasResponded ? 'resolution' : 'response';
    $deadline = $phase === 'response'
                ? $slaRecord?->response_deadline
                : $slaRecord?->resolution_deadline;

    $isBreached = false;
    if ($slaRecord && !$isResolved && !$isPaused) {
        $isBreached = $phase === 'response'
            ? (!$hasResponded && now()->gt($slaRecord->response_deadline))
            : ($slaRecord->resolution_breached || now()->gt($slaRecord->resolution_deadline));
    }

    $timerClass = match(true) {
        $isResolved => $slaRecord?->resolution_breached ? 'breached' : 'on-time',
        $isPaused   => 'paused',
        $isBreached => 'breached',
        $deadline && now()->diffInHours($deadline, false) <= 2 => 'warning',
        default     => 'on-time',
    };

    $phaseLabel = $phase === 'response' ? 'Respon' : 'Selesai';
@endphp

@if($slaRecord)
    <div class="sla-timer {{ $timerClass }}"
         id="slaTimer{{ $ticket->id }}"
         data-deadline="{{ $deadline?->toISOString() }}"
         data-paused="{{ $isPaused ? 'true' : 'false' }}"
         data-resolved="{{ $isResolved ? 'true' : 'false' }}"
         data-breached="{{ $isBreached ? 'true' : 'false' }}"
         data-phase="{{ $phase }}">

        @if($isResolved)
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>
                @if($slaRecord->resolution_breached)
                    SLA Terlewat
                @else
                    SLA Terpenuhi
                @endif
            </span>

        @elseif($isPaused)
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>SLA Dijeda</span>

        @elseif($isBreached)
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ $phaseLabel }} Terlewat!</span>

        @else
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="sla-countdown"
                  data-deadline="{{ $deadline?->toISOString() }}"
                  data-phase="{{ $phase }}"
                  data-label="{{ $phaseLabel }}">
                Menghitung...
            </span>
        @endif
    </div>

    @once
        @push('scripts')
        <script>
            function updateSlaCountdowns() {
                document.querySelectorAll('.sla-countdown').forEach(el => {
                    const timer    = el.closest('.sla-timer');
                    const paused   = timer.dataset.paused === 'true';
                    const resolved = timer.dataset.resolved === 'true';
                    const phase    = el.dataset.phase;
                    const label    = el.dataset.label;

                    if (paused || resolved) return;

                    const deadline = new Date(el.dataset.deadline);
                    const now      = new Date();
                    const diff     = deadline - now;

                    if (diff <= 0) {
                        timer.className = 'sla-timer breached';
                        timer.innerHTML = `
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:14px;height:14px;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>${label} Terlewat!</span>
                        `;
                        return;
                    }

                    const totalHours = Math.floor(diff / 3600000);
                    const minutes    = Math.floor((diff % 3600000) / 60000);
                    const days       = Math.floor(totalHours / 24);
                    const hours      = totalHours % 24;

                    let display = '';
                    if (days > 0)       display = `${days}h ${hours}j ${minutes}m`;
                    else if (hours > 0) display = `${hours}j ${minutes}m`;
                    else                display = `${minutes}m`;

                    el.textContent = `${label}: ${display}`;

                    if (diff < 3600000) {
                        timer.className = 'sla-timer breached';
                    } else if (diff < 7200000) {
                        timer.className = 'sla-timer warning';
                    }
                });
            }

            updateSlaCountdowns();
            setInterval(updateSlaCountdowns, 60000);
        </script>
        @endpush
    @endonce
@endif
