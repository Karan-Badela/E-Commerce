@extends('layouts.app')

@section('title', 'My orders')

@section('content')
    <h1 class="h3 mb-4">My orders</h1>
    @if ($orders->isEmpty())
        <div class="alert alert-light border">You have not placed any orders yet.</div>
    @else
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Order</th><th>Date</th><th>Status</th><th>Total</th><th></th></tr></thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr>
                            <td>#{{ $order->id }}</td>
                            <td>{{ $order->created_at->format('M j, Y') }}</td>
                            <td>{{ ucfirst($order->status) }}</td>
                            <td>₹{{ number_format((float) $order->total_amount, 2) }}</td>
                            <td><a class="btn btn-outline-secondary btn-sm" href="{{ route('orders.show', $order) }}">View</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $orders->links() }}
    @endif
@endsection
