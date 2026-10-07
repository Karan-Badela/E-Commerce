<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddCartItemRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    private function cart(): Cart
    {
        return Cart::firstOrCreate(['user_id' => request()->user()->id]);
    }

    public function show()
    {
        $cart = $this->cart()->load('items.product.category');

        return response()->json(['data' => $cart]);
    }

    public function add(AddCartItemRequest $request)
    {
        $data = $request->validated();
        $cart = $this->cart();
        $product = Product::findOrFail($data['product_id']);
        $item = DB::transaction(function () use ($data, $cart, $product) {
            $locked = Product::whereKey($product->id)->lockForUpdate()->firstOrFail();
            $existing = CartItem::where('cart_id', $cart->id)->where('product_id', $product->id)->lockForUpdate()->first();
            $quantity = ($existing?->quantity ?? 0) + $data['quantity'];
            if ($quantity > $locked->stock) throw ValidationException::withMessages(['quantity' => ['There is not enough stock for this quantity.']]);
            if ($existing) { $existing->update(['quantity' => $quantity]); return $existing->refresh(); }
            return CartItem::create(['cart_id' => $cart->id, 'product_id' => $product->id, 'quantity' => $quantity]);
        });
        return response()->json(['data' => $cart->load('items.product.category')], 201);
    }

    public function update(UpdateCartItemRequest $request, CartItem $cartItem)
    {
        abort_unless($cartItem->cart()->where('user_id', request()->user()->id)->exists(), 404);
        $product = Product::findOrFail($cartItem->product_id);
        if ($request->validated('quantity') > $product->stock) throw ValidationException::withMessages(['quantity' => ['There is not enough stock for this quantity.']]);
        $cartItem->update($request->validated());
        return response()->json(['data' => $this->cart()->load('items.product.category')]);
    }

    public function destroy(CartItem $cartItem)
    {
        abort_unless($cartItem->cart()->where('user_id', request()->user()->id)->exists(), 404);
        $cartItem->delete();
        return response()->noContent();
    }
}
