@extends('layouts.guest')

@section('content')
<form method="POST" action="{{ route('login') }}" novalidate>
    @csrf
    <div class="mb-3">
        <label class="form-label" for="email">Email</label>
        <input id="email" class="form-control" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
    </div>
    <div class="mb-3">
        <label class="form-label" for="password">Password</label>
        <input id="password" class="form-control" type="password" name="password" required autocomplete="current-password">
    </div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="remember" id="remember">
            <label class="form-check-label" for="remember">Remember me</label>
        </div>
        <a href="{{ route('register') }}" class="small">Need an account?</a>
    </div>
    <div class="d-grid gap-2">
        <button type="submit" class="btn btn-primary">Log in</button>
        <a class="btn btn-outline-secondary" href="{{ url('/') }}">Back to app</a>
    </div>
</form>
@endsection
