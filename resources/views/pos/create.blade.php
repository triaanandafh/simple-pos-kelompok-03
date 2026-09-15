@extends('layouts.app')

@section('title', 'Kasir')

@section('content')
<div class="max-w-7xl mx-auto">
    <h1 class="text-2xl font-bold mb-6 text-slate-800">Transaksi Kasir</h1>

    <!-- Grid Daftar Produk -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach ($products as $product)
        <div class="border rounded-xl p-4 bg-white shadow-sm hover:shadow-md transition flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-start mb-2">
                    <p class="font-semibold text-slate-800">{{ $product->name }}</p>
                    @if ($product->stock < 10)
                        <span class="bg-amber-100 text-amber-700 text-xs px-2 py-0.5 rounded-full font-medium">Stok Menipis</span>
                    @endif
                </div>
                <p class="text-xs text-slate-400">Stok: {{ $product->stock }}</p>
            </div>
            <p class="text-blue-600 font-bold mt-4">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
        </div>
        @endforeach
    </div>
</div>
@endsection