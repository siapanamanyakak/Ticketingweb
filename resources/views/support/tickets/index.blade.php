<x-layout.app title="Ticket List" pageTitle="Ticket List">

    <div class="page-header">
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
            <div>
                <h1 class="page-title">Ticket List</h1>
                <p class="page-subtitle">List of all tickets</p>
            </div>
            <button onclick="openCreateTicketModal()" class="btn btn-primary">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Create New Ticket
</button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-body" style="padding: 16px 20px;">
            <form method="GET" action="{{ route('support.tickets.index') }}">
                <div class="filters-bar">
                    <div class="search-box">
            <span>
          <input type="text" name="search" placeholder="Search tickets..."
                               value="{{ request('search') }}">
                               <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
            </span>
                    </div>

                    <select name="priority" class="filter-select" onchange="this.form.submit()">
                        <option value="">All Priorities</option>
                        <option value="low"      {{ request('priority') === 'low'      ? 'selected' : '' }}>Low</option>
                        <option value="medium"   {{ request('priority') === 'medium'   ? 'selected' : '' }}>Medium</option>
                        <option value="high"     {{ request('priority') === 'high'     ? 'selected' : '' }}>High</option>
                        <option value="critical" {{ request('priority') === 'critical' ? 'selected' : '' }}>Critical</option>
                    </select>

                    <select name="category" class="filter-select" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        @foreach(\App\Models\Category::where('is_active', true)->get() as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>

                    @if(request()->hasAny(['search', 'priority', 'category']))
                        <a href="{{ route('support.tickets.index') }}" class="btn btn-secondary btn-sm">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    @php
        $activeTab = request('tab', 'all');
        $tabs = [
            'all'         => ['label' => 'All Active',  'count' => $totalActive],
            'open'        => ['label' => 'Open',          'count' => $tabCounts['open'] ?? 0],
            'in_progress' => ['label' => 'In Progress',   'count' => $tabCounts['in_progress'] ?? 0],
            'pending'     => ['label' => 'Pending',       'count' => $tabCounts['pending'] ?? 0],
            'resolved'    => ['label' => 'Resolved',      'count' => $tabCounts['resolved'] ?? 0],
        ];
    @endphp

<div class="ticket-tabs">
    @foreach($tabs as $key => $tab)
        <a href="{{ route('support.tickets.index', array_merge(request()->except(['tab','year','month','day']), ['tab' => $key])) }}"
           class="ticket-tab {{ $activeTab === $key ? 'active' : '' }}">
            {{ $tab['label'] }}
            <span class="tab-count">{{ $tab['count'] }}</span>
        </a>
    @endforeach
</div>

{{-- Filter Tanggal (hanya muncul saat tab Riwayat) --}}
@if($activeTab === 'closed')
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-body" style="padding: 14px 20px;">
            <form method="GET">
                <input type="hidden" name="tab" value="closed">
                <div class="filters-bar">
                    <span style="font-size:12px; font-weight:600; color:var(--gray-500);">Filter History:</span>

                    <select name="year" class="filter-select" onchange="this.form.submit()">
                        <option value="">All Years</option>
                        @foreach($availableYears as $year)
                            <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>

                    <select name="month" class="filter-select" onchange="this.form.submit()">
                        <option value="">All Months</option>
                        @foreach(range(1, 12) as $month)
                            <option value="{{ $month }}" {{ request('month') == $month ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($month)->format('F') }}
                            </option>
                        @endforeach
                    </select>

                    <select name="day" class="filter-select" onchange="this.form.submit()">
                        <option value="">All Days</option>
                        @foreach(range(1, 31) as $day)
                            <option value="{{ $day }}" {{ request('day') == $day ? 'selected' : '' }}>
                                {{ $day }}
                            </option>
                        @endforeach
                    </select>

                    @if(request()->hasAny(['year', 'month', 'day']))
                        <a href="{{ route('support.tickets.index', ['tab' => 'closed']) }}"
                           class="btn btn-secondary btn-sm">Reset Filter</a>
                    @endif
                </div>
            </form>
        </div>
    </div>
@endif

    {{-- Ticket List --}}
    @if($tickets->count() > 0)
        @foreach($tickets as $ticket)
            <x-ticket.card :ticket="$ticket" />
        @endforeach
        <x-ui.pagination :paginator="$tickets" />
    @else
        <div class="card">
            <x-ui.empty-state
                title="No tickets"
                description="There are no tickets that match the selected filter."
            />
        </div>
    @endif


{{-- Modal Buat Tiket --}}
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

                {{-- Reporter --}}
                <div class="form-group">
                    <label class="form-label required">Reporter</label>
                    <select name="user_id" class="form-control" required
                            id="reporterSelect" onchange="fillDepartment(this)">
                        <option value="">Select Reporter</option>
                        <optgroup label="IT Support">
                            <option value="{{ auth()->id() }}"
                                    data-dept="{{ auth()->user()->department?->name ?? '—' }}">
                                {{ auth()->user()->name }} ( — Self Reporting)
                            </option>
                        </optgroup>
                        <optgroup label="Employees">
                            @foreach($users->where('role', 'user') as $user)
                                <option value="{{ $user->id }}"
                                        data-dept="{{ $user->department?->name ?? '—' }}">
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </optgroup>
                    </select>
                    @error('user_id')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Departemen (auto-fill, disabled) --}}
                <div class="form-group">
                    <label class="form-label">Department</label>
                    <input type="text" id="departmentField" class="form-control"
                        placeholder="Automatically filled when selecting reporter"
                        disabled
                        style="background:var(--gray-50); color:var(--gray-500);">
                </div>

                {{-- Judul --}}
                <div class="form-group">
                    <label class="form-label required">Issue Title</label>
                    <input type="text" name="title" class="form-control"
                           placeholder="Example: Computer won't turn on"
                           value="{{ old('title') }}" required>
                    @error('title')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Deskripsi --}}
                <div class="form-group">
                    <label class="form-label required">Problem Description</label>
                    <textarea name="description" class="form-control" rows="3"
                              placeholder="Explain the problem in detail..."
                              required>{{ old('description') }}</textarea>
                    @error('description')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Category & Priority (opsional, override auto-detect) --}}
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div class="form-group">
                        <label class="form-label">
                            Category
                            <span style="font-size:10px; color:var(--gray-400); font-weight:400;">(optional)</span>
                        </label>
                        <select name="category_id" class="form-control">
                            <option value="">Auto Detect</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            Priority
                            <span style="font-size:10px; color:var(--gray-400); font-weight:400;">(optional)</span>
                        </label>
                        <select name="priority_id" class="form-control">
                            <option value="">Auto Detect</option>
                            @foreach($priorities as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Attachment --}}
                <div class="form-group">
                    <label class="form-label">
                        Attachment
                        <span style="font-size:10px; color:var(--gray-400); font-weight:400;">(optional)</span>
                    </label>
                    <input type="file" name="attachment" class="form-control"
                           accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                    <span class="form-hint">Format: JPG, PNG, PDF, DOC. Max 2MB.</span>
                    @error('attachment')
                        <span class="form-error" style="color:#dc2626; font-size:12px; font-weight:600; display:block; margin-top:4px;">
                            ⚠️ {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- Info auto-detect --}}
                <div class="alert alert-info" style="margin-top:4px; margin-bottom:0;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>If category & priority are not selected, the system will automatically detect them from the description.</span>
                </div>

            </div>

            <div class="quick-modal-footer">
                <button type="button" class="btn btn-secondary"
                        onclick="closeCreateTicketModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Create Ticket
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openCreateTicketModal() {
        document.getElementById('createTicketOverlay').classList.add('open');
    }

    function closeCreateTicketModal() {
        document.getElementById('createTicketOverlay').classList.remove('open');
        document.getElementById('createTicketForm').reset();
    }

    // Close saat klik overlay
    document.getElementById('createTicketOverlay').addEventListener('click', function(e) {
        if (e.target === this) closeCreateTicketModal();
    });
    function fillDepartment(select) {
    const selected = select.options[select.selectedIndex];
    const dept     = selected.dataset.dept ?? '';
    const field    = document.getElementById('departmentField');
    field.value    = dept || '—';
    }

    @if($errors->has('title') || $errors->has('description') || $errors->has('department_id'))
        openCreateTicketModal();
    @endif
</script>
@endpush


{{-- Quick Action Modal Khusus Halaman List (Digerakkan oleh ticket-action.js) --}}
<div class="quick-modal-overlay" id="quickModalOverlay">
    <div class="quick-modal">
        <div class="quick-modal-header">
            <span class="quick-modal-title" id="quickModalTitle">Confirm</span>
            <button class="quick-modal-close" type="button" onclick="closeQuickModal()">✕</button>
        </div>

        <div class="quick-modal-body">
            <p id="quickModalDesc" style="font-size:13px; color:var(--gray-600); margin-bottom:16px;"></p>

            <div id="quickModalNoteField" class="form-group">
                <label class="form-label" id="quickModalNoteLabel">Note (Optional)</label>
                <textarea id="quickModalNote" class="form-control" rows="2" placeholder="Add a note..."></textarea>
            </div>

            <div id="resolutionField" style="display:none;" class="form-group">
                <label class="form-label required">Resolution Notes</label>
                <textarea id="quickModalResolution" class="form-control" rows="3" placeholder="Explain the steps taken to resolve the issue..."></textarea>
            </div>
        </div>

        <div class="modal-footer" style="padding: 16px; border-top: 1px solid var(--gray-200); display: flex; justify-content: flex-end; gap: 8px;">
            <button type="button" class="btn btn-secondary" onclick="closeQuickModal()">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="submitQuickAction()">Confirm Update</button>
        </div>
    </div>
</div>

</x-layout.app>
