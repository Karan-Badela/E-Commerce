@extends('layouts.app')

@section('title', 'Create account')

@section('content')
    <div class="auth-card card mx-auto">
        <div class="card-body p-4">
            <h1 class="h3 mb-3">Create an account</h1>
            <form method="POST" action="{{ route('register.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="name">Name</label>
                    <input class="form-control" id="name" name="name" type="text" value="{{ old('name') }}" required autocomplete="name">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="email">Email</label>
                    <input class="form-control" id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">Password</label>
                    <input class="form-control" id="password" name="password" type="password" required autocomplete="new-password">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password_confirmation">Confirm password</label>
                    <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
                </div>
                <button class="btn btn-primary w-100" type="submit">Register</button>
            </form>
            <p class="text-secondary mt-3 mb-0">Already have an account? <a href="{{ route('login') }}">Log in</a>.</p>
        </div>
    </div>
@endsection
