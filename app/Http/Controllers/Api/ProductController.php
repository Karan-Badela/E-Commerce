<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min(max((int) $request->query('per_page', 10), 1), 100);
        $products = Product::with('category')->when($request->filled('search'), function ($query) use ($request) {
            $term = $request->string('search')->toString();
            $query->where(fn ($query) => $query->where('name', 'like', "%{$term}%")->orWhere('description', 'like', "%{$term}%"));
        })->orderBy('id')->paginate($perPage)->withQueryString();
        return response()->json(['data' => $products->items(), 'meta' => ['current_page' => $products->currentPage(), 'last_page' => $products->lastPage(), 'per_page' => $products->perPage(), 'total' => $products->total()]]);
    }

    public function show(Product $product)
    {
        return response()->json(['data' => $product->load('category')]);
    }

    public function store(StoreProductRequest $request)
    {
        $product = Product::create($request->validated())->load('category');

        return response()->json(['data' => $product], 201);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->update($request->validated());

        return response()->json(['data' => $product->refresh()->load('category')]);
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->noContent();
    }
}
