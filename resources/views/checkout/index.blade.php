@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
    <h1 class="h3 mb-4">Checkout</h1>
    <div class="row g-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Order items</div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead><tr><th>Product</th><th>Price</th><th>Quantity</th><th>Subtotal</th></tr></thead>
                        <tbody>
                            @foreach ($cart->items as $item)
                                <tr>
                                    <td>{{ $item->product->name }}</td>
                                    <td>₹{{ number_format((float) $item->product->price, 2) }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>₹{{ number_format((float) $item->product->price * $item->quantity, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-body text-end">
                    <strong>Total: ₹{{ number_format($cart->items->sum(fn ($item) => (float) $item->product->price * $item->quantity), 2) }}</strong>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h2 class="h5">Order information</h2>
                    <p class="text-secondary">Your order will be placed for {{ auth()->user()->name }} ({{ auth()->user()->email }}).</p>
                    <form method="POST" action="{{ route('checkout.place') }}">
                        @csrf
                        <button class="btn btn-primary w-100" type="submit">Place order</button>
                    </form>
                    <a class="btn btn-link w-100 mt-2" href="{{ route('cart.index') }}">Back to cart</a>
                </div>
            </div>
        </div>
    </div>
@endsection
