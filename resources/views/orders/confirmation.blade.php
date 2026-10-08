@extends('layouts.app')

@section('title', 'Order confirmed')

@section('content')
    <div class="alert alert-success">
        <h1 class="h4">Thank you. Your order has been placed.</h1>
        <p class="mb-0">Order #{{ $order->id }} is {{ $order->status }}.</p>
    </div>
    <p>Total: <strong>₹{{ number_format((float) $order->total_amount, 2) }}</strong></p>
    <a class="btn btn-primary" href="{{ route('orders.show', $order) }}">View order</a>
    <a class="btn btn-outline-secondary" href="{{ route('store.index') }}">Continue shopping</a>
@endsection
