<x-layout.app title="Ticket List" pageTitle="Ticket List">

    <div class="page-header">
        <div>
            <h1 class="page-title">Ticket List</h1>
            <p class="page-subtitle">Monitor all tickets that come into the system</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-body" style="padding: 16px 20px;">
            <form method="GET">
                <div class="filters-bar">
                    <div class="search-box">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" name="search" placeholder="Cari tiket..."
                               value="{{ request('search') }}">
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

                    @if(request()->hasAny(['search', 'status', 'priority', 'category']))
                        <a href="{{ route('supervisor.tickets.index') }}" class="btn btn-secondary btn-sm">Reset</a>
                    @endif

                    <button type="submit" class="btn btn-primary btn-sm">Search</button>
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
        <a href="{{ route('supervisor.tickets.index', array_merge(request()->except(['tab','year','month','day']), ['tab' => $key])) }}"
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
                        <a href="{{ route('supervisor.tickets.index', ['tab' => 'closed']) }}"
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
                title="No tickets found"
                description="No tickets match the selected filter."
            />
        </div>
    @endif

</x-layout.app>
