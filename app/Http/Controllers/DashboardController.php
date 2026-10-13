<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = Auth::user();

        $todayTotal = Transaction::whereDate('created_at', today())
            ->whereDate('created_at', today())
            ->when($user->role !== 'admin', fn ($q) => $q->where('user_id', $user->id))
            ->sum('total');

        $todayCount = Transaction::whereDate('created_at', today())
            ->whereDate('created_at', today())
            ->when($user->role !== 'admin', fn ($q) => $q->where('user_id', $user->id))
            ->count();

        $lowStock = Product::where('stock', '<', 10)->orderBy('stock')->limit(5)->get();

        $rangeStart = today()->subDays(6);
        $dailyTotals = Transaction::whereBetween('created_at', [$rangeStart->copy()->startOfDay(), today()->endOfDay()])
            ->whereBetween('created_at', [$rangeStart->copy()->startOfDay(), today()->endOfDay()])
            ->when($user->role !== 'admin', fn ($q) => $q->where('user_id', $user->id))
            ->selectRaw('date(created_at) as day, sum(total) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $weekSeries = collect(range(0, 6))->map(function (int $offset) use ($rangeStart, $dailyTotals) {
            $date = $rangeStart->copy()->addDays($offset);
            $key = $date->format('Y-m-d');

            return ['label' => $date->translatedFormat('D'), 'total' => (int) ($dailyTotals[$key] ?? 0)];
        });

        return view('dashboard', compact('todayTotal', 'todayCount', 'lowStock', 'weekSeries'));
    }
}
