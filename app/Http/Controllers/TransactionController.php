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
        $products = Product::where('stock', '>', 0)->get();
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