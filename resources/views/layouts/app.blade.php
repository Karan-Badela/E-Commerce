<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'Simple Shop'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
        <div class="container">
            <a class="navbar-brand" href="{{ route('store.index') }}">{{ config('app.name', 'Simple Shop') }}</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <div class="navbar-nav me-auto">
                    <a class="nav-link" href="{{ route('store.index') }}">Products</a>
                    @auth
                        <a class="nav-link" href="{{ route('cart.index') }}">Cart</a>
                        <a class="nav-link" href="{{ route('orders.index') }}">My orders</a>
                    @endauth
                </div>
                <div class="d-flex gap-2 align-items-center">
                    @auth
                        <span class="text-secondary small">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="btn btn-outline-secondary btn-sm" type="submit">Log out</button>
                        </form>
                    @else
                        <a class="btn btn-outline-secondary btn-sm" href="{{ route('login') }}">Log in</a>
                        <a class="btn btn-primary btn-sm" href="{{ route('register') }}">Register</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="container py-4">
        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @yield('content')
    </main>

    <footer class="border-top bg-white py-3 mt-5">
        <div class="container text-secondary small">{{ config('app.name', 'Simple Shop') }} · {{ date('Y') }}</div>
    </footer>
</body>
</html>
