@extends('layouts.app')

@section('title', 'Your cart')

@section('content')
    <h1 class="h3 mb-4">Your cart</h1>
    @if (!$cart || $cart->items->isEmpty())
        <div class="alert alert-light border">Your cart is empty. <a href="{{ route('store.index') }}">Browse products</a>.</div>
    @else
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr><th>Product</th><th>Price</th><th>Quantity</th><th>Subtotal</th><th></th></tr>
                </thead>
                <tbody>
                    @foreach ($cart->items as $item)
                        <tr>
                            <td><a href="{{ route('store.show', $item->product) }}">{{ $item->product->name }}</a></td>
                            <td>₹{{ number_format((float) $item->product->price, 2) }}</td>
                            <td>
                                <form method="POST" action="{{ route('cart.update', $item) }}" class="d-flex gap-2">
                                    @csrf
                                    @method('PUT')
                                    <input class="form-control form-control-sm quantity-input" type="number" name="quantity" min="1" max="{{ $item->product->stock }}" value="{{ $item->quantity }}" required>
                                    <button class="btn btn-outline-secondary btn-sm" type="submit">Update</button>
                                </form>
                            </td>
                            <td>₹{{ number_format((float) $item->product->price * $item->quantity, 2) }}</td>
                            <td>
                                <form method="POST" action="{{ route('cart.remove', $item) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-link text-danger btn-sm" type="submit">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr><th colspan="3" class="text-end">Total</th><th>₹{{ number_format($cart->items->sum(fn ($item) => (float) $item->product->price * $item->quantity), 2) }}</th><th></th></tr>
                </tfoot>
            </table>
        </div>
        <div class="d-flex justify-content-end">
            <a class="btn btn-primary" href="{{ route('checkout.index') }}">Continue to checkout</a>
        </div>
    @endif
@endsection
