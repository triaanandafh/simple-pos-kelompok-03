namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ProductsImport implements ToModel, WithHeadingRow, WithChunkReading
{
    public function model(array $row)
    {
        return Product::updateOrCreate(
            ['name' => $row['name']],
            [
                'category_id' => $row['category_id'],
                'price'       => $row['price'],
            ]
        );
    }

    public function chunkSize(): int
    {
        return 100;
    }
}