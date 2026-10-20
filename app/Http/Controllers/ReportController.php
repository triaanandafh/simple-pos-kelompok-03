<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'category' => ['nullable', 'integer', 'exists:categories,id'],
        ]);
        $categoryId = $validated['category'] ?? null;
        [$from, $to] = $this->resolveDateRange($request);
        $transactions = $this->filteredTransactions($from, $to, $categoryId);

        return view('reports.index', [
            'totalPenjualan' => $categoryId === null
                ? $transactions->sum('total')
                : $transactions->sum(fn (Transaction $transaction) => $transaction->details->sum('subtotal')),
            'jumlahTransaksi' => $transactions->count(),
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'categories' => Category::orderBy('name')->get(),
            'selectedCategory' => $categoryId,
        ]);
    }

    private function resolveDateRange(Request $request): array
    {
        $from = $request->date('from')?->startOfDay() ?? now()->startOfMonth();
        $to = $request->date('to')?->endOfDay() ?? now()->endOfDay();

        return [$from, $to];
    }

    private function filteredTransactions(Carbon $from, Carbon $to, ?int $categoryId): Collection
    {
        return Transaction::query()
            ->whereBetween('created_at', [$from, $to])
            ->when($categoryId !== null, function ($query) use ($categoryId) {
                $query->whereHas('details.product', fn ($productQuery) => $productQuery->where('category_id', $categoryId))
                    ->with(['details' => fn ($detailsQuery) => $detailsQuery->whereHas(
                        'product',
                        fn ($productQuery) => $productQuery->where('category_id', $categoryId)
                    )]);
            })
            ->get();
    }
}
