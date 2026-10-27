
@extends('layouts.app')

@section('title', 'Import Produk')

@section('content')
    <div class="p-4">
        <h1 class="text-lg font-semibold mb-4">
            Import Produk dari CSV
        </h1>

        @if (session('success'))
            <div class="bg-green-50 text-green-700 p-3 rounded-md mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 text-red-700 p-3 rounded-md mb-4">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST"
            action="{{ route('import.store') }}"
            enctype="multipart/form-data"
            class="max-w-md">

            @csrf

            <label class="block mb-3">
                <span class="text-sm font-medium">File CSV</span>

                <input
                    type="file"
                    name="csv"
                    accept=".csv,.txt"
                    required
                    class="mt-1 block w-full text-sm border rounded p-2"
                >

                @error('csv')
                    <p class="text-sm text-red-600 mt-1">
                        {{ $message }}
                    </p>
                @enderror
            </label>

            <p class="text-xs text-slate-500 mb-3">
                Kolom yang dibutuhkan:
                <code>name</code>,
                <code>category_id</code>,
                <code>price</code>.
            </p>

            <button
                type="submit"
                class="bg-slate-900 text-white px-4 py-2 rounded-md text-sm hover:bg-slate-700"
            >
                Import
            </button>
        </form>

        @if(isset($importErrors) && $importErrors->isNotEmpty())
            <div class="mt-6 max-w-2xl border-t pt-4">
                <h2 class="text-md font-semibold text-red-600 mb-3">Log Baris Gagal Di-import</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border border-gray-200 bg-white">
                        <thead class="bg-red-50 text-red-700">
                            <tr>
                                <th class="border px-3 py-2 text-center w-20">Baris ke-</th>
                                <th class="border px-3 py-2 text-left">Pesan Error</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($importErrors as $error)
                                <tr class="border-b">
                                    <td class="border px-3 py-2 font-medium text-center">{{ $error->row_number }}</td>
                                    <td class="border px-3 py-2 text-red-600">{{ $error->error_message }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
