<?php

namespace App\Services;

use App\Jobs\SendOrderConfirmation;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function place(User $user): Order
    {
        $order = DB::transaction(function () use ($user) {
            $cart = Cart::where('user_id', $user->id)->first();

            if (!$cart) {
                throw ValidationException::withMessages(['cart' => ['Your cart is empty.']]);
            }

            $items = $cart->items()->with('product')->get();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages(['cart' => ['Your cart is empty.']]);
            }

            $products = Product::whereIn('id', $items->pluck('product_id')->sort()->values())
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            $totalCents = 0;

            foreach ($items as $item) {
                $product = $products->get($item->product_id);

                if (!$product || $product->stock < $item->quantity) {
                    throw ValidationException::withMessages(['stock' => ["There is not enough stock for {$item->product?->name}."]]);
                }

                $totalCents += $this->cents($product->price) * $item->quantity;
            }

            $order = Order::create([
                'user_id' => $user->id,
                'total_amount' => number_format($totalCents / 100, 2, '.', ''),
                'status' => 'confirmed',
            ]);

            foreach ($items as $item) {
                $product = $products->get($item->product_id);
                $subtotalCents = $this->cents($product->price) * $item->quantity;

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $item->quantity,
                    'price' => $product->price,
                    'subtotal' => number_format($subtotalCents / 100, 2, '.', ''),
                ]);

                $product->decrement('stock', $item->quantity);
            }

            $cart->items()->delete();

            return $order;
        });

        SendOrderConfirmation::dispatch($order->id)->afterCommit();

        return $order->load('items.product');
    }

    private function cents(string $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '00');

        return ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');
    }
}
