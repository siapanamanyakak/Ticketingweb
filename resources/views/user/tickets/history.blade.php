<x-layout.app title="Ticket History" pageTitle="Ticket History">

    <div class="page-header">
        <div>
            <h1 class="page-title">Ticket History</h1>
            <p class="page-subtitle">Your completed and closed tickets</p>
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
                <input type="text" name="search" placeholder="Search ticket..."
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
                    <a href="{{ route('user.tickets.history') }}"
                       class="btn btn-secondary btn-sm">Reset</a>
                @endif
            </div>

            {{-- Active filters indicator --}}
            @if(request()->hasAny(['search', 'year', 'month', 'day', 'priority', 'category']))
                <div style="margin-top:10px; display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                    <span style="font-size:11px; color:var(--gray-400); font-weight:600;">Active Filters:</span>
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
                        closed: {{ $ticket->closed_at?->format('d M Y, H:i') ?? '-' }}
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
                            <span class="badge badge-high">SLA Missed</span>
                        @else
                            <span class="badge badge-resolved">SLA On-Time</span>
                        @endif
                    @endif
                </div>

                <p class="ticket-card-description">{{ $ticket->description }}</p>

                <div class="ticket-card-footer">
                    <div style="font-size:12px; color:var(--gray-500);">
                        Created: {{ $ticket->created_at->format('d M Y') }}
                        · Resolved: {{ $ticket->resolved_at?->format('d M Y') ?? '-' }}
                    </div>
                    <div class="ticket-card-actions">
                        <x-ui.badge-priority :priority="$ticket->priority?->level ?? 'low'" />
                        <x-ui.badge-status :status="$ticket->status" />
                        <a href="{{ route('user.tickets.show', $ticket) }}" class="btn btn-secondary btn-sm">
                            View Details
                        </a>
                    </div>
                </div>
            </div>
        @endforeach

        <x-ui.pagination :paginator="$tickets" />
    @else
        <div class="card">
            <x-ui.empty-state
                title="No ticket history available"
                description="Tickets you have closed will appear here."
            />
        </div>
    @endif

</x-layout.app>
