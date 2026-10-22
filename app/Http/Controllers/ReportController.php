<?php

namespace App\Http\Controllers;

use App\Exports\SalesReportExport;
use App\Jobs\ImportProductsJob;
use App\Models\Category;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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
                : $query->get()->sum(
                    fn (Transaction $transaction) =>
                        $transaction->details->sum('subtotal')
                ),
            'jumlahTransaksi' => $query->count(),
            'transactions' => $query->latest()->limit(50)->get(),
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ]);

        return $pdf->download('laporan-penjualan.pdf');
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        $validated = $request->validate([
            'category' => ['nullable', 'integer', 'exists:categories,id'],
        ]);

        $categoryId = $validated['category'] ?? null;

        [$from, $to] = $this->resolveDateRange($request);

        return Excel::download(
            new SalesReportExport(
                $this->filteredQuery($from, $to, $categoryId)
            ),
            'laporan-penjualan.xlsx',
        );
    }

    public function importForm(): View
    {
        return view('reports.import');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'csv' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $file = $request->file('csv');

        $lines = file(
            $file->getRealPath(),
            FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
        );

        if ($lines === false || count($lines) < 2) {
            return back()->with(
                'error',
                'File CSV kosong atau tidak dapat dibaca.'
            );
        }

        $header = str_getcsv(array_shift($lines));

        // Menghapus BOM jika file CSV disimpan dari Excel.
        if (isset($header[0])) {
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        }

        $header = array_map('trim', $header);

        $requiredColumns = ['name', 'category_id', 'price'];

        if (
            count($header) !== count(array_unique($header))
            || array_diff($requiredColumns, $header) !== []
        ) {
            return back()->with(
                'error',
                'Header CSV harus memuat kolom name, category_id, dan price.'
            );
        }

        $path = $file->store('imports', 'local');

        if ($path === false) {
            return back()->with(
                'error',
                'File CSV gagal disimpan.'
            );
        }

        ImportProductsJob::dispatch($path);

        return back()->with(
            'success',
            'Import sedang diproses di background.'
        );
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
}
