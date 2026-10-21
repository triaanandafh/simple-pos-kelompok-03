<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Exports\SalesReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'category' => ['nullable', 'integer', 'exists:categories,id'],
        ]);

        $categoryId = $validated['category'] ?? null;

        [$from, $to] = $this->resolveDateRange($request);

        $transactions = $this->filteredQuery($from, $to, $categoryId)
            ->get();

        return view('reports.index', [
            'totalPenjualan' => $categoryId === null
                ? $transactions->sum('total')
                : $transactions->sum(
                    fn (Transaction $transaction) =>
                        $transaction->details->sum('subtotal')
                ),
            'jumlahTransaksi' => $transactions->count(),
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'categories' => Category::orderBy('name')->get(),
            'selectedCategory' => $categoryId,
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'category' => ['nullable', 'integer', 'exists:categories,id'],
        ]);

        $categoryId = $validated['category'] ?? null;

        [$from, $to] = $this->resolveDateRange($request);

        $query = $this->filteredQuery($from, $to, $categoryId);

        return response()->streamDownload(function () use ($query, $categoryId) {
            $out = fopen('php://output', 'w');

            fputcsv($out, ['ID', 'Tanggal', 'Kasir', 'Total']);

            foreach ($query->lazyById() as $transaction) {
                $total = $categoryId === null
                    ? $transaction->total
                    : $transaction->details->sum('subtotal');

                fputcsv($out, [
                    $transaction->id,
                    $transaction->created_at,
                    $transaction->user?->name ?? '',
                    $total,
                ]);
            }

            fclose($out);
        }, 'laporan-penjualan.csv');
    }

    public function exportPdf(Request $request): Response
    {
        $validated = $request->validate([
            'category' => ['nullable', 'integer', 'exists:categories,id'],
        ]);
        $categoryId = $validated['category'] ?? null;
        [$from, $to] = $this->resolveDateRange($request);
        $query = $this->filteredQuery($from, $to, $categoryId);

        $pdf = Pdf::loadView('reports.pdf', [
            'totalPenjualan' => $categoryId === null
                ? $query->sum('total')
                : TransactionDetail::query()
                    ->whereHas('transaction', fn (Builder $transactionQuery) => $transactionQuery
                        ->whereBetween('created_at', [$from, $to]))
                    ->whereHas('product', fn (Builder $productQuery) => $productQuery
                        ->where('category_id', $categoryId))
                    ->sum('subtotal'),
            'jumlahTransaksi' => $query->count(),
            'transactions' => $query->latest()->limit(50)->get(),
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'categoryId' => $categoryId,
        ]);

        return $pdf->download('laporan-penjualan.pdf');
    }

    private function resolveDateRange(Request $request): array
    {
        $from = $request->date('from')?->startOfDay()
            ?? now()->startOfMonth();

        $to = $request->date('to')?->endOfDay()
            ?? now()->endOfDay();

        return [$from, $to];
    }

    private function filteredQuery(
        Carbon $from,
        Carbon $to,
        ?int $categoryId
    ): Builder {
        return Transaction::query()
            ->with('user')
            ->whereBetween('created_at', [$from, $to])
            ->when($categoryId !== null, function (Builder $query) use ($categoryId) {
                $query->whereHas(
                    'details.product',
                    fn ($productQuery) => $productQuery->where(
                        'category_id',
                        $categoryId
                    )
                )->with([
                    'details' => fn ($detailsQuery) => $detailsQuery->whereHas(
                        'product',
                        fn ($productQuery) => $productQuery->where(
                            'category_id',
                            $categoryId
                        )
                    ),
                ]);
            });
    }

        public function exportExcel(Request $request): BinaryFileResponse
    {
        $validated = $request->validate([
            'category' => ['nullable', 'integer', 'exists:categories,id'],
        ]);

        $categoryId = $validated['category'] ?? null;

        [$from, $to] = $this->resolveDateRange($request);

        return Excel::download(
            new SalesReportExport($this->filteredQuery($from, $to, $categoryId)),
            'laporan-penjualan.xlsx',
        );
    }
}
