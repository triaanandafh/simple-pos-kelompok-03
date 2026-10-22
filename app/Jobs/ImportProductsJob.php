<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ImportProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 60];

    public function __construct(public string $path)
    {
    }

    public function handle(): void
    {
        $fullPath = storage_path('app/private/' . $this->path);

        if (! is_file($fullPath)) {
            throw new RuntimeException('File CSV import tidak ditemukan.');
        }

        $lines = file(
            $fullPath,
            FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
        );

        if ($lines === false || count($lines) < 2) {
            throw new RuntimeException('File CSV kosong atau tidak dapat dibaca.');
        }

        $header = str_getcsv(array_shift($lines));

        if (isset($header[0])) {
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        }

        $header = array_map('trim', $header);

        $requiredColumns = ['name', 'category_id', 'price'];

        if (
            count($header) !== count(array_unique($header))
            || array_diff($requiredColumns, $header) !== []
        ) {
            throw new RuntimeException('Header CSV tidak valid.');
        }

        foreach ($lines as $line) {
            $row = str_getcsv($line);

            if (count($row) !== count($header)) {
                continue;
            }

            $rowData = array_combine($header, $row);

            if ($rowData === false) {
                continue;
            }

            $validator = Validator::make($rowData, [
                'name' => ['required', 'string'],
                'category_id' => [
                    'required',
                    'integer',
                    'exists:categories,id',
                ],
                'price' => ['required', 'integer', 'min:0'],
            ]);

            if ($validator->fails()) {
                continue;
            }

            $data = $validator->validated();

            Product::updateOrCreate(
                [
                    'name' => $data['name'],
                    'category_id' => $data['category_id'],
                ],
                [
                    'price' => $data['price'],
                ]
            );
        }

        Storage::disk('local')->delete($this->path);
    }
}
