<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\SlaRecord;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_open'        => Ticket::where('status', 'open')->count(),
            'total_in_progress' => Ticket::where('status', 'in_progress')->count(),
            'total_pending'     => Ticket::where('status', 'pending')->count(),
            'total_resolved'    => Ticket::where('status', 'resolved')->count(),
            'total_closed'      => Ticket::where('status', 'closed')->count(),
            'sla_breached'      => SlaRecord::where('resolution_breached', true)->count(),

            // Tiket hari ini
            'today_tickets' => Ticket::whereDate('created_at', today())->count(),

            // Tren 7 hari terakhir
            'weekly_trend' => Ticket::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->where('created_at', '>=', now()->subDays(7))
                ->groupBy('date')
                ->orderBy('date')
                ->get(),

            // Distribusi status
            'status_distribution' => Ticket::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get(),

            // Tiket per kategori
            'category_distribution' => Ticket::selectRaw('category_id, COUNT(*) as count')
                ->with('category')
                ->groupBy('category_id')
                ->get(),

            // SLA compliance rate
            'sla_compliance' => $this->getSlaComplianceRate(),
        ];

        return view('supervisor.dashboard', compact('stats'));
    }

    private function getSlaComplianceRate(): float
    {
        $total    = SlaRecord::whereNotNull('resolution_met_at')->count();
        $onTime   = SlaRecord::whereNotNull('resolution_met_at')
                             ->where('resolution_breached', false)
                             ->count();

        return $total > 0 ? round(($onTime / $total) * 100, 1) : 0;
    }
}
