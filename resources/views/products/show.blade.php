@extends('layouts.app')

@section('title', $product->name)

@section('content')
    <a href="{{ route('store.index') }}" class="link-secondary">Back to products</a>
    <div class="row g-4 mt-1">
        <div class="col-md-5">
            <div class="border rounded bg-light d-flex align-items-center justify-content-center product-placeholder">
                <span class="text-secondary">{{ $product->category->name }}</span>
            </div>
        </div>
        <div class="col-md-7">
            <div class="text-secondary mb-1">{{ $product->category->name }}</div>
            <h1 class="h2">{{ $product->name }}</h1>
            <p>{{ $product->description ?: 'No description available.' }}</p>
            <p class="h4">₹{{ number_format((float) $product->price, 2) }}</p>
            @if ($product->stock > 0)
                <p class="text-success">In stock: {{ $product->stock }}</p>
                @auth
                    <form method="POST" action="{{ route('cart.add', $product) }}" class="row g-2 align-items-end">
                        @csrf
                        <div class="col-auto">
                            <label for="quantity" class="form-label">Quantity</label>
                            <input class="form-control" id="quantity" name="quantity" type="number" min="1" max="{{ $product->stock }}" value="1" required>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-primary" type="submit">Add to cart</button>
                        </div>
                    </form>
                @else
                    <a class="btn btn-primary" href="{{ route('login') }}">Log in to add to cart</a>
                @endauth
            @else
                <p class="text-danger">Out of stock</p>
            @endif
        </div>
    </div>
@endsection
