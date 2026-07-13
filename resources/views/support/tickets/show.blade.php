<x-layout.app title="Ticket Details" pageTitle="Ticket Details">

    <div class="page-header">
        <div style="display:flex; align-items:center; gap:12px;">
            <a href="{{ route('support.tickets.index') }}" class="btn btn-secondary btn-sm">
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
                    <x-ui.sla-timer :ticket="$ticket" :timeData="$slaRemaining" />
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

                {{-- Resolution Notes --}}
                @if($ticket->resolution_notes)
                    <div style="margin-top:20px; padding:16px; background:#dcfce7; border-radius:10px; border-left:4px solid #16a34a;">
                        <div style="font-size:12px; font-weight:700; color:#15803d; margin-bottom:6px; text-transform:uppercase; letter-spacing:0.5px;">
                            ✓ Resolution Notes
                        </div>
                        <p style="font-size:13px; color:#166534; line-height:1.6;">{{ $ticket->resolution_notes }}</p>
                        <div style="font-size:11px; color:#15803d; margin-top:8px; opacity:0.7;">
                            Resolved: {{ $ticket->resolved_at?->format('d M Y, H:i') ?? '—' }}
                        </div>
                    </div>
                @endif
            </div>

{{-- Update Status + Comments (2 kolom) --}}
<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; align-items:start; margin-top:20px;">

    {{-- KOLOM 1: Update Status, Priority, & Category --}}
    @if(!in_array($ticket->status, ['closed']))
        <div class="status-update-form">
            <div style="font-size:13px; font-weight:700; color:var(--gray-700); margin-bottom:14px;">
                🔄 Update Status & Priority
            </div>

            {{-- Status Dropdown --}}
            <div style="margin-bottom:16px;">
                <label class="form-label">Status</label>
                @php
                    $availableStatuses = match($ticket->status) {
                        'open'        => ['in_progress' => ['label' => 'In Progress', 'color' => '#2563eb', 'bg' => '#dbeafe']],
                        'in_progress' => ['pending' => ['label' => 'Pending', 'color' => '#b45309', 'bg' => '#fef3c7'], 'resolved' => ['label' => 'Resolved', 'color' => '#15803d', 'bg' => '#dcfce7']],
                        'pending'     => ['in_progress' => ['label' => 'In Progress', 'color' => '#2563eb', 'bg' => '#dbeafe']],
                        'resolved'    => ['closed' => ['label' => 'Closed', 'color' => '#374151', 'bg' => '#f3f4f6']],
                        default       => [],
                    };
                @endphp

                <div style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:10px;">
                    @foreach($availableStatuses as $statusKey => $statusInfo)
                        <button type="button"
                                onclick="openDetailStatusModal('{{ $ticket->id }}', '{{ $statusKey }}', '{{ $statusInfo['label'] }}', {{ in_array($statusKey, ['pending']) ? 'true' : 'false' }})"
                                class="quick-action-btn {{ $statusKey }}"
                                style="background:{{ $statusInfo['bg'] }}; color:{{ $statusInfo['color'] }};">
                            <span style="width:8px; height:8px; border-radius:50%;
                                        background:{{ $statusInfo['color'] }}; display:inline-block;"></span>
                            {{ $statusInfo['label'] }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Priority Dropdown --}}
            <div>
                <label class="form-label">Priority</label>
                <div style="display:flex; flex-wrap:wrap; gap:8px;">
                    @foreach(\App\Models\Priority::all() as $p)
                        @php
                            $pColors = [
                                'low'      => ['color' => '#15803d', 'bg' => '#dcfce7'],
                                'medium'   => ['color' => '#b45309', 'bg' => '#fef3c7'],
                                'high'     => ['color' => '#b91c1c', 'bg' => '#fee2e2'],
                                'critical' => ['color' => '#7c2d12', 'bg' => '#fce7f3'],
                            ];
                            $pc = $pColors[$p->level] ?? ['color' => '#374151', 'bg' => '#f3f4f6'];
                        @endphp
                        <button type="button"
                                onclick="openDetailPriorityModal({{ $ticket->id }}, {{ $p->id }}, '{{ $p->name }}')"
                                class="quick-action-btn {{ $p->level }}"
                                style="{{ $ticket->priority_id == $p->id ? 'ring: 2px solid ' . $pc['color'] . '; outline: 2px solid ' . $pc['color'] . ';' : 'opacity:0.6;' }}">
                            <span style="width:8px; height:8px; border-radius:50%;
                                        background:{{ $pc['color'] }}; display:inline-block;"></span>
                            {{ $p->name }}
                            @if($ticket->priority_id == $p->id)
                                ✓
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Divider --}}
            <div style="border-top:1px solid var(--gray-100); margin:16px 0;"></div>

            {{-- Update Category --}}
            <div style="font-size:13px; font-weight:700; color:var(--gray-700); margin-bottom:14px;">
                📂 Update Category
            </div>
            <form method="POST" action="{{ route('support.tickets.updateCategory', $ticket) }}">
                @csrf @method('PATCH')
                <div style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:12px;">
                    @foreach(\App\Models\Category::where('is_active', true)->get() as $cat)
                        <input type="radio" name="category_id" id="category_{{ $cat->id }}"
                            value="{{ $cat->id }}" class="status-option"
                            {{ $ticket->category_id == $cat->id ? 'checked' : '' }}>
                        <label for="category_{{ $cat->id }}" class="status-option-label"
                            style="--status-color:var(--navy-600); --status-bg:var(--navy-50);">
                            {{ $cat->name }}
                            @if($ticket->category_id == $cat->id) ✓ @endif
                        </label>
                    @endforeach
                </div>
                <div class="form-group">
                    <label class="form-label">Catatan (Opsional)</label>
                    <textarea name="note" class="form-control" rows="2"
                            placeholder="Add a note for this update..."></textarea>
                </div>
                <button type="submit" class="btn btn-secondary btn-sm">Update Category</button>
            </form>
        </div>
    @else
        <div class="status-update-form" style="opacity:0.6;">
            <div style="font-size:13px; font-weight:700; color:var(--gray-700); margin-bottom:8px;">
                🔒 Ticket Closed
            </div>
            <p style="font-size:12px; color:var(--gray-500);">This ticket is already closed and cannot be modified.</p>
        </div>
    @endif
    {{-- KOLOM 2: Comments --}}
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
                    No comments yet.
                </p>
            @endif
        </div>

        {{-- Form tetap di bawah, tidak ikut scroll --}}
        @if(!in_array($ticket->status, ['closed']))
            <div class="comment-form">
                <form method="POST" action="{{ route('support.tickets.comments.store', $ticket) }}">
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
    {{-- END Comments --}}

</div>
        </div>{{-- END KOLOM KIRI --}}

        {{-- KOLOM KANAN --}}
        <div class="ticket-sidebar-panel">

            @if($ticket->slaRecord)
                <div class="panel-card">
                    <div class="panel-card-title">⏱ SLA Status</div>
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
                            <span class="panel-row-label">Total Paused</span>
                            <span class="panel-row-value">{{ $ticket->slaRecord->total_paused_minutes }} minutes</span>
                        </div>
                        @if($ticket->slaPauses->count() > 0)
                            <div class="panel-row">
                                <span class="panel-row-label">Paused History</span>
                                <span class="panel-row-value">{{ $ticket->slaPauses->count() }}x</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="panel-card">
                <div class="panel-card-title">📋Ticket Information</div>
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
                    <span class="panel-row-label">Pending</span>
                    <span class="panel-row-value" style="{{ $ticket->had_pending ? 'color:#d97706;' : '' }}">
                        {{ $ticket->had_pending ? $ticket->pending_count . 'x' : 'No' }}
                    </span>
                </div>
            </div>

        <div class="panel-card">
            <div class="panel-card-title">
                📜 Log ({{ $ticket->logs->count() }}x changes)
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
                    No history available.
                </p>
            @endif
        </div>

        </div>{{-- END KOLOM KANAN --}}

    </div>{{-- END ticket-detail-wrapper --}}

     @push('scripts')
        <script>
            // ═══════════════════════════════════════════
            //  1. BAGIAN UPDATE STATUS
            // ═══════════════════════════════════════════
            function openDetailStatusModal(ticketId, status, label, noteRequired) {
                // Isi input tersembunyi agar dibaca oleh Laravel Controller
                document.getElementById('modalStatusInput').value = status;

                // Biarkan ticket-action.js yang mengurus perubahan wajah UI-nya
                openStatusModal(ticketId, status, label, noteRequired);
            }

            function validateStatusForm() {
                const status = document.getElementById('modalStatusInput').value;
                const note = document.getElementById('quickModalNote');
                const resolution = document.getElementById('quickModalResolution');

                // Reset warna border merah
                if (note) note.style.borderColor = '';
                if (resolution) resolution.style.borderColor = '';

                // Cek wajib isi untuk Pending
                if (status === 'pending') {
                    if (!note.value.trim()) {
                        note.style.borderColor = '#dc2626';
                        note.focus();
                        return false; // Tahan form, jangan dikirim
                    }
                }

                // Cek wajib isi untuk Resolved
                if (status === 'resolved') {
                    if (!resolution.value.trim()) {
                        resolution.style.borderColor = '#dc2626';
                        resolution.focus();
                        return false; // Tahan form, jangan dikirim
                    }
                }

                return true; // Form aman, kirim ke server!
            }


            // ═══════════════════════════════════════════
            //  2. BAGIAN UPDATE PRIORITAS
            // ═══════════════════════════════════════════
            function openDetailPriorityModal(ticketId, priorityId, priorityLabel) {
                document.getElementById('modalPriorityInput').value = priorityId;
                document.getElementById('priorityModalDesc').innerHTML = `Change priority to <strong>${priorityLabel}</strong>?`;
                document.getElementById('priorityModalOverlay').classList.add('open');
            }

            function closePriorityModal() {
                document.getElementById('priorityModalOverlay').classList.remove('open');
            }
        </script>
     @endpush

    {{-- Quick Action Modal --}}
<div class="quick-modal-overlay" id="quickModalOverlay">
    <div class="quick-modal">
        <div class="quick-modal-header">
            <span class="quick-modal-title" id="quickModalTitle">Confirm</span>
            <button class="quick-modal-close" onclick="closeQuickModal()">✕</button>
        </div>

        <form action="{{ route('support.tickets.updateStatus', $ticket->id) }}" method="POST" id="statusUpdateForm" onsubmit="return validateStatusForm()">
            @csrf
            @method('PATCH')

            <input type="hidden" name="status" id="modalStatusInput">

            <div class="quick-modal-body">
                <p id="quickModalDesc" style="font-size:13px; color:var(--gray-600); margin-bottom:16px;"></p>

                <div id="quickModalNoteField" class="form-group">
                    <label class="form-label" id="quickModalNoteLabel">Note (Optional)</label>
                    <textarea id="quickModalNote" name="note" class="form-control" rows="2" placeholder="Add a note..."></textarea>
                </div>

                <div id="resolutionField" style="display:none;" class="form-group">
                    <label class="form-label required">Resolution Notes</label>
                    <textarea id="quickModalResolution" name="resolution_notes" class="form-control" rows="3" placeholder="Explain the steps taken to resolve the issue..."></textarea>
                </div>
            </div>

            <div class="modal-footer" style="padding: 16px; border-top: 1px solid var(--gray-200); display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" class="btn btn-secondary" onclick="closeQuickModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Status</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Khusus Update Priority --}}
<div class="quick-modal-overlay" id="priorityModalOverlay">
    <div class="quick-modal">
        <div class="quick-modal-header">
            <span class="quick-modal-title">Update Ticket Priority</span>
            <button class="quick-modal-close" type="button" onclick="closePriorityModal()">✕</button>
        </div>

        <form action="{{ route('support.tickets.updatePriority', $ticket->id) }}" method="POST">
            @csrf
            @method('PATCH')

            <input type="hidden" name="priority_id" id="modalPriorityInput">

            <div class="quick-modal-body">
                <p id="priorityModalDesc" style="font-size:13px; color:var(--gray-600); margin-bottom:16px;"></p>

                <div class="form-group">
                    <label class="form-label">Note (Optional)</label>
                    <textarea name="note" class="form-control" rows="2" placeholder="Add a note..."></textarea>
                </div>
            </div>

            <div class="modal-footer" style="padding: 16px; border-top: 1px solid var(--gray-200); display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" class="btn btn-secondary" onclick="closePriorityModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Priority</button>
            </div>
        </form>
    </div>
</div>

</x-layout.app>
