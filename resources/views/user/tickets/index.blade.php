<x-layout.app title="Tiket Saya" pageTitle="Tiket Saya">

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
            <form method="GET" action="{{ route('user.tickets.index') }}">
                <div class="filters-bar">
                    <div class="search-box">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" name="search" placeholder="Cari tiket..."
                               value="{{ request('search') }}">
                    </div>

                    <select name="status" class="filter-select" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="open"        {{ request('status') === 'open'        ? 'selected' : '' }}>Open</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="pending"     {{ request('status') === 'pending'     ? 'selected' : '' }}>Pending</option>
                        <option value="resolved"    {{ request('status') === 'resolved'    ? 'selected' : '' }}>Resolved</option>
                        <option value="closed"      {{ request('status') === 'closed'      ? 'selected' : '' }}>Closed</option>
                    </select>

                    <select name="priority" class="filter-select" onchange="this.form.submit()">
                        <option value="">Semua Prioritas</option>
                        <option value="low"      {{ request('priority') === 'low'      ? 'selected' : '' }}>Low</option>
                        <option value="medium"   {{ request('priority') === 'medium'   ? 'selected' : '' }}>Medium</option>
                        <option value="high"     {{ request('priority') === 'high'     ? 'selected' : '' }}>High</option>
                        <option value="critical" {{ request('priority') === 'critical' ? 'selected' : '' }}>Critical</option>
                    </select>

                    @if(request()->hasAny(['search', 'status', 'priority']))
                        <a href="{{ route('user.tickets.index') }}" class="btn btn-secondary btn-sm">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

{{-- Tabs --}}
@php
    $activeTab = request('tab', 'active');
    $tabs = [
        'active'      => ['label' => 'Aktif',       'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',       'count' => $totalActive],
        'open'        => ['label' => 'Open',         'icon' => 'M12 4v16m8-8H4',                                                                                                                         'count' => $tabCounts['open'] ?? 0],
        'in_progress' => ['label' => 'In Progress',  'icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',                           'count' => $tabCounts['in_progress'] ?? 0],
        'pending'     => ['label' => 'Pending',      'icon' => 'M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z',                                                                                          'count' => $tabCounts['pending'] ?? 0],
        'resolved'    => ['label' => 'Resolved',     'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',                                                                                          'count' => $tabCounts['resolved'] ?? 0],
    ];
@endphp

<div class="ticket-tabs">
    @foreach($tabs as $key => $tab)
        <a href="{{ route('user.tickets.index', array_merge(request()->except(['tab','year','month','day']), ['tab' => $key])) }}"
           class="ticket-tab {{ $activeTab === $key ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="15" height="15">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $tab['icon'] }}"/>
            </svg>
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
                        <a href="{{ route('user.tickets.index', ['tab' => 'closed']) }}"
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
            title="Belum ada tiket"
            description="Kamu belum pernah membuat tiket. Buat tiket pertamamu sekarang!"
        >
            {{-- Ini disebut Slot. Semua di dalam sini akan dikirim ke komponen --}}
            <button onclick="openCreateTicketModal()" class="btn btn-primary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20" style="display: inline-block; margin-right: 4px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Buat Tiket Baru
            </button>
        </x-ui.empty-state>
    </div>
@endif


{{-- Modal Buat Tiket --}}
<div class="quick-modal-overlay" id="createTicketOverlay">
    <div class="quick-modal" style="max-width:520px;">
        <div class="quick-modal-header">
            <span class="quick-modal-title">🎫 Buat Tiket Baru</span>
            <button class="quick-modal-close" onclick="closeCreateTicketModal()">✕</button>
        </div>

        <form method="POST" action="{{ route('user.tickets.store') }}"
              enctype="multipart/form-data" id="createTicketForm">
            @csrf

            <div class="quick-modal-body" style="max-height:70vh; overflow-y:auto;">

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
                    <textarea name="description" class="form-control" rows="4"
                              placeholder="Jelaskan masalah kamu secara detail. Sistem akan otomatis menentukan kategori dan prioritas."
                              required>{{ old('description') }}</textarea>
                    @error('description')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
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

                {{-- Info --}}
                <div class="alert alert-info" style="margin-bottom:0;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Kategori dan prioritas akan ditentukan otomatis oleh sistem.</span>
                </div>

            </div>

            <div class="quick-modal-footer">
                <button type="button" class="btn btn-secondary"
                        onclick="closeCreateTicketModal()">Batal</button>
                <button type="submit" class="btn btn-primary">
                    Kirim Tiket
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

    document.getElementById('createTicketOverlay').addEventListener('click', function(e) {
        if (e.target === this) closeCreateTicketModal();
    });

    // Auto buka modal kalau ada error validasi
    @if($errors->any())
        openCreateTicketModal();
    @endif
</script>
@endpush

</x-layout.app>
