@extends('layouts.app')

@section('title', 'Log in')

@section('content')
    <div class="auth-card card mx-auto">
        <div class="card-body p-4">
            <h1 class="h3 mb-3">Log in</h1>
            <form method="POST" action="{{ route('login.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="email">Email</label>
                    <input class="form-control" id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">Password</label>
                    <input class="form-control" id="password" name="password" type="password" required autocomplete="current-password">
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" id="remember" name="remember" type="checkbox" value="1">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
                <button class="btn btn-primary w-100" type="submit">Log in</button>
            </form>
            <p class="text-secondary mt-3 mb-0">New to the shop? <a href="{{ route('register') }}">Create an account</a>.</p>
        </div>
    </div>
@endsection
