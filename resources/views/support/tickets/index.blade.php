<x-layout.app title="List Tiket" pageTitle="List Tiket">

    <div class="page-header">
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
            <div>
                <h1 class="page-title">Tiket Saya</h1>
                <p class="page-subtitle">Daftar seluruh tiket yang pernah kamu buat</p>
            </div>
            <button onclick="openCreateTicketModal()" class="btn btn-primary">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Buat Tiket Baru
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
          <input type="text" name="search" placeholder="Cari tiket..."
                               value="{{ request('search') }}">
                               <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
</span>


                    </div>

                    <select name="priority" class="filter-select" onchange="this.form.submit()">
                        <option value="">Semua Prioritas</option>
                        <option value="low"      {{ request('priority') === 'low'      ? 'selected' : '' }}>Low</option>
                        <option value="medium"   {{ request('priority') === 'medium'   ? 'selected' : '' }}>Medium</option>
                        <option value="high"     {{ request('priority') === 'high'     ? 'selected' : '' }}>High</option>
                        <option value="critical" {{ request('priority') === 'critical' ? 'selected' : '' }}>Critical</option>
                    </select>

                    <select name="category" class="filter-select" onchange="this.form.submit()">
                        <option value="">Semua Kategori</option>
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
            'all'         => ['label' => 'Semua Aktif',  'count' => $totalActive],
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
                    <span style="font-size:12px; font-weight:600; color:var(--gray-500);">Filter Riwayat:</span>

                    <select name="year" class="filter-select" onchange="this.form.submit()">
                        <option value="">Semua Tahun</option>
                        @foreach($availableYears as $year)
                            <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>

                    <select name="month" class="filter-select" onchange="this.form.submit()">
                        <option value="">Semua Bulan</option>
                        @foreach(range(1, 12) as $month)
                            <option value="{{ $month }}" {{ request('month') == $month ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($month)->format('F') }}
                            </option>
                        @endforeach
                    </select>

                    <select name="day" class="filter-select" onchange="this.form.submit()">
                        <option value="">Semua Hari</option>
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
                title="Tidak ada tiket"
                description="Tidak ada tiket yang sesuai dengan filter yang dipilih."
            />
        </div>
    @endif


{{-- Modal Buat Tiket --}}
<div class="quick-modal-overlay" id="createTicketOverlay">
    <div class="quick-modal" style="max-width:560px;">
        <div class="quick-modal-header">
            <span class="quick-modal-title">🎫 Buat Tiket Baru</span>
            <button class="quick-modal-close" onclick="closeCreateTicketModal()">✕</button>
        </div>

        <form method="POST" action="{{ route('support.tickets.store') }}"
              enctype="multipart/form-data" id="createTicketForm">
            @csrf

            <div class="quick-modal-body" style="max-height:70vh; overflow-y:auto;">

                {{-- Reporter --}}
                <div class="form-group">
                    <label class="form-label required">Pelapor</label>
                    <select name="user_id" class="form-control" required
                            id="reporterSelect" onchange="fillDepartment(this)">
                        <option value="">Pilih Pelapor</option>
                        <optgroup label="IT Support">
                            <option value="{{ auth()->id() }}"
                                    data-dept="{{ auth()->user()->department?->name ?? '—' }}">
                                {{ auth()->user()->name }} (Saya — Pencatatan Mandiri)
                            </option>
                        </optgroup>
                        <optgroup label="Karyawan">
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
                    <label class="form-label">Departemen</label>
                    <input type="text" id="departmentField" class="form-control"
                        placeholder="Otomatis terisi saat memilih pelapor"
                        disabled
                        style="background:var(--gray-50); color:var(--gray-500);">
                </div>

                {{-- Judul --}}
                <div class="form-group">
                    <label class="form-label required">Judul Masalah</label>
                    <input type="text" name="title" class="form-control"
                           placeholder="Contoh: Komputer tidak bisa menyala"
                           value="{{ old('title') }}" required>
                    @error('title')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Deskripsi --}}
                <div class="form-group">
                    <label class="form-label required">Deskripsi Masalah</label>
                    <textarea name="description" class="form-control" rows="3"
                              placeholder="Jelaskan masalah secara detail..."
                              required>{{ old('description') }}</textarea>
                    @error('description')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Category & Priority (opsional, override auto-detect) --}}
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div class="form-group">
                        <label class="form-label">
                            Kategori
                            <span style="font-size:10px; color:var(--gray-400); font-weight:400;">(opsional)</span>
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
                            Prioritas
                            <span style="font-size:10px; color:var(--gray-400); font-weight:400;">(opsional)</span>
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
                        Lampiran
                        <span style="font-size:10px; color:var(--gray-400); font-weight:400;">(opsional)</span>
                    </label>
                    <input type="file" name="attachment" class="form-control"
                           accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                    <span class="form-hint">Format: JPG, PNG, PDF, DOC. Maksimal 2MB.</span>
                </div>

                {{-- Info auto-detect --}}
                <div class="alert alert-info" style="margin-top:4px; margin-bottom:0;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Jika kategori & prioritas tidak dipilih, sistem akan mendeteksi otomatis dari deskripsi.</span>
                </div>

            </div>

            <div class="quick-modal-footer">
                <button type="button" class="btn btn-secondary"
                        onclick="closeCreateTicketModal()">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Buat Tiket
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
</script>
@endpush

</x-layout.app>
