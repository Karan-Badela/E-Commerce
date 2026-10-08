<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OrderService;

class OrderController extends Controller
{
    public function index()
    {
        $orders = request()->user()->orders()->with('items.product')->latest()->paginate(min(max((int) request()->query('per_page', 10), 1), 100));
        return response()->json(['data' => $orders->items(), 'meta' => ['current_page' => $orders->currentPage(), 'last_page' => $orders->lastPage(), 'per_page' => $orders->perPage(), 'total' => $orders->total()]]);
    }

    public function show(int $order)
    {
        $order = request()->user()->orders()->with('items.product')->findOrFail($order);

        return response()->json(['data' => $order]);
    }

    public function store(OrderService $orders)
    {
        $order = $orders->place(request()->user());

        return response()->json(['data' => $order], 201);
    }
}
