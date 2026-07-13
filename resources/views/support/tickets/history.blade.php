<x-layout.app title="Ticket History" pageTitle="Ticket History">

    <div class="page-header">
        <div>
            <h1 class="page-title">Ticket History</h1>
            <p class="page-subtitle">All closed tickets from all reporters</p>
        </div>
    </div>

    <div class="card" style="margin-bottom:20px;">
    <div class="card-body" style="padding:16px 20px;">
        <form method="GET" action="{{ route('support.tickets.history') }}" id="historyFilterForm">

            {{-- Search --}}
            <div class="search-box" style="margin-bottom:12px;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" placeholder="Search tickets or staff..."
                       value="{{ request('search') }}">
            </div>

            {{-- Filter Row --}}
            <div class="filters-bar">

                {{-- Tahun — dari database, otomatis bertambah --}}
                <select name="year" class="filter-select" onchange="this.form.submit()">
                    <option value="">All Years</option>
                    @foreach($availableYears as $year)
                        <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endforeach
                </select>

                {{-- Bulan --}}
                <select name="month" class="filter-select" onchange="this.form.submit()">
                    <option value="">All Months</option>
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->locale('id')->monthName }}
                        </option>
                    @endforeach
                </select>

                {{-- Priority --}}
                <select name="priority" class="filter-select" onchange="this.form.submit()">
                    <option value="">All Priorities</option>
                    @foreach(\App\Models\Priority::orderBy('id')->get() as $p)
                        <option value="{{ $p->level }}" {{ request('priority') === $p->level ? 'selected' : '' }}>
                            {{ $p->name }}
                        </option>
                    @endforeach
                </select>

                {{-- Kategori --}}
                <select name="category" class="filter-select" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    @foreach(\App\Models\Category::where('is_active', true)->get() as $cat)
                        <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>

                {{-- Tombol --}}
                <button type="submit" class="btn btn-primary btn-sm">Search</button>

                @if(request()->hasAny(['search', 'year', 'month', 'day', 'priority', 'category']))
                    <a href="{{ route('support.tickets.history') }}"
                       class="btn btn-secondary btn-sm">Reset</a>
                @endif
            </div>

            {{-- Active filters indicator --}}
            @if(request()->hasAny(['search', 'year', 'month', 'day', 'priority', 'category']))
                <div style="margin-top:10px; display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                    <span style="font-size:11px; color:var(--gray-400); font-weight:600;">Active filters:</span>
                    @if(request('year'))
                        <span style="background:var(--navy-100); color:var(--navy-600);
                                     padding:2px 10px; border-radius:20px; font-size:11px; font-weight:600;">
                            Year: {{ request('year') }}
                        </span>
                    @endif
                    @if(request('month'))
                        <span style="background:var(--navy-100); color:var(--navy-600);
                                     padding:2px 10px; border-radius:20px; font-size:11px; font-weight:600;">
                            Month: {{ \Carbon\Carbon::create()->month(request('month'))->locale('id')->monthName }}
                        </span>
                    @endif
                    @if(request('priority'))
                        <span style="background:var(--navy-100); color:var(--navy-600);
                                     padding:2px 10px; border-radius:20px; font-size:11px; font-weight:600;">
                            Priority: {{ ucfirst(request('priority')) }}
                        </span>
                    @endif
                    @if(request('category'))
                        @php $cat = \App\Models\Category::find(request('category')); @endphp
                        <span style="background:var(--navy-100); color:var(--navy-600);
                                     padding:2px 10px; border-radius:20px; font-size:11px; font-weight:600;">
                            Category: {{ $cat?->name }}
                        </span>
                    @endif
                    @if(request('search'))
                        <span style="background:var(--navy-100); color:var(--navy-600);
                                     padding:2px 10px; border-radius:20px; font-size:11px; font-weight:600;">
                            Search: "{{ request('search') }}"
                        </span>
                    @endif
                </div>
            @endif

        </form>
    </div>
</div>

    @if($tickets->count() > 0)
        @foreach($tickets as $ticket)
            <x-ticket.card :ticket="$ticket" />
        @endforeach
        <x-ui.pagination :paginator="$tickets" />
    @else
        <div class="card">
            <x-ui.empty-state
                title="No history tickets yet"
                description="No closed tickets yet."
            />
        </div>
    @endif

</x-layout.app>
