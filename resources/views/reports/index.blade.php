
@extends('layouts.app')

@section('title', 'Laporan Penjualan')

@section('content')
    <div class="p-4">
        <h1 class="text-xl font-semibold mb-4">Laporan Penjualan</h1>

        <form method="GET" action="{{ route('reports.index') }}"
            class="flex flex-wrap items-end gap-4 mb-6">

            <div>
                <label for="from" class="block text-sm font-medium mb-1">
                    Dari
                </label>
                <input type="date" id="from" name="from" value="{{ $from }}"
                    class="border rounded px-3 py-2">
            </div>

            <div>
                <label for="to" class="block text-sm font-medium mb-1">
                    Sampai
                </label>
                <input type="date" id="to" name="to" value="{{ $to }}"
                    class="border rounded px-3 py-2">
            </div>

            <div>
                <label for="category" class="block text-sm font-medium mb-1">
                    Kategori
                </label>
                <select id="category" name="category"
                    class="border rounded px-3 py-2">

                    <option value="">Semua kategori</option>

                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}"
                            @selected((string) $selectedCategory === (string) $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach

                </select>
            </div>

            <button type="submit"
                class="bg-slate-900 text-white rounded px-4 py-2 hover:bg-slate-700">
                Filter
            </button>

            <a href="{{ route('reports.export.csv', [
                'from' => $from,
                'to' => $to,
                'category' => $selectedCategory,
            ]) }}"
                class="bg-green-700 text-white rounded px-4 py-2 hover:bg-green-800">
                Export CSV
            </a>
            <a href="{{ route('reports.export.pdf', ['from' => $from, 'to' => $to, 'category' => $selectedCategory]) }}" 
                class="bg-red-600 text-white px-4 py-2 rounded">
                Export PDF
            </a>
        </form>

        <section class="border rounded-md p-4 max-w-md">
            <h2 class="text-sm text-slate-500">Total Penjualan</h2>

            <p class="text-2xl font-semibold">
                Rp {{ number_format($totalPenjualan) }}
            </p>

            <p class="text-sm text-slate-500 mt-1">
                {{ $jumlahTransaksi }} transaksi
            </p>
        </section>
    </div>
@endsection
