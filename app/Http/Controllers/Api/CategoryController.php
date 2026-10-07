<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        return response()->json(['data' => Category::orderBy('name')->get(['id', 'name'])]);
    }

    public function show(Category $category)
    {
        return response()->json(['data' => $category->only(['id', 'name'])]);
    }

    public function store(StoreCategoryRequest $request)
    {
        $category = Category::create($request->validated());

        return response()->json(['data' => $category->only(['id', 'name'])], 201);
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category->update($request->validated());

        return response()->json(['data' => $category->refresh()->only(['id', 'name'])]);
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return response()->json(['message' => 'Categories with products cannot be deleted.'], 409);
        }

        $category->delete();

        return response()->noContent();
    }
}
