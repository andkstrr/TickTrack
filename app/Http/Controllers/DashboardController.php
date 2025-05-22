<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\DashboardResource;

class DashboardController extends Controller
{
    public function getStatistics()
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        $totalTickets = Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])->count();

        $activeTickets = Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])
            ->where('status', '!=', 'resolved')
            ->count();

        $resolvedTickets = Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])
            ->where('status', 'resolved')
            ->count();

        // Menghitung rata-rata waktu yg dibutuhkan untuk resolved tiket
        $avgResolutionTime = Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])
            ->where('status', 'resolved')
            ->whereNotNull('completed_at')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(HOUR, created_at, completed_at)) as avg_time'))
            ->value('avg_time') ?? 0;

        $statusDistribution = [
            'open' => Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])->where('status', 'open')->count(),
            'on_progress' => Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])->where('status', 'on_progress')->count(),
            'resolved' => Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])->where('status', 'resolved')->count(),
            'rejected' => Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])->where('status', 'rejected')->count(),
        ];

        $dashboarData = [
            'total_tickets' => $totalTickets,
            'active_tickets' => $activeTickets,
            'resolved_tickets' => $resolvedTickets,
            'avg_resolution_time' => round($avgResolutionTime, 1),
            'status_distribution' => $statusDistribution
        ]; // data yg akan dikirim di view

        return response()->json([
            'message' => 'Dashboard Statistics fetched successfully',
            'data' => new DashboardResource($dashboarData)
        ], 200);
    }
}
