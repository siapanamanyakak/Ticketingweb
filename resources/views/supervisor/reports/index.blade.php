<x-layout.app title="Laporan" pageTitle="Laporan Layanan IT">

    <div class="page-header">
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
            <div>
                <h1 class="page-title">Laporan Layanan IT</h1>
                <p class="page-subtitle">Evaluasi kinerja operasional IT Helpdesk</p>
            </div>
            <div style="display:flex; gap:8px;">
                <a href="{{ route('supervisor.reports.export-pdf', array_merge(request()->query(), ['filter_type' => $filterType, 'start_date' => $startDate, 'end_date' => $endDate])) }}"
                   class="btn btn-danger">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Export PDF
                </a>
                <a href="{{ route('supervisor.reports.export-excel', array_merge(request()->query(), ['filter_type' => $filterType, 'start_date' => $startDate, 'end_date' => $endDate])) }}"
                    class="btn btn-success">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                    </svg>
                    Export Excel
                </a>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="report-filters">
        <form method="GET" style="display:flex; align-items:center; gap:10px; flex-wrap:wrap; width:100%;">

            {{-- Quick filter buttons --}}
            <div style="display:flex; gap:6px; flex-wrap:wrap;">
                @php
                    $quickFilters = [
                        'this_week'  => 'Minggu Ini',
                        'last_week'  => 'Minggu Lalu',
                        'this_month' => 'Bulan Ini',
                        'last_month' => 'Bulan Lalu',
                        'this_year'  => 'Tahun Ini',
                        'last_year'  => 'Tahun Lalu',
                        'custom'     => 'Custom',
                    ];
                @endphp

                @foreach($quickFilters as $value => $label)
                    <button type="submit" name="filter_type" value="{{ $value }}"
                            class="btn btn-sm {{ $filterType === $value ? 'btn-primary' : 'btn-secondary' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            {{-- Custom date range (hanya tampil saat custom) --}}
            <div id="customDateRange"
                style="display:{{ $filterType === 'custom' ? 'flex' : 'none' }};
                        align-items:center; gap:8px; flex-wrap:wrap;">
                <label style="font-size:12px; font-weight:600; color:var(--gray-500);">Dari:</label>
                <input type="date" name="start_date" class="form-control"
                    style="width:auto;" value="{{ $startDate }}">
                <label style="font-size:12px; font-weight:600; color:var(--gray-500);">Sampai:</label>
                <input type="date" name="end_date" class="form-control"
                    style="width:auto;" value="{{ $endDate }}">
                <button type="submit" name="filter_type" value="custom"
                        class="btn btn-primary btn-sm">Terapkan</button>
            </div>

            {{-- Info periode aktif --}}
            <div style="margin-left:auto; font-size:12px; color:var(--gray-500); font-weight:500;">
                📅 {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}
                — {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
            </div>

        </form>
    </div>

    @push('scripts')
    <script>
        // Tampilkan custom date range saat filter custom dipilih
        document.querySelectorAll('button[name="filter_type"]').forEach(btn => {
            btn.addEventListener('click', function() {
                const customRange = document.getElementById('customDateRange');
                customRange.style.display = this.value === 'custom' ? 'flex' : 'none';
            });
        });
    </script>
    @endpush

    {{-- Summary Cards --}}
    <div class="report-summary-grid">
        <div class="report-summary-item">
            <div class="report-summary-value">{{ $summary['total'] }}</div>
            <div class="report-summary-label">Total Tiket</div>
        </div>
        <div class="report-summary-item">
            <div class="report-summary-value" style="color:#16a34a;">{{ $summary['resolved'] }}</div>
            <div class="report-summary-label">Diselesaikan</div>
        </div>
        <div class="report-summary-item">
            <div class="report-summary-value" style="color:#2563eb;">{{ $summary['sla_met'] }}</div>
            <div class="report-summary-label">SLA Terpenuhi</div>
        </div>
        <div class="report-summary-item">
            <div class="report-summary-value" style="color:#dc2626;">{{ $summary['sla_breached'] }}</div>
            <div class="report-summary-label">SLA Terlewat</div>
        </div>
        <div class="report-summary-item">
            <div class="report-summary-value" style="color:#d97706;">{{ $summary['with_pending'] }}</div>
            <div class="report-summary-label">Ada Pending</div>
        </div>
        <div class="report-summary-item">
            <div class="report-summary-value">{{ $summary['without_pending'] }}</div>
            <div class="report-summary-label">Tanpa Pending</div>
        </div>
        <div class="report-summary-item">
            <div class="report-summary-value" style="color:#2563eb;">{{ $summary['compliance_rate'] }}%</div>
            <div class="report-summary-label">Compliance Rate</div>
        </div>
        <div class="report-summary-item">
            <div class="report-summary-value">{{ $summary['avg_resolution'] }}</div>
            <div class="report-summary-label">Rata-rata Selesai</div>
        </div>
    </div>

    {{-- Ticket Table --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Detail Tiket ({{ $tickets->total() }} tiket)</span>
        </div>
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nomor Tiket</th>
                        <th>Pelapor</th>
                        <th>Kategori</th>
                        <th>Prioritas</th>
                        <th>Status</th>
                        <th>SLA</th>
                        <th>Pending</th>
                        <th>Dibuat</th>
                        <th>Selesai</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                        <tr>
                            <td>
                                <a href="{{ route('supervisor.tickets.show', $ticket) }}"
                                   style="font-weight:700; color:var(--navy-600); text-decoration:none;">
                                    {{ $ticket->ticket_number }}
                                </a>
                            </td>
                            <td>
                                <div style="font-size:13px; font-weight:600;">{{ $ticket->reporter->name }}</div>
                                <div style="font-size:11px; color:var(--gray-400);">{{ $ticket->reporter->department?->name }}</div>
                            </td>
                            <td>{{ $ticket->category?->name ?? '—' }}</td>
                            <td><x-ui.badge-priority :priority="$ticket->priority?->level ?? 'low'" /></td>
                            <td><x-ui.badge-status :status="$ticket->status" /></td>
                            <td>
                                @if($ticket->slaRecord)
                                    @if($ticket->slaRecord->resolution_breached)
                                        <span class="badge badge-high">Terlewat</span>
                                    @elseif($ticket->slaRecord->resolution_met_at)
                                        <span class="badge badge-resolved">Tepat Waktu</span>
                                    @else
                                        <span class="badge badge-pending">Berjalan</span>
                                    @endif
                                @else
                                    <span style="color:var(--gray-400);">—</span>
                                @endif
                            </td>
                            <td>
                                @if($ticket->had_pending)
                                    <span style="color:#d97706; font-weight:600;">{{ $ticket->pending_count }}x</span>
                                @else
                                    <span style="color:var(--gray-400);">—</span>
                                @endif
                            </td>
                            <td style="font-size:12px; color:var(--gray-500);">
                                {{ $ticket->created_at->format('d M Y') }}
                            </td>
                            <td style="font-size:12px; color:var(--gray-500);">
                                {{ $ticket->resolved_at?->format('d M Y') ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <x-ui.empty-state
                                    title="Tidak ada data"
                                    description="Tidak ada tiket pada periode yang dipilih."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tickets->hasPages())
            <div style="padding: 0 20px;">
                <x-ui.pagination :paginator="$tickets" />
            </div>
        @endif
    </div>

</x-layout.app>
