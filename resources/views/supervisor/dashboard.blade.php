<x-layout.app title="Dashboard" pageTitle="Dashboard">

    <div class="page-header">
        <div>
            <h1 class="page-title">Selamat Datang, {{ auth()->user()->name }}! 👋</h1>
            <p class="page-subtitle">{{ now()->format('l, d F Y') }} — Ringkasan operasional IT Helpdesk</p>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-icon blue">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div>
                <div class="stat-card-value">{{ $stats['total_open'] }}</div>
                <div class="stat-card-label">Tiket Open</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-icon amber">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </div>
            <div>
                <div class="stat-card-value">{{ $stats['total_in_progress'] }}</div>
                <div class="stat-card-label">In Progress</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-icon purple">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <div class="stat-card-value">{{ $stats['total_pending'] }}</div>
                <div class="stat-card-label">Pending</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-icon green">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <div class="stat-card-value">{{ $stats['total_resolved'] }}</div>
                <div class="stat-card-label">Resolved</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-icon red">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <div class="stat-card-value">{{ $stats['sla_breached'] }}</div>
                <div class="stat-card-label">SLA Terlewat</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-icon navy">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <div class="stat-card-value">{{ $stats['today_tickets'] }}</div>
                <div class="stat-card-label">Tiket Hari Ini</div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="dashboard-grid" style="margin-bottom: 24px;">

        {{-- Weekly Trend Chart --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">📈 Tren Tiket 7 Hari Terakhir</span>
            </div>
            <div class="card-body">
                <div class="chart-wrapper">
                    <canvas id="weeklyTrendChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Status Distribution --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">🍩 Distribusi Status</span>
            </div>
            <div class="card-body" style="display:flex; align-items:center; justify-content:center;">
                <div style="width: 220px; height: 220px;">
                    <canvas id="statusDonutChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Row --}}
    <div class="dashboard-grid">

        {{-- SLA Compliance --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">🎯 SLA Compliance Rate</span>
            </div>
            <div class="card-body">
                <div class="compliance-meter">
                    <div class="compliance-circle">
                        <svg width="120" height="120" viewBox="0 0 120 120">
                            <circle cx="60" cy="60" r="50" fill="none"
                                    stroke="var(--gray-100)" stroke-width="12"/>
                            <circle cx="60" cy="60" r="50" fill="none"
                                    stroke="{{ $stats['sla_compliance'] >= 80 ? '#16a34a' : ($stats['sla_compliance'] >= 60 ? '#d97706' : '#dc2626') }}"
                                    stroke-width="12"
                                    stroke-dasharray="{{ 2 * 3.14159 * 50 }}"
                                    stroke-dashoffset="{{ 2 * 3.14159 * 50 * (1 - $stats['sla_compliance'] / 100) }}"
                                    stroke-linecap="round"/>
                        </svg>
                        <div class="compliance-value">
                            <span class="compliance-percentage">{{ $stats['sla_compliance'] }}%</span>
                            <span class="compliance-label">Compliance</span>
                        </div>
                    </div>
                    <div style="text-align:center;">
                        @if($stats['sla_compliance'] >= 80)
                            <span class="badge badge-resolved">Performa Baik</span>
                        @elseif($stats['sla_compliance'] >= 60)
                            <span class="badge badge-pending">Perlu Perhatian</span>
                        @else
                            <span class="badge badge-high">Performa Rendah</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Category Distribution --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">📊 Tiket per Kategori</span>
            </div>
            <div class="card-body">
                <div class="chart-wrapper">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
    <script>
        // Weekly Trend Chart
        const weeklyCtx = document.getElementById('weeklyTrendChart').getContext('2d');
        new Chart(weeklyCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($stats['weekly_trend']->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))) !!},
                datasets: [{
                    label: 'Jumlah Tiket',
                    data: {!! json_encode($stats['weekly_trend']->pluck('count')) !!},
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37,99,235,0.08)',
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#2563eb',
                    pointRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } },
                    x: { grid: { display: false } }
                }
            }
        });

        // Status Donut Chart
        const statusCtx = document.getElementById('statusDonutChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Open', 'In Progress', 'Pending', 'Resolved', 'Closed'],
                datasets: [{
                    data: [
                        {{ $stats['total_open'] }},
                        {{ $stats['total_in_progress'] }},
                        {{ $stats['total_pending'] }},
                        {{ $stats['total_resolved'] }},
                        {{ $stats['total_closed'] }},
                    ],
                    backgroundColor: ['#6b7280','#2563eb','#d97706','#16a34a','#374151'],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { font: { family: 'Montserrat', size: 11 }, padding: 12 }
                    }
                },
                cutout: '65%',
            }
        });

        // Category Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($stats['category_distribution']->pluck('category.name')) !!},
                datasets: [{
                    label: 'Jumlah Tiket',
                    data: {!! json_encode($stats['category_distribution']->pluck('count')) !!},
                    backgroundColor: '#2563eb',
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } },
                    x: { grid: { display: false } }
                }
            }
        });
    </script>
    @endpush

</x-layout.app>
