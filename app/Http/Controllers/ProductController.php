<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Models\Category;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')
            ->orderBy('name')
            ->paginate(10);

        return view('products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('products.create', compact('categories'));
    }

    public function store(StoreProductRequest $request)
    {
        Product::create($request->validated());

        return redirect()
            ->route('products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

   public function edit(Product $product)
    {
<<<<<<< HEAD
        $product = Product::findOrFail($id);
        $categories = Category::orderBy('name')->get();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(StoreProductRequest $request, string $id)
    {
        $product = Product::findOrFail($id);
        $product->update($request->validated());
        
        return redirect()
            ->route('products.index')
            ->with('success', 'Produk berhasil diperbarui.');
=======
        $categories = Category::all();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(StoreProductRequest $request, Product $product)
    {
        $product->update($request->validated());

        return redirect()->route('products.index')
                        ->with('success', 'Produk berhasil diperbarui.');
>>>>>>> 705e4cdfceea8c5f4de1ba200b9a3bb6dae48d81
    }
}