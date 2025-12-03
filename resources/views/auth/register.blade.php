@extends('layouts.guest')

@section('content')
<form method="POST" action="{{ route('register') }}" novalidate>
    @csrf
    <div class="mb-3">
        <label class="form-label" for="name">Name</label>
        <input id="name" class="form-control" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
    </div>
    <div class="mb-3">
        <label class="form-label" for="email">Email</label>
        <input id="email" class="form-control" type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
    </div>
    <div class="mb-3">
        <label class="form-label" for="password">Password</label>
        <input id="password" class="form-control" type="password" name="password" required autocomplete="new-password">
    </div>
    <div class="mb-4">
        <label class="form-label" for="password_confirmation">Confirm password</label>
        <input id="password_confirmation" class="form-control" type="password" name="password_confirmation" required autocomplete="new-password">
    </div>
    <div class="d-grid gap-2">
        <button type="submit" class="btn btn-primary">Create account</button>
        <a class="btn btn-outline-secondary" href="{{ route('login') }}">Back to login</a>
    </div>
</form>
@endsection
