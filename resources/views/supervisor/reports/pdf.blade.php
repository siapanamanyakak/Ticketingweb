<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan IT Helpdesk</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #111827; }
        .header { background: #0f2044; color: white; padding: 20px 24px; margin-bottom: 20px; }
        .header h1 { font-size: 18px; font-weight: 700; margin-bottom: 4px; }
        .header p { font-size: 11px; opacity: 0.8; }
        .summary-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 20px; }
        .summary-item { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; text-align: center; }
        .summary-value { font-size: 22px; font-weight: 800; color: #111827; }
        .summary-label { font-size: 10px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; }
        table { width: 100%; border-collapse: collapse; font-size: 10px; }
        th { background: #f3f4f6; padding: 8px 10px; text-align: left; font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; border-bottom: 1px solid #e5e7eb; }
        td { padding: 8px 10px; border-bottom: 1px solid #f3f4f6; color: #374151; }
        tr:nth-child(even) td { background: #fafafa; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: 600; }
        .badge-resolved  { background: #dcfce7; color: #15803d; }
        .badge-high      { background: #fee2e2; color: #b91c1c; }
        .badge-pending   { background: #fef3c7; color: #b45309; }
        .badge-in-progress { background: #dbeafe; color: #1d4ed8; }
        .badge-open      { background: #f3f4f6; color: #4b5563; }
        .badge-closed    { background: #f3f4f6; color: #374151; }
        .badge-low       { background: #dcfce7; color: #15803d; }
        .badge-medium    { background: #fef3c7; color: #b45309; }
        .badge-critical  { background: #fce7f3; color: #9d174d; }
        .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 12px; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Laporan Layanan IT Helpdesk</h1>
        <p>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} — {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}</p>
        <p>Dicetak: {{ now()->format('d F Y, H:i') }}</p>
    </div>

    {{-- Summary --}}
    <div class="summary-grid">
        <div class="summary-item">
            <div class="summary-value">{{ $summary['total'] }}</div>
            <div class="summary-label">Total Tiket</div>
        </div>
        <div class="summary-item">
            <div class="summary-value" style="color:#16a34a;">{{ $summary['resolved'] }}</div>
            <div class="summary-label">Diselesaikan</div>
        </div>
        <div class="summary-item">
            <div class="summary-value" style="color:#dc2626;">{{ $summary['sla_breached'] }}</div>
            <div class="summary-label">SLA Terlewat</div>
        </div>
        <div class="summary-item">
            <div class="summary-value" style="color:#2563eb;">{{ $summary['compliance_rate'] }}%</div>
            <div class="summary-label">Compliance Rate</div>
        </div>
    </div>

    {{-- Table --}}
    <table>
        <thead>
            <tr>
                <th>No. Tiket</th>
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
            @foreach($tickets as $ticket)
                <tr>
                    <td><strong>{{ $ticket->ticket_number }}</strong></td>
                    <td>{{ $ticket->reporter->name }}</td>
                    <td>{{ $ticket->category?->name ?? '—' }}</td>
                    <td>
                        <span class="badge badge-{{ $ticket->priority?->level ?? 'low' }}">
                            {{ ucfirst($ticket->priority?->level ?? 'low') }}
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-{{ str_replace('_', '-', $ticket->status) }}">
                            {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                        </span>
                    </td>
                    <td>
                        @if($ticket->slaRecord?->resolution_breached)
                            <span class="badge badge-high">Terlewat</span>
                        @elseif($ticket->slaRecord?->resolution_met_at)
                            <span class="badge badge-resolved">Tepat Waktu</span>
                        @else
                            <span class="badge badge-pending">Berjalan</span>
                        @endif
                    </td>
                    <td>{{ $ticket->had_pending ? $ticket->pending_count . 'x' : '—' }}</td>
                    <td>{{ $ticket->created_at->format('d/m/Y') }}</td>
                    <td>{{ $ticket->resolved_at?->format('d/m/Y') ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        KTU IT Helpdesk System — Laporan dibuat otomatis pada {{ now()->format('d F Y, H:i') }}
    </div>

</body>
</html>
