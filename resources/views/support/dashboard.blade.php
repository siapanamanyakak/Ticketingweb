<x-layout.app title="Dashboard" pageTitle="Dashboard">


@php $user = auth()->user(); @endphp

    {{-- News Banner --}}
    <x-ui.news-banner :news="$activeNews" />

{{-- ── BENTO ROW 1: Welcome + Quick Actions ── --}}
<div style="display:grid; grid-template-columns:1fr 280px; gap:16px; margin-bottom:16px;">

    {{-- Welcome Card --}}
    <div style="background:linear-gradient(135deg, var(--navy-900) 0%, var(--navy-700) 100%);
                border-radius:16px; padding:28px; color:white; position:relative; overflow:hidden;">
        <div style="position:absolute; right:-30px; top:-30px; width:160px; height:160px;
                    border-radius:50%; background:rgba(255,255,255,0.05);"></div>
        <div style="position:absolute; right:40px; bottom:-40px; width:120px; height:120px;
                    border-radius:50%; background:rgba(255,255,255,0.03);"></div>

        <div style="position:relative; z-index:1;">
            <p style="font-size:13px; font-weight:500; opacity:0.7; margin-bottom:6px;">
                {{ now()->format('l, d F Y') }}
            </p>
            <h2 style="font-size:22px; font-weight:800; margin-bottom:6px;">
                Welcome, {{ explode(' ', $user->name)[0] }}! 👋
            </h2>
            <p style="font-size:13px; opacity:0.7; margin-bottom:12px;">
                Ready to handle tickets today?
            </p>
            <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
                <div style="display:flex; align-items:center; gap:6px; font-size:12px; opacity:0.8;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="14" height="14">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2"/>
                    </svg>
                    {{ $user->id_staff ?? 'N/A' }}
                </div>
                <div style="display:flex; align-items:center; gap:6px; font-size:12px; opacity:0.8;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="14" height="14">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    {{ $user->department?->name ?? 'N/A' }}
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div style="background:white; border-radius:16px; padding:24px;
                box-shadow:0 1px 4px rgba(0,0,0,0.06);">
        <p style="font-size:11px; font-weight:700; color:var(--gray-400);
                  text-transform:uppercase; letter-spacing:0.8px; margin-bottom:16px;">
            Quick Actions
        </p>

        <button onclick="openCreateTicketModal()"
                style="width:100%; display:flex; align-items:center; gap:10px;
                       padding:12px 14px; background:var(--navy-50); border:1.5px solid var(--navy-100);
                       border-radius:10px; cursor:pointer; font-family:'Montserrat',sans-serif;
                       font-size:13px; font-weight:600; color:var(--navy-600);
                       transition:all 0.2s; margin-bottom:10px;"
                onmouseover="this.style.background='var(--navy-100)'"
                onmouseout="this.style.background='var(--navy-50)'">
            <div style="width:32px; height:32px; background:var(--navy-600); border-radius:8px;
                        display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <svg fill="none" stroke="white" viewBox="0 0 24 24" width="16" height="16">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </div>
            Create New Ticket
        </button>

        <button onclick="openAddNewsModal()"
                style="width:100%; display:flex; align-items:center; gap:10px;
                    padding:12px 14px; background:#fff7ed; border:1.5px solid #fed7aa;
                    border-radius:10px; cursor:pointer; font-family:'Montserrat',sans-serif;
                    font-size:13px; font-weight:600; color:#c2410c;
                    transition:all 0.2s; margin-bottom:10px;"
                onmouseover="this.style.background='#ffedd5'"
                onmouseout="this.style.background='#fff7ed'">
            <div style="width:32px; height:32px; background:#c2410c; border-radius:8px;
                        display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <svg fill="none" stroke="white" viewBox="0 0 24 24" width="16" height="16">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                </svg>
            </div>
            Create News
        </button>
    </div>
</div>

{{-- ── BENTO ROW 2: Stats ── --}}
<div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:12px; margin-bottom:16px;">
    @php
        $statItems = [
            ['label' => 'Open Tickets',    'value' => $stats['open'],         'bg' => '#f3f4f6', 'color' => '#4b5563',  'icon' => 'M12 4v16m8-8H4'],
            ['label' => 'In Progress',   'value' => $stats['in_progress'],  'bg' => '#dbeafe', 'color' => '#1d4ed8',  'icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'],
            ['label' => 'Pending',       'value' => $stats['pending'],      'bg' => '#fef3c7', 'color' => '#b45309',  'icon' => 'M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['label' => 'SLA Breached',  'value' => $stats['sla_breached'], 'bg' => '#fee2e2', 'color' => '#b91c1c',  'icon' => 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        ];
    @endphp

    @foreach($statItems as $stat)
        <div style="background:white; border-radius:12px; padding:16px;
                    box-shadow:0 1px 4px rgba(0,0,0,0.06);">
            <div style="width:36px; height:36px; background:{{ $stat['bg'] }}; border-radius:8px;
                        display:flex; align-items:center; justify-content:center; margin-bottom:10px;">
                <svg fill="none" stroke="{{ $stat['color'] }}" viewBox="0 0 24 24" width="18" height="18">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="{{ $stat['icon'] }}"/>
                </svg>
            </div>
            <div style="font-size:22px; font-weight:800; color:var(--gray-900); line-height:1;">
                {{ $stat['value'] }}
            </div>
            <div style="font-size:11px; font-weight:600; color:var(--gray-500); margin-top:3px;">
                {{ $stat['label'] }}
            </div>
        </div>
    @endforeach
</div>

{{-- ── BENTO ROW 3: Open Tickets ── --}}
<div style="background:white; border-radius:16px; padding:24px;
            box-shadow:0 1px 4px rgba(0,0,0,0.06);">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
        <div>
            <h3 style="font-size:15px; font-weight:700; color:var(--gray-900);">
                🎫 Open Tickets
            </h3>
            <p style="font-size:12px; color:var(--gray-400); margin-top:2px;">
                Tickets waiting to be handled
            </p>
        </div>
        <a href="{{ route('support.tickets.index') }}"
           style="font-size:12px; font-weight:700; color:var(--navy-600); text-decoration:none;
                  display:flex; align-items:center; gap:4px;">
            View All
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="14" height="14">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    @forelse($openTickets as $ticket)
        <a href="{{ route('support.tickets.show', $ticket) }}"
           style="display:flex; align-items:center; gap:14px; padding:12px 0;
                  border-bottom:1px solid var(--gray-100); text-decoration:none;
                  transition:background 0.15s;"
           onmouseover="this.style.background='var(--gray-50)'"
           onmouseout="this.style.background='transparent'">

            @php
                $priorityColors = [
                    'low' => '#16a34a', 'medium' => '#d97706',
                    'high' => '#dc2626', 'critical' => '#7c2d12'
                ];
                $pColor = $priorityColors[$ticket->priority?->level ?? 'low'];
            @endphp
            <div style="width:4px; height:48px; border-radius:2px;
                        background:{{ $pColor }}; flex-shrink:0;"></div>

            {{-- Reporter Avatar --}}
            <div style="width:36px; height:36px; border-radius:50%; background:var(--navy-100);
                        display:flex; align-items:center; justify-content:center;
                        font-size:13px; font-weight:700; color:var(--navy-600); flex-shrink:0;">
                {{ strtoupper(substr($ticket->reporter->name, 0, 1)) }}
            </div>

            {{-- Info --}}
            <div style="flex:1; min-width:0;">
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:3px;">
                    <span style="font-size:12px; font-weight:700; color:var(--navy-600);">
                        {{ $ticket->ticket_number }}
                    </span>
                    <span style="font-size:11px; color:var(--gray-400);">
                        {{ $ticket->reporter->name }} · {{ $ticket->reporter->department?->name ?? '-' }}
                    </span>
                </div>
                <p style="font-size:13px; font-weight:600; color:var(--gray-900);
                          overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                    {{ $ticket->title }}
                </p>
            </div>

            {{-- SLA + Time --}}
            <div style="display:flex; flex-direction:column; align-items:flex-end; gap:4px; flex-shrink:0;">
                @if($ticket->slaRecord)
                    <x-ui.sla-timer :ticket="$ticket"/>
                @endif
                <span style="font-size:11px; color:var(--gray-400);">
                    {{ $ticket->created_at->diffForHumans() }}
                </span>
            </div>
        </a>
    @empty
        <div style="text-align:center; padding:40px 0; color:var(--gray-400);">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"
                 width="40" height="40" style="margin:0 auto 12px; display:block; opacity:0.4;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p style="font-size:13px; font-weight:600;">No open tickets</p>
            <p style="font-size:12px; margin-top:4px;">All tickets have been handled!</p>
        </div>
    @endforelse
</div>

{{-- Modal Create Ticket --}}
<div class="quick-modal-overlay" id="createTicketOverlay">
    <div class="quick-modal" style="max-width:560px;">
        <div class="quick-modal-header">
            <span class="quick-modal-title">🎫 Create New Ticket</span>
            <button class="quick-modal-close" onclick="closeCreateTicketModal()">✕</button>
        </div>
        <form method="POST" action="{{ route('support.tickets.store') }}"
              enctype="multipart/form-data" id="createTicketForm">
            @csrf
            <div class="quick-modal-body" style="max-height:70vh; overflow-y:auto;">

                <div class="form-group">
                    <label class="form-label required">Reporter</label>
                    <select name="user_id" class="form-control" required
                            onchange="fillDepartment(this)">
                        <option value="">Select Reporter</option>
                        <optgroup label="IT Support">
                            <option value="{{ auth()->id() }}"
                                    data-dept="{{ auth()->user()->department?->name ?? '—' }}">
                                {{ auth()->user()->name }} (Me)
                            </option>
                        </optgroup>
                        <optgroup label="Employees">
                            @foreach(\App\Models\User::where('role', 'user')->where('is_active', true)->with('department')->orderBy('name')->get() as $u)
                                <option value="{{ $u->id }}"
                                        data-dept="{{ $u->department?->name ?? '—' }}">
                                    {{ $u->name }}
                                </option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Department</label>
                    <input type="text" id="departmentField" class="form-control"
                           placeholder="Automatically filled when selecting reporter"
                           disabled style="background:var(--gray-50); color:var(--gray-500);">
                </div>

                <div class="form-group">
                    <label class="form-label required">Issue Title</label>
                    <input type="text" name="title" class="form-control"
                           placeholder="Example: Computer won't turn on" required>
                </div>

                <div class="form-group">
                    <label class="form-label required">Problem Description</label>
                    <textarea name="description" class="form-control" rows="3"
                              placeholder="Explain the problem in detail..." required></textarea>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div class="form-group">
                        <label class="form-label">Category <span style="font-size:10px; color:var(--gray-400); font-weight:400;">(optional)</span></label>
                        <select name="category_id" class="form-control">
                            <option value="">Auto Detect</option>
                            @foreach(\App\Models\Category::where('is_active', true)->get() as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Priority <span style="font-size:10px; color:var(--gray-400); font-weight:400;">(optional)</span></label>
                        <select name="priority_id" class="form-control">
                            <option value="">Auto Detect</option>
                            @foreach(\App\Models\Priority::all() as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Attachment <span style="font-size:10px; color:var(--gray-400); font-weight:400;">(optional)</span></label>
                    <input type="file" name="attachment" class="form-control"
                           accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                    <span class="form-hint">Format: JPG, PNG, PDF, DOC. Maximum 2MB.</span>
                </div>

            </div>
            <div class="quick-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeCreateTicketModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Ticket</button>
            </div>
        </form>
    </div>
</div>

    {{-- Modal Buat News --}}
    <div class="quick-modal-overlay" id="addNewsOverlay">
        <div class="quick-modal" style="max-width:520px;">
            <div class="quick-modal-header">
                <span class="quick-modal-title">📢 Create New News</span>
                <button class="quick-modal-close" onclick="closeAddNewsModal()">✕</button>
            </div>
            <form method="POST" action="{{ route('support.news.store') }}">
                @csrf
                <div class="quick-modal-body" style="max-height:70vh; overflow-y:auto;">
                    <div class="form-group">
                        <label class="form-label required">Title</label>
                        <input type="text" name="title" class="form-control"
                            placeholder="Example: Monthly server maintenance" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label required">Description</label>
                        <textarea name="description" class="form-control" rows="3"
                                placeholder="Explain the details..." required></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label required">Type</label>
                        <select name="type" class="form-control" required>
                            <option value="info">📢 Info</option>
                            <option value="warning">⚠️ Warning</option>
                            <option value="maintenance">🔧 Maintenance</option>
                        </select>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div class="form-group">
                            <label class="form-label">Start Showing</label>
                            <input type="datetime-local" name="starts_at" class="form-control">
                            <span class="form-hint">Empty = show immediately</span>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ends At</label>
                            <input type="datetime-local" name="ends_at" class="form-control">
                            <span class="form-hint">Empty = no expiration</span>
                        </div>
                    </div>
                </div>
                <div class="quick-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeAddNewsModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Publish</button>
                </div>
            </form>
        </div>
    </div>

@push('scripts')
<script>

    function openAddNewsModal()  { document.getElementById('addNewsOverlay').classList.add('open'); }
    function closeAddNewsModal() { document.getElementById('addNewsOverlay').classList.remove('open'); }
    document.getElementById('addNewsOverlay').addEventListener('click', function(e) {
        if (e.target === this) closeAddNewsModal();
    });

    function openCreateTicketModal() {
        document.getElementById('createTicketOverlay').classList.add('open');
    }
    function closeCreateTicketModal() {
        document.getElementById('createTicketOverlay').classList.remove('open');
        document.getElementById('createTicketForm').reset();
        document.getElementById('departmentField').value = '';
    }
    function fillDepartment(select) {
        const selected = select.options[select.selectedIndex];
        document.getElementById('departmentField').value = selected.dataset.dept ?? '—';
    }
    document.getElementById('createTicketOverlay').addEventListener('click', function(e) {
        if (e.target === this) closeCreateTicketModal();
    });
</script>
@endpush

</x-layout.app>
