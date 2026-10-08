@extends('layouts.app')

@section('title', 'Order details')

@section('content')
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div><h1 class="h3">Order #{{ $order->id }}</h1><p class="text-secondary">{{ $order->created_at->format('M j, Y') }}</p></div>
        <span class="badge text-bg-secondary">{{ ucfirst($order->status) }}</span>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead><tr><th>Product</th><th>Price</th><th>Quantity</th><th>Subtotal</th></tr></thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td>{{ $item->product_name }}</td>
                        <td>₹{{ number_format((float) $item->price, 2) }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>₹{{ number_format((float) $item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot><tr><th colspan="3" class="text-end">Total</th><th>₹{{ number_format((float) $order->total_amount, 2) }}</th></tr></tfoot>
        </table>
    </div>
    <a class="btn btn-outline-secondary" href="{{ route('orders.index') }}">Back to orders</a>
@endsection
