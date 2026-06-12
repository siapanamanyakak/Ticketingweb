<x-layout.app title="Detail Tiket" pageTitle="Detail Tiket">

    <div class="page-header">
        <div style="display:flex; align-items:center; gap:12px;">
            <a href="{{ route('supervisor.tickets.index') }}" class="btn btn-secondary btn-sm">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali
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
                    <x-ui.sla-timer :ticket="$ticket" />
                </div>

                <div style="display:flex; align-items:center; gap:10px; margin-bottom:16px;">
                    <div class="reporter-avatar" style="width:40px;height:40px;font-size:14px;">
                        {{ strtoupper(substr($ticket->reporter->name, 0, 1)) }}
                    </div>
                    <div>
                        <div style="font-size:14px; font-weight:700; color:var(--gray-900);">
                            {{ $ticket->reporter->name }}
                        </div>
                        <div style="font-size:12px; color:var(--gray-500);">
                            {{ $ticket->reporter->department?->name ?? '-' }}
                            · {{ $ticket->reporter->id_staff ?? '-' }}
                        </div>
                    </div>
                </div>

                <div class="ticket-timeline">
                    <div class="timeline-item">
                        <span class="timeline-label">Dilaporkan</span>
                        <span class="timeline-value">{{ $ticket->created_at->format('d M Y, H:i') }}</span>
                    </div>
                    <div class="timeline-item">
                        <span class="timeline-label">Direspon</span>
                        <span class="timeline-value {{ !$ticket->first_response_at ? 'empty' : '' }}">
                            {{ $ticket->first_response_at?->format('d M Y, H:i') ?? '—' }}
                        </span>
                    </div>
                    <div class="timeline-item">
                        <span class="timeline-label">Diselesaikan</span>
                        <span class="timeline-value {{ !$ticket->resolved_at ? 'empty' : '' }}">
                            {{ $ticket->resolved_at?->format('d M Y, H:i') ?? '—' }}
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
                                Lihat Lampiran
                            </a>
                        @endif
                    </div>
                @endif

                {{-- Resolution Notes --}}
                @if($ticket->resolution_notes)
                    <div style="margin-top:20px; padding:16px; background:#dcfce7; border-radius:10px; border-left:4px solid #16a34a;">
                        <div style="font-size:12px; font-weight:700; color:#15803d; margin-bottom:6px; text-transform:uppercase; letter-spacing:0.5px;">
                            ✓ Catatan Penyelesaian
                        </div>
                        <p style="font-size:13px; color:#166534; line-height:1.6;">{{ $ticket->resolution_notes }}</p>
                        <div style="font-size:11px; color:#15803d; margin-top:8px; opacity:0.7;">
                            Diselesaikan: {{ $ticket->resolved_at?->format('d M Y, H:i') ?? '—' }}
                        </div>
                    </div>
                @endif
            </div>

    {{-- Comments --}}
    <div class="comments-section" style="margin-bottom:0;">
        <div class="comments-title">
            💬 Komentar ({{ $ticket->comments->whereNull('parent_id')->count() }})
        </div>

        {{-- Scrollable list --}}
        <div class="comments-list">
            @if($ticket->comments->whereNull('parent_id')->count() > 0)
                @foreach($ticket->comments->whereNull('parent_id') as $comment)
                    <x-ticket.comment :comment="$comment" :ticketId="$ticket->id" />
                @endforeach
            @else
                <p style="font-size:13px; color:var(--gray-400); text-align:center; padding:20px 0;">
                    Belum ada komentar.
                </p>
            @endif
        </div>

        </div>
        </div>{{-- END KOLOM KIRI --}}

        {{-- KOLOM KANAN --}}
        <div class="ticket-sidebar-panel">

            @if($ticket->slaRecord)
                <div class="panel-card">
                    <div class="panel-card-title">⏱ Detail SLA</div>
                    <x-ui.sla-timer :ticket="$ticket" />
                    <div style="margin-top:12px;">
                        <div class="panel-row">
                            <span class="panel-row-label">Deadline Respon</span>
                            <span class="panel-row-value" style="font-size:11px;">
                                {{ $ticket->slaRecord->response_deadline?->format('d M, H:i') ?? '—' }}
                            </span>
                        </div>
                        <div class="panel-row">
                            <span class="panel-row-label">Deadline Selesai</span>
                            <span class="panel-row-value" style="font-size:11px;">
                                {{ $ticket->slaRecord->resolution_deadline?->format('d M, H:i') ?? '—' }}
                            </span>
                        </div>
                        <div class="panel-row">
                            <span class="panel-row-label">Status Respon</span>
                            <span class="panel-row-value">
                                @if($ticket->slaRecord->response_met_at)
                                    @if($ticket->slaRecord->response_breached)
                                        <span style="color:#dc2626; font-size:11px;">Terlambat</span>
                                    @else
                                        <span style="color:#16a34a; font-size:11px;">Tepat Waktu</span>
                                    @endif
                                @else
                                    <span style="color:#d97706; font-size:11px;">Menunggu</span>
                                @endif
                            </span>
                        </div>
                        <div class="panel-row">
                            <span class="panel-row-label">Status Resolusi</span>
                            <span class="panel-row-value">
                                @if($ticket->slaRecord->resolution_met_at)
                                    @if($ticket->slaRecord->resolution_breached)
                                        <span style="color:#dc2626; font-size:11px;">Terlambat</span>
                                    @else
                                        <span style="color:#16a34a; font-size:11px;">Tepat Waktu</span>
                                    @endif
                                @else
                                    <span style="color:#d97706; font-size:11px;">Belum Selesai</span>
                                @endif
                            </span>
                        </div>
                        <div class="panel-row">
                            <span class="panel-row-label">Total Dijeda</span>
                            <span class="panel-row-value">{{ $ticket->slaRecord->total_paused_minutes }} menit</span>
                        </div>
                    </div>
                </div>
            @endif

            <div class="panel-card">
                <div class="panel-card-title">📋 Info Tiket</div>
                <div class="panel-row">
                    <span class="panel-row-label">Nomor</span>
                    <span class="panel-row-value">{{ $ticket->ticket_number }}</span>
                </div>
                <div class="panel-row">
                    <span class="panel-row-label">Kategori</span>
                    <span class="panel-row-value">{{ $ticket->category?->name ?? '—' }}</span>
                </div>
                <div class="panel-row">
                    <span class="panel-row-label">Prioritas</span>
                    <span class="panel-row-value">
                        <x-ui.badge-priority :priority="$ticket->priority?->level ?? 'low'" />
                    </span>
                </div>
                <div class="panel-row">
                    <span class="panel-row-label">Pernah Pending</span>
                    <span class="panel-row-value" style="{{ $ticket->had_pending ? 'color:#d97706;' : '' }}">
                        {{ $ticket->had_pending ? 'Ya (' . $ticket->pending_count . 'x)' : 'Tidak' }}
                    </span>
                </div>
                @if($ticket->had_pending)
                    <div class="panel-row">
                        <span class="panel-row-label">Durasi Pending</span>
                        <span class="panel-row-value">{{ $ticket->pending_duration }} menit</span>
                    </div>
                @endif
            </div>

        <div class="panel-card">
            <div class="panel-card-title">
                📜 Log ({{ $ticket->logs->count() }}x perubahan)
            </div>

            @if($ticket->logs->count() > 0)
                {{-- Scrollable log wrapper --}}
                <div class="ticket-log-scrollable">
                    <div class="ticket-log">
                        @foreach($ticket->logs->sortByDesc('created_at') as $log)
                            <x-ticket.log-item :log="$log" :canViewNote="true" />
                        @endforeach
                    </div>
                </div>
            @else
                <p style="font-size:12px; color:var(--gray-400); text-align:center; padding:12px 0;">
                    Belum ada riwayat.
                </p>
            @endif
        </div>

        </div>{{-- END KOLOM KANAN --}}

    </div>{{-- END ticket-detail-wrapper --}}

</x-layout.app>
