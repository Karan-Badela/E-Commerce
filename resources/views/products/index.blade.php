@extends('layouts.app')

@section('title', 'Products')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h3 mb-1">Products</h1>
            <p class="text-secondary mb-0">Browse the items available in our shop.</p>
        </div>
    </div>

    <form method="GET" action="{{ route('store.index') }}" class="row g-2 mb-4">
        <div class="col-md-6">
            <label class="visually-hidden" for="search">Search products</label>
            <input class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Search products">
        </div>
        <div class="col-md-4">
            <label class="visually-hidden" for="category">Category</label>
            <select class="form-select" id="category" name="category">
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-grid">
            <button class="btn btn-primary" type="submit">Search</button>
        </div>
    </form>

    @forelse ($products as $product)
        @if ($loop->first)
            <div class="row g-3">
        @endif
        <div class="col-sm-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body d-flex flex-column">
                    <div class="small text-secondary mb-1">{{ $product->category->name }}</div>
                    <h2 class="h5">{{ $product->name }}</h2>
                    <p class="text-secondary flex-grow-1">{{ Str::limit($product->description, 120) }}</p>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <strong>₹{{ number_format((float) $product->price, 2) }}</strong>
                        @if ($product->stock > 0)
                            <span class="small text-success">In stock ({{ $product->stock }})</span>
                        @else
                            <span class="small text-danger">Out of stock</span>
                        @endif
                    </div>
                    <div class="d-flex gap-2">
                        <a class="btn btn-outline-secondary btn-sm" href="{{ route('store.show', $product) }}">View product</a>
                        @if ($product->stock > 0)
                            @auth
                                <form method="POST" action="{{ route('cart.add', $product) }}">
                                    @csrf
                                    <input type="hidden" name="quantity" value="1">
                                    <button class="btn btn-primary btn-sm" type="submit">Add to cart</button>
                                </form>
                            @else
                                <a class="btn btn-primary btn-sm" href="{{ route('login') }}">Log in to add</a>
                            @endauth
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @if ($loop->last)
            </div>
        @endif
    @empty
        <div class="alert alert-light border">No products found.</div>
    @endforelse

    <div class="mt-4">{{ $products->links() }}</div>
@endsection
