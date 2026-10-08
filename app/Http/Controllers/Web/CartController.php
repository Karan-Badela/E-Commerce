<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function index()
    {
        $cart = auth()->user()->cart()->with('items.product')->first();

        return view('cart.index', ['cart' => $cart]);
    }

    public function add(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:10000']]);
        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);

        DB::transaction(function () use ($cart, $product, $data) {
            $locked = Product::whereKey($product->id)->lockForUpdate()->firstOrFail();
            $item = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->lockForUpdate()
                ->first();
            $quantity = ($item?->quantity ?? 0) + $data['quantity'];

            if ($quantity > $locked->stock) {
                throw ValidationException::withMessages(['quantity' => ['There is not enough stock for this quantity.']]);
            }

            if ($item) {
                $item->update(['quantity' => $quantity]);
            } else {
                CartItem::create(['cart_id' => $cart->id, 'product_id' => $product->id, 'quantity' => $quantity]);
            }
        });

        return redirect()->route('cart.index')->with('status', 'Product added to cart.');
    }

    public function update(Request $request, CartItem $cartItem): RedirectResponse
    {
        abort_unless($cartItem->cart()->where('user_id', $request->user()->id)->exists(), 404);
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:10000']]);
        $product = Product::findOrFail($cartItem->product_id);

        if ($data['quantity'] > $product->stock) {
            throw ValidationException::withMessages(['quantity' => ['There is not enough stock for this quantity.']]);
        }

        $cartItem->update($data);

        return redirect()->route('cart.index')->with('status', 'Cart updated.');
    }

    public function remove(Request $request, CartItem $cartItem): RedirectResponse
    {
        abort_unless($cartItem->cart()->where('user_id', $request->user()->id)->exists(), 404);
        $cartItem->delete();

        return redirect()->route('cart.index')->with('status', 'Product removed from cart.');
    }
}
