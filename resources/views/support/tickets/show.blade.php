<x-layout.app title="Detail Tiket" pageTitle="Detail Tiket">

    <div class="page-header">
        <div style="display:flex; align-items:center; gap:12px;">
            <a href="{{ route('support.tickets.index') }}" class="btn btn-secondary btn-sm">
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

{{-- Update Status + Comments (2 kolom) --}}
<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; align-items:start; margin-top:20px;">

    {{-- KOLOM 1: Update Status, Priority, & Category --}}
    @if(!in_array($ticket->status, ['closed']))
        <div class="status-update-form">
            <div style="font-size:13px; font-weight:700; color:var(--gray-700); margin-bottom:14px;">
                🔄 Update Status & Prioritas
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
                <label class="form-label">Prioritas</label>
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
                                onclick="openPriorityModal({{ $ticket->id }}, {{ $p->id }}, '{{ $p->name }}')"
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

            {{-- Update Kategori --}}
            <div style="font-size:13px; font-weight:700; color:var(--gray-700); margin-bottom:14px;">
                📂 Update Kategori
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
                            placeholder="Alasan perubahan kategori..."></textarea>
                </div>
                <button type="submit" class="btn btn-secondary btn-sm">Update Kategori</button>
            </form>
        </div>
    @else
        <div class="status-update-form" style="opacity:0.6;">
            <div style="font-size:13px; font-weight:700; color:var(--gray-700); margin-bottom:8px;">
                🔒 Tiket Ditutup
            </div>
            <p style="font-size:12px; color:var(--gray-500);">Tiket ini sudah ditutup dan tidak dapat diubah.</p>
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
                    Belum ada komentar.
                </p>
            @endif
        </div>

        {{-- Form tetap di bawah, tidak ikut scroll --}}
        @if(!in_array($ticket->status, ['closed']))
            <div class="comment-form">
                <form method="POST" action="{{ route('support.tickets.comments.store', $ticket) }}">
                    @csrf
                    <textarea name="comment"
                            placeholder="Tulis komentar atau update informasi ke pelapor..."
                            required></textarea>
                    <div class="comment-form-footer">
                        <button type="submit" class="btn btn-primary btn-sm">Kirim Komentar</button>
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
                    <div class="panel-card-title">⏱ Status SLA</div>
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
                            <span class="panel-row-label">Total Dijeda</span>
                            <span class="panel-row-value">{{ $ticket->slaRecord->total_paused_minutes }} menit</span>
                        </div>
                        @if($ticket->slaPauses->count() > 0)
                            <div class="panel-row">
                                <span class="panel-row-label">Riwayat Jeda</span>
                                <span class="panel-row-value">{{ $ticket->slaPauses->count() }}x</span>
                            </div>
                        @endif
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
                    <span class="panel-row-label">Pending</span>
                    <span class="panel-row-value" style="{{ $ticket->had_pending ? 'color:#d97706;' : '' }}">
                        {{ $ticket->had_pending ? $ticket->pending_count . 'x' : 'Tidak' }}
                    </span>
                </div>
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

@push('scripts')
    <script>
        // 1. Fungsi Utama untuk mengatur perubahan form (Disatukan di sini)
        function handleStatusChange(status) {
            const noteLabel        = document.getElementById('detailNoteLabel');
            const noteField        = document.getElementById('detailNoteField');
            const detailNote       = document.getElementById('detailNote');

            const resolutionField  = document.getElementById('detailResolutionField');
            const detailResolution = document.getElementById('detailResolution');

            // Reset border error setiap kali status diganti
            if (detailNote) detailNote.style.borderColor = '';
            if (detailResolution) detailResolution.style.borderColor = '';

            if (status === 'pending') {
                noteLabel.textContent  = 'Alasan Pending';
                noteLabel.className    = 'form-label required';
                detailNote.placeholder = 'Wajib isi alasan pending...';

                noteField.style.display       = 'block';
                resolutionField.style.display = 'none';
                if(detailResolution) detailResolution.required = false;

            } else if (status === 'resolved') {
                noteField.style.display       = 'none';
                resolutionField.style.display = 'block';
                if(detailResolution) detailResolution.required = true;

            } else {
                noteLabel.textContent  = 'Catatan (Opsional)';
                noteLabel.className    = 'form-label';
                detailNote.placeholder = 'Tambahkan catatan...';

                noteField.style.display       = 'block';
                resolutionField.style.display = 'none';
                if(detailResolution) detailResolution.required = false;
            }
        }

        // 2. Fungsi Validasi saat tombol submit ditekan
        function validateStatusForm() {
            const selectedStatus = document.querySelector('input[name="status"]:checked');
            if (!selectedStatus) {
                alert('Pilih status terlebih dahulu!');
                return false;
            }

            const status = selectedStatus.value;
            const detailNote = document.getElementById('detailNote');
            const detailResolution = document.getElementById('detailResolution');

            // Reset border dulu sebelum divalidasi ulang
            if (detailNote) detailNote.style.borderColor = '';
            if (detailResolution) detailResolution.style.borderColor = '';

            if (status === 'pending') {
                const note = detailNote.value.trim();
                if (!note) {
                    detailNote.style.borderColor = '#dc2626';
                    detailNote.focus();
                    return false;
                }
            }

            if (status === 'resolved') {
                const resolution = detailResolution.value.trim();
                if (!resolution) {
                    detailResolution.style.borderColor = '#dc2626';
                    detailResolution.focus();
                    return false;
                }
            }

            return true;
        }

        function openDetailStatusModal(ticketId, status, label, noteRequired) {
            // Gunakan fungsi yang sama dengan quick action
            openStatusModal(ticketId, status, label, noteRequired);
        }

        // 3. Pasang Event Listener saat halaman selesai dimuat (DOM Ready)
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('input[name="status"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    // Panggil fungsi utama di atas saat radio button diklik
                    handleStatusChange(this.value);
                });
            });
        });
    </script>
    @endpush

</x-layout.app>
