@props(['ticket'])

@php
    $priority    = $ticket->priority?->level ?? 'low';
    $status      = $ticket->status;
    $isSupport   = auth()->user()->isItSupport();
    $isSupervisor = auth()->user()->isItSupervisor();

    // Status yang tersedia berdasarkan status sekarang
    $availableStatuses = match($status) {
        'open'        => ['in_progress' => 'In Progress'],
        'in_progress' => ['pending' => 'Pending', 'resolved' => 'Resolved'],
        'pending'     => ['in_progress' => 'In Progress'],
        'resolved'    => ['closed' => 'Closed'],
        default       => [],
    };

    // Note required untuk status tertentu
    $noteRequired = ['pending'];
@endphp

<div class="ticket-card priority-{{ $priority }} status-{{ $status }}">

    {{-- Header --}}
    <div class="ticket-card-header">
        <div class="ticket-card-left">
            <span class="ticket-number">{{ $ticket->ticket_number }}</span>
            <span class="ticket-title">{{ $ticket->title }}</span>
        </div>
        <span class="ticket-response-time">
            {{ $ticket->created_at->format('d M Y, H:i') }}
        </span>
    </div>

    {{-- Meta --}}
    <div class="ticket-card-meta">
        <span class="ticket-meta-item">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
            </svg>
            {{ $ticket->category?->name ?? 'Uncategorized' }}
        </span>

        <span class="ticket-meta-item">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ $ticket->created_at->diffForHumans() }}
        </span>

        @if($ticket->slaRecord)
            <x-ui.sla-timer :ticket="$ticket" />
        @endif
    </div>

    {{-- Description --}}
    <p class="ticket-card-description">{{ $ticket->description }}</p>

    {{-- Footer --}}
    <div class="ticket-card-footer">
        {{-- Reporter --}}
        <div class="ticket-card-reporter">
            <div class="reporter-avatar">
                {{ strtoupper(substr($ticket->reporter->name, 0, 1)) }}
            </div>
            <div>
                <div class="reporter-name">{{ $ticket->reporter->name }}</div>
                <div class="reporter-dept">{{ $ticket->reporter->department?->name ?? '-' }}</div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="ticket-card-actions">

            {{-- Priority Dropdown (hanya support) --}}
            @if($isSupport && !in_array($status, ['resolved', 'closed']))
                <div class="quick-action-wrapper">
                    <button class="quick-action-btn {{ $priority }}"
                            onclick="toggleDropdown('priority-{{ $ticket->id }}')">
                        {{ ucfirst($priority) }} Priority
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div class="quick-dropdown" id="dropdown-priority-{{ $ticket->id }}">
                        @foreach(\App\Models\Priority::all() as $p)
                            <button class="quick-dropdown-item {{ $p->level === $priority ? 'disabled' : '' }}"
                                    onclick="openPriorityModal({{ $ticket->id }}, {{ $p->id }}, '{{ ucfirst($p->level) }}')">
                                <span class="status-dot dot-{{ $p->level }}"></span>
                                {{ $p->name }}
                                @if($p->level === $priority) <span style="margin-left:auto; font-size:10px;">✓</span> @endif
                            </button>
                        @endforeach
                    </div>
                </div>
            @else
                <x-ui.badge-priority :priority="$priority" />
            @endif

            {{-- Status Dropdown (hanya support) --}}
            @if($isSupport && count($availableStatuses) > 0)
                <div class="quick-action-wrapper">
                    <button class="quick-action-btn {{ $status }}"
                            onclick="toggleDropdown('status-{{ $ticket->id }}')">
                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div class="quick-dropdown" id="dropdown-status-{{ $ticket->id }}">
                        @foreach($availableStatuses as $statusKey => $statusLabel)
                            <button class="quick-dropdown-item"
                                    onclick="openStatusModal(
                                        {{ $ticket->id }},
                                        '{{ $statusKey }}',
                                        '{{ $statusLabel }}',
                                        {{ in_array($statusKey, $noteRequired) ? 'true' : 'false' }}
                                    )">
                                <span class="status-dot dot-{{ $statusKey }}"></span>
                                {{ $statusLabel }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @else
                <x-ui.badge-status :status="$status" />
            @endif

            {{-- Detail Button --}}
            @php
                $detailRoute = match(auth()->user()->role) {
                    'it_support'    => route('support.tickets.show', $ticket),
                    'it_supervisor' => route('supervisor.tickets.show', $ticket),
                    default         => route('user.tickets.show', $ticket),
                };
            @endphp

            <a href="{{ $detailRoute }}" class="btn btn-primary btn-sm">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                Detail
            </a>
        </div>
    </div>
</div>
