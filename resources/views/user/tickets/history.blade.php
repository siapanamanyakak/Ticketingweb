<x-layout.app title="Riwayat Tiket" pageTitle="Riwayat Tiket">

    <div class="page-header">
        <div>
            <h1 class="page-title">Riwayat Tiket</h1>
            <p class="page-subtitle">Tiket kamu yang telah selesai dan ditutup</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card" style="margin-bottom:20px;">
    <div class="card-body" style="padding:16px 20px;">
        <form method="GET" action="{{ route('user.tickets.history') }}" id="historyFilterForm">

            {{-- Search --}}
            <div class="search-box" style="margin-bottom:12px;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" placeholder="Cari tiket atau nama staff..."
                       value="{{ request('search') }}">
            </div>

            {{-- Filter Row --}}
            <div class="filters-bar">

                {{-- Tahun — dari database, otomatis bertambah --}}
                <select name="year" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Tahun</option>
                    @foreach($availableYears as $year)
                        <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endforeach
                </select>

                {{-- Bulan --}}
                <select name="month" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Bulan</option>
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->locale('id')->monthName }}
                        </option>
                    @endforeach
                </select>

                {{-- Priority --}}
                <select name="priority" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Prioritas</option>
                    @foreach(\App\Models\Priority::orderBy('id')->get() as $p)
                        <option value="{{ $p->level }}" {{ request('priority') === $p->level ? 'selected' : '' }}>
                            {{ $p->name }}
                        </option>
                    @endforeach
                </select>

                {{-- Kategori --}}
                <select name="category" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Kategori</option>
                    @foreach(\App\Models\Category::where('is_active', true)->get() as $cat)
                        <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>

                {{-- Tombol --}}
                <button type="submit" class="btn btn-primary btn-sm">Cari</button>

                @if(request()->hasAny(['search', 'year', 'month', 'day', 'priority', 'category']))
                    <a href="{{ route('user.tickets.history') }}"
                       class="btn btn-secondary btn-sm">Reset</a>
                @endif
            </div>

            {{-- Active filters indicator --}}
            @if(request()->hasAny(['search', 'year', 'month', 'day', 'priority', 'category']))
                <div style="margin-top:10px; display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                    <span style="font-size:11px; color:var(--gray-400); font-weight:600;">Filter aktif:</span>
                    @if(request('year'))
                        <span style="background:var(--navy-100); color:var(--navy-600);
                                     padding:2px 10px; border-radius:20px; font-size:11px; font-weight:600;">
                            Tahun: {{ request('year') }}
                        </span>
                    @endif
                    @if(request('month'))
                        <span style="background:var(--navy-100); color:var(--navy-600);
                                     padding:2px 10px; border-radius:20px; font-size:11px; font-weight:600;">
                            Bulan: {{ \Carbon\Carbon::create()->month(request('month'))->locale('id')->monthName }}
                        </span>
                    @endif
                    @if(request('priority'))
                        <span style="background:var(--navy-100); color:var(--navy-600);
                                     padding:2px 10px; border-radius:20px; font-size:11px; font-weight:600;">
                            Prioritas: {{ ucfirst(request('priority')) }}
                        </span>
                    @endif
                    @if(request('category'))
                        @php $cat = \App\Models\Category::find(request('category')); @endphp
                        <span style="background:var(--navy-100); color:var(--navy-600);
                                     padding:2px 10px; border-radius:20px; font-size:11px; font-weight:600;">
                            Kategori: {{ $cat?->name }}
                        </span>
                    @endif
                    @if(request('search'))
                        <span style="background:var(--navy-100); color:var(--navy-600);
                                     padding:2px 10px; border-radius:20px; font-size:11px; font-weight:600;">
                            Cari: "{{ request('search') }}"
                        </span>
                    @endif
                </div>
            @endif

        </form>
    </div>
</div>

    {{-- Ticket List --}}
    @if($tickets->count() > 0)
        @foreach($tickets as $ticket)
            <div class="ticket-card priority-{{ $ticket->priority?->level ?? 'low' }}" style="opacity:0.85;">
                <div class="ticket-card-header">
                    <div class="ticket-card-left">
                        <span class="ticket-number">{{ $ticket->ticket_number }}</span>
                        <span class="ticket-title">{{ $ticket->title }}</span>
                    </div>
                    <span class="ticket-response-time">
                        Ditutup: {{ $ticket->closed_at?->format('d M Y, H:i') ?? '-' }}
                    </span>
                </div>

                <div class="ticket-card-meta">
                    <span class="ticket-meta-item">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        {{ $ticket->category?->name ?? 'Uncategorized' }}
                    </span>

                    @if($ticket->slaRecord)
                        @if($ticket->slaRecord->resolution_breached)
                            <span class="badge badge-high">SLA Terlewat</span>
                        @else
                            <span class="badge badge-resolved">SLA Tepat Waktu</span>
                        @endif
                    @endif

                    @if($ticket->had_pending)
                        <span class="badge badge-pending">Pernah Pending {{ $ticket->pending_count }}x</span>
                    @endif
                </div>

                <p class="ticket-card-description">{{ $ticket->description }}</p>

                <div class="ticket-card-footer">
                    <div style="font-size:12px; color:var(--gray-500);">
                        Dibuat: {{ $ticket->created_at->format('d M Y') }}
                        · Selesai: {{ $ticket->resolved_at?->format('d M Y') ?? '-' }}
                    </div>
                    <div class="ticket-card-actions">
                        <x-ui.badge-priority :priority="$ticket->priority?->level ?? 'low'" />
                        <x-ui.badge-status :status="$ticket->status" />
                        <a href="{{ route('user.tickets.show', $ticket) }}" class="btn btn-secondary btn-sm">
                            Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
        @endforeach

        <x-ui.pagination :paginator="$tickets" />
    @else
        <div class="card">
            <x-ui.empty-state
                title="Belum ada riwayat tiket"
                description="Tiket yang sudah ditutup akan muncul di sini."
            />
        </div>
    @endif

</x-layout.app>
