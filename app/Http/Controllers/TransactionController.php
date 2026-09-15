<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Models\Product;
use App\Models\ShopSetting;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class TransactionController extends Controller
{
    public function create()
    {
        $products = collect([
            (object) ['id' => 1, 'name' => 'Kopi Sachet', 'price' => 3000, 'stock' => 40],
            (object) ['id' => 2, 'name' => 'Teh Celup', 'price' => 2500, 'stock' => 25],
            (object) ['id' => 3, 'name' => 'Mie Instan', 'price' => 3500, 'stock' => 8],
            (object) ['id' => 4, 'name' => 'Air Mineral 600ml', 'price' => 4000, 'stock' => 60],
            (object) ['id' => 5, 'name' => 'Roti Tawar', 'price' => 12000, 'stock' => 15],
            (object) ['id' => 6, 'name' => 'Gula Pasir 1kg', 'price' => 15000, 'stock' => 5],
        ]);

        return view('pos.create', ['products' => $products]);
    }

    public function store()
    {
        return 'Transaksi disimpan (belum ada logika penyimpanan)';
    }

   public function index()
{
    return view('transactions.index');
}

    public function show(string $id)
    {
        return "Detail transaksi #{$id}";
    }
}