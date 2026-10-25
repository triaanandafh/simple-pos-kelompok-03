<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\ImportError;
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

    public function __construct(public string $csvPath) {}

    public function handle(): void
{
    $fullPath = Storage::path($this->csvPath);
    
    if (!file_exists($fullPath)) {
        return;
    }

    $lines = file($fullPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    if ($lines === false || count($lines) < 2) {
        Storage::delete($this->csvPath);
        return;
    }

    // Bersihkan log error dari import sebelumnya
    ImportError::truncate();

    $rows = array_map('str_getcsv', $lines);
    $header = array_shift($rows);

    if (isset($header[0])) {
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
    }
    $header = array_map('trim', $header);

    // Gunakan foreach dengan key ($index) untuk mendeteksi nomor baris
    foreach ($rows as $index => $row) {
        // Baris CSV dimulai dari row ke-2 di file (karena row 1 header) -> index 0 adalah baris ke-2
        $rowNumber = $index + 2;

        if (count($row) !== count($header)) {
            ImportError::create([
                'row_number' => $rowNumber,
                'error_message' => 'Jumlah kolom pada baris tidak sesuai dengan header.',
            ]);
            continue;
        }

        $validator = Validator::make(array_combine($header, $row), [
            'name' => ['required', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'price' => ['required', 'integer', 'min:0'],
        ]);

        if ($validator->fails()) {
            // Gabungkan pesan error dari validator
            $errorMessage = implode(', ', $validator->errors()->all());

            ImportError::create([
                'row_number' => $rowNumber,
                'error_message' => $errorMessage,
            ]);
            continue;
        }

        $data = $validator->validated();

        Product::updateOrCreate(
            ['name' => $data['name'], 'category_id' => $data['category_id']],
            ['price' => $data['price']]
        );
    }

    Storage::delete($this->csvPath);
}

}
