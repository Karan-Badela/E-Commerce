<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function checkout()
    {
        $cart = request()->user()->cart()->with('items.product')->first();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('status', 'Your cart is empty.');
        }

        return view('checkout.index', ['cart' => $cart]);
    }

    public function place(Request $request, OrderService $orders): RedirectResponse
    {
        $order = $orders->place($request->user());

        return redirect()->route('orders.confirmation', $order);
    }

    public function index(Request $request)
    {
        $orders = $request->user()->orders()->with('items')->latest()->paginate(10);

        return view('orders.index', ['orders' => $orders]);
    }

    public function show(Request $request, Order $order)
    {
        $order = $request->user()->orders()->with('items.product')->findOrFail($order->id);

        return view('orders.show', ['order' => $order]);
    }

    public function confirmation(Request $request, Order $order)
    {
        $order = $request->user()->orders()->with('items.product')->findOrFail($order->id);

        return view('orders.confirmation', ['order' => $order]);
    }
}
