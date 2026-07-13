<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\SlaRecord;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;


class ReportController extends Controller
{
    public function index(Request $request)
{
    // Handle filter period
    [$startDate, $endDate] = $this->resolveDateRange($request);

    $tickets = Ticket::with(['reporter', 'category', 'priority', 'slaRecord'])
        ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
        ->latest()
        ->paginate(15);

    $summary    = $this->getSummary($startDate, $endDate);
    $filterType = $request->filter_type ?? 'custom';

    return view('supervisor.reports.index', compact(
        'tickets', 'summary', 'startDate', 'endDate', 'filterType'
    ));
}

    private function resolveDateRange(Request $request): array
    {
        $filterType = $request->filter_type ?? 'custom';

        return match($filterType) {
            'this_week'  => [
                now()->startOfWeek()->toDateString(),
                now()->endOfWeek()->toDateString(),
            ],
            'last_week'  => [
                now()->subWeek()->startOfWeek()->toDateString(),
                now()->subWeek()->endOfWeek()->toDateString(),
            ],
            'this_month' => [
                now()->startOfMonth()->toDateString(),
                now()->endOfMonth()->toDateString(),
            ],
            'last_month' => [
                now()->subMonth()->startOfMonth()->toDateString(),
                now()->subMonth()->endOfMonth()->toDateString(),
            ],
            'this_year'  => [
                now()->startOfYear()->toDateString(),
                now()->endOfYear()->toDateString(),
            ],
            'last_year'  => [
                now()->subYear()->startOfYear()->toDateString(),
                now()->subYear()->endOfYear()->toDateString(),
            ],
            default => [ // custom
                $request->start_date ?? now()->startOfMonth()->toDateString(),
                $request->end_date   ?? now()->toDateString(),
            ],
        };
    }

    public function exportPdf(Request $request)
    {
        [$startDate, $endDate] = $this->resolveDateRange($request);
        $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
        $endDate   = $request->end_date ?? now()->toDateString();

        $tickets = Ticket::with(['reporter', 'category', 'priority', 'slaRecord'])
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->latest()
            ->get();

        $summary = $this->getSummary($startDate, $endDate);

        $pdf = Pdf::loadView('supervisor.reports.pdf', compact('tickets', 'summary', 'startDate', 'endDate'))
                  ->setPaper('A4', 'landscape');

        return $pdf->download('laporan-it-' . $startDate . '-sd-' . $endDate . '.pdf');
    }

    public function exportExcel(Request $request)
{
    [$startDate, $endDate] = $this->resolveDateRange($request);
    $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
    $endDate   = $request->end_date ?? now()->toDateString();

    $tickets = Ticket::with(['reporter', 'category', 'priority', 'slaRecord'])
        ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
        ->latest()
        ->get();

    $summary = $this->getSummary($startDate, $endDate);

    // ── Buat Spreadsheet ──────────────────────
    $spreadsheet = new Spreadsheet();
    $sheet       = $spreadsheet->getActiveSheet();
    $sheet->setTitle('IT Helpdesk Reports');

    // ── Header Judul ──────────────────────────
    $sheet->mergeCells('A1:I1');
    $sheet->setCellValue('A1', 'IT HELPDESK REPORTS — KTU SHIPYARD');
    $sheet->getStyle('A1')->applyFromArray([
        'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0F2044']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    ]);
    $sheet->getRowDimension(1)->setRowHeight(30);

    // ── Sub Header ────────────────────────────
    $sheet->mergeCells('A2:I2');
    $sheet->setCellValue('A2', 'Periode: ' . \Carbon\Carbon::parse($startDate)->format('d F Y') . ' — ' . \Carbon\Carbon::parse($endDate)->format('d F Y'));
    $sheet->getStyle('A2')->applyFromArray([
        'font'      => ['size' => 10, 'color' => ['rgb' => 'FFFFFF']],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1A3160']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    ]);

    // ── Summary ───────────────────────────────
    $sheet->setCellValue('A4', 'SUMMARY');
    $sheet->getStyle('A4')->getFont()->setBold(true);

    $summaryData = [
        ['Total Tickets',       $summary['total']],
        ['Resolved',      $summary['resolved']],
        ['SLA Fulfilled',     $summary['sla_met']],
        ['SLA Breached',      $summary['sla_breached']],
        ['Pending',       $summary['with_pending']],
        ['Without Pending',     $summary['without_pending']],
        ['Compliance Rate',   $summary['compliance_rate'] . '%'],
        ['Average Resolution Time', $summary['avg_resolution']],
    ];

    $row = 5;
    foreach ($summaryData as $item) {
        $sheet->setCellValue('A' . $row, $item[0]);
        $sheet->setCellValue('B' . $row, $item[1]);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F3F4F6');
        $row++;
    }

    // ── Header Tabel ─────────────────────────
    $headerRow = $row + 1;
    $headers   = ['No. Ticket', 'Reporter', 'Department', 'Category', 'Priority', 'Status', 'SLA', 'Pending', 'Created', 'Resolved'];
    $cols      = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];

    // Extend merge untuk 10 kolom
    $sheet->mergeCells('A1:J1');
    $sheet->mergeCells('A2:J2');

    foreach ($cols as $i => $col) {
        $sheet->setCellValue($col . $headerRow, $headers[$i]);
        $sheet->getStyle($col . $headerRow)->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0F2044']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ]);
    }
    $sheet->getRowDimension($headerRow)->setRowHeight(22);

    // ── Data Tiket ────────────────────────────
    $dataRow = $headerRow + 1;
    foreach ($tickets as $ticket) {
        // SLA status
        $slaStatus = '—';
        if ($ticket->slaRecord) {
            if ($ticket->slaRecord->resolution_breached)  $slaStatus = 'Breached';
            elseif ($ticket->slaRecord->resolution_met_at) $slaStatus = 'Fulfilled';
            else                                           $slaStatus = 'In Progress';
        }

        $sheet->setCellValue('A' . $dataRow, $ticket->ticket_number);
        $sheet->setCellValue('B' . $dataRow, $ticket->reporter->name);
        $sheet->setCellValue('C' . $dataRow, $ticket->reporter->department?->name ?? '—');
        $sheet->setCellValue('D' . $dataRow, $ticket->category?->name ?? '—');
        $sheet->setCellValue('E' . $dataRow, ucfirst($ticket->priority?->level ?? '—'));
        $sheet->setCellValue('F' . $dataRow, ucfirst(str_replace('_', ' ', $ticket->status)));
        $sheet->setCellValue('G' . $dataRow, $slaStatus);
        $sheet->setCellValue('H' . $dataRow, $ticket->had_pending ? $ticket->pending_count . 'x' : '—');
        $sheet->setCellValue('I' . $dataRow, $ticket->created_at->format('d/m/Y'));
        $sheet->setCellValue('J' . $dataRow, $ticket->resolved_at?->format('d/m/Y') ?? '—');

        // Warna baris alternating
        if ($dataRow % 2 === 0) {
            $sheet->getStyle('A' . $dataRow . ':J' . $dataRow)
                  ->getFill()->setFillType(Fill::FILL_SOLID)
                  ->getStartColor()->setRGB('F9FAFB');
        }

        // Warna SLA breach
        if ($slaStatus === 'Breached') {
            $sheet->getStyle('G' . $dataRow)
                  ->getFont()->getColor()->setRGB('DC2626');
            $sheet->getStyle('G' . $dataRow)
                  ->getFont()->setBold(true);
        } elseif ($slaStatus === 'Fulfilled') {
            $sheet->getStyle('G' . $dataRow)
                  ->getFont()->getColor()->setRGB('16A34A');
        }

        $dataRow++;
    }

    // ── Auto width kolom ──────────────────────
    foreach ($cols as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // ── Border seluruh tabel ──────────────────
    $sheet->getStyle('A' . $headerRow . ':J' . ($dataRow - 1))
          ->getBorders()->getAllBorders()
          ->setBorderStyle(Border::BORDER_THIN);

    // ── Download ──────────────────────────────
    $filename = 'laporan-it-' . $startDate . '-sd-' . $endDate . '.xlsx';

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

    private function getSummary(string $startDate, string $endDate): array
    {
        $tickets = Ticket::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59']);

        $totalTickets  = (clone $tickets)->count();
        $resolved      = (clone $tickets)->whereIn('status', ['resolved', 'closed'])->count();
        $withPending   = (clone $tickets)->where('had_pending', true)->count();
        $withoutPending = (clone $tickets)->where('had_pending', false)->whereIn('status', ['resolved', 'closed'])->count();

        $slaBreached = SlaRecord::whereHas('ticket', function ($q) use ($startDate, $endDate) {
            $q->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59']);
        })->where('resolution_breached', true)->count();

        $avgResolution = Ticket::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->whereNotNull('resolved_at')
            ->get()
            ->avg(fn($t) => $t->created_at->diffInMinutes($t->resolved_at));

        return [
            'total'            => $totalTickets,
            'resolved'         => $resolved,
            'with_pending'     => $withPending,
            'without_pending'  => $withoutPending,
            'sla_breached'     => $slaBreached,
            'sla_met'          => $resolved - $slaBreached,
            'avg_resolution'   => $avgResolution ? round($avgResolution / 60, 1) . ' jam' : '-',
            'compliance_rate'  => $resolved > 0 ? round((($resolved - $slaBreached) / $resolved) * 100, 1) : 0,
        ];
    }
}
