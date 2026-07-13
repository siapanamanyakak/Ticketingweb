<x-layout.app title="Ticket Details" pageTitle="Ticket Details">

    <div class="page-header">
        <div style="display:flex; align-items:center; gap:12px;">
            <a href="{{ route('user.tickets.index') }}" class="btn btn-secondary btn-sm">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back
            </a>
            <div>
                <h1 class="page-title">{{ $ticket->ticket_number }}</h1>
                <p class="page-subtitle">{{ $ticket->title }}</p>
            </div>
        </div>
    </div>

    <div class="ticket-detail-wrapper">

        {{-- KOLOM KIRI --}}
        <div>

            {{-- Info Header --}}
            <div class="ticket-info-header">
                <div class="ticket-info-top">
                    <div class="ticket-info-number">
                        <div class="ticket-status-dot {{ $ticket->status }}"></div>
                        <span class="ticket-info-id">{{ $ticket->ticket_number }}</span>
                        <x-ui.badge-status :status="$ticket->status" />
                        <x-ui.badge-priority :priority="$ticket->priority?->level ?? 'low'" />
                    </div>
                </div>

                <div class="ticket-timeline">
                    <div class="timeline-item">
                        <span class="timeline-label">Reported</span>
                        <span class="timeline-value">{{ $ticket->created_at->format('d M Y, H:i') }}</span>
                    </div>
                    <div class="timeline-item">
                        <span class="timeline-label">Responded</span>
                        <span class="timeline-value {{ !$ticket->first_response_at ? 'empty' : '' }}">
                            {{ $ticket->first_response_at?->format('d M Y, H:i') ?? '—' }}
                        </span>
                    </div>
                    <div class="timeline-item">
                        <span class="timeline-label">Resolved</span>
                        <span class="timeline-value {{ !$ticket->resolved_at ? 'empty' : '' }}">
                            {{ $ticket->resolved_at?->format('d M Y, H:i') ?? '—' }}
                        </span>
                    </div>
                    <div class="timeline-item">
                        <span class="timeline-label">Closed</span>
                        <span class="timeline-value {{ !$ticket->closed_at ? 'empty' : '' }}">
                            {{ $ticket->closed_at?->format('d M Y, H:i') ?? '—' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Konten Tiket --}}
            <div class="ticket-content">
                <div class="ticket-category-label">{{ $ticket->category?->name ?? 'Uncategorized' }}</div>
                <p class="ticket-description">{{ $ticket->description }}</p>

                @if($ticket->attachment)
                    <div class="ticket-attachment">
                        @php $ext = pathinfo($ticket->attachment, PATHINFO_EXTENSION); @endphp
                        @if(in_array(strtolower($ext), ['jpg','jpeg','png']))
                            <div class="attachment-preview">
                                <img src="{{ asset('storage/' . $ticket->attachment) }}" alt="Lampiran">
                            </div>
                        @else
                            <a href="{{ asset('storage/' . $ticket->attachment) }}" target="_blank" class="attachment-file">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                </svg>
                                View Attachment
                            </a>
                        @endif
                    </div>
                @endif
            </div>

        {{-- Comments --}}
        <div class="comments-section" style="margin-bottom:0;">
            <div class="comments-title">
                💬 Comments ({{ $ticket->comments->whereNull('parent_id')->count() }})
            </div>

            {{-- Scrollable list --}}
            <div class="comments-list">
                @if($ticket->comments->whereNull('parent_id')->count() > 0)
                    @foreach($ticket->comments->whereNull('parent_id') as $comment)
                        <x-ticket.comment :comment="$comment" :ticketId="$ticket->id" />
                    @endforeach
                @else
                    <p style="font-size:13px; color:var(--gray-400); text-align:center; padding:20px 0;">
                        No comments yet.
                    </p>
                @endif
            </div>

            {{-- Form tetap di bawah, tidak ikut scroll --}}
            @if(!in_array($ticket->status, ['closed']))
                <div class="comment-form">
                    <form method="POST" action="{{ route('user.tickets.comments.store', $ticket) }}">
                        @csrf
                        <textarea name="comment"
                                placeholder="Write a comment or update information for the reporter..."
                                required></textarea>
                        <div class="comment-form-footer">
                            <button type="submit" class="btn btn-primary btn-sm">Send Comment</button>
                        </div>
                    </form>
                </div>
            @endif
        </div>

        </div>{{-- END KOLOM KIRI --}}

        {{-- KOLOM KANAN --}}
        <div class="ticket-sidebar-panel">

            {{-- SLA Info --}}
            @if($ticket->slaRecord)
                <div class="panel-card">
                    <div class="panel-card-title">⏱ SLA Details</div>
                    <x-ui.sla-timer :ticket="$ticket" :timeData="$slaRemaining" />
                    <div style="margin-top:12px;">
                        <div class="panel-row">
                            <span class="panel-row-label">Response Deadline</span>
                            <span class="panel-row-value" style="font-size:11px;">
                                {{ $ticket->slaRecord->response_deadline?->format('d M, H:i') ?? '—' }}
                            </span>
                        </div>
                        <div class="panel-row">
                            <span class="panel-row-label">Resolution Deadline</span>
                            <span class="panel-row-value" style="font-size:11px;">
                                {{ $ticket->slaRecord->resolution_deadline?->format('d M, H:i') ?? '—' }}
                            </span>
                        </div>
                        <div class="panel-row">
                            <span class="panel-row-label">Response Status</span>
                            <span class="panel-row-value">
                                @if($ticket->slaRecord->response_met_at)
                                    @if($ticket->slaRecord->response_breached)
                                        <span style="color:#dc2626; font-size:11px;">Late</span>
                                    @else
                                        <span style="color:#16a34a; font-size:11px;">On Time</span>
                                    @endif
                                @else
                                    <span style="color:#d97706; font-size:11px;">Wait For Response</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Info Tiket --}}
            <div class="panel-card">
                <div class="panel-card-title">📋 Ticket Information</div>
                <div class="panel-row">
                    <span class="panel-row-label">Number</span>
                    <span class="panel-row-value">{{ $ticket->ticket_number }}</span>
                </div>
                <div class="panel-row">
                    <span class="panel-row-label">Category</span>
                    <span class="panel-row-value">{{ $ticket->category?->name ?? '—' }}</span>
                </div>
                <div class="panel-row">
                    <span class="panel-row-label">Priority</span>
                    <span class="panel-row-value">
                        <x-ui.badge-priority :priority="$ticket->priority?->level ?? 'low'" />
                    </span>
                </div>
                <div class="panel-row">
                    <span class="panel-row-label">Status</span>
                    <span class="panel-row-value">
                        <x-ui.badge-status :status="$ticket->status" />
                    </span>
                </div>
                @if($ticket->had_pending)
                    <div class="panel-row">
                        <span class="panel-row-label">Has Been Pending</span>
                        <span class="panel-row-value" style="color:#d97706;">
                            {{ $ticket->pending_count }}x
                        </span>
                    </div>
                @endif
            </div>

            {{-- Riwayat --}}
            <div class="panel-card">
                <div class="panel-card-title">
                    📜 Log ({{ $ticket->logs->count() }}x changes)
                </div>

                @if($ticket->logs->count() > 0)
                    {{-- Scrollable log wrapper --}}
                    <div class="ticket-log-scrollable">
                        <div class="ticket-log">
                            @foreach($ticket->logs->sortByDesc('created_at') as $log)
                                <x-ticket.log-item
                                    :log="$log"
                                    :canViewNote="auth()->user()->role === 'it_support'"
                                />
                            @endforeach
                        </div>
                    </div>
                @else
                    <p style="font-size:12px; color:var(--gray-400); text-align:center; padding:12px 0;">
                        No history yet.
                    </p>
                @endif
            </div>

        </div>{{-- END KOLOM KANAN --}}

    </div>{{-- END ticket-detail-wrapper --}}

</x-layout.app>
