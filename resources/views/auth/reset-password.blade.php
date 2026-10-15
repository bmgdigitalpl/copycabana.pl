@extends('layouts.auth')
@section('title', 'Ustaw nowe hasło | CopyCabana')
@section('content')
<div class="auth-card">
    <h1>Ustaw nowe hasło</h1>
    <p class="lead">Wybierz hasło do panelu administracyjnego.</p>

    <form method="POST" action="{{ route('password.update') }}" class="auth-form">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email }}">
        @error('email')
            <p class="auth-error">{{ $message }}</p>
        @enderror
        <label>Nowe hasło
            <input name="password" type="password" required autofocus autocomplete="new-password">
        </label>
        <label>Powtórz hasło
            <input name="password_confirmation" type="password" required autocomplete="new-password">
        </label>
        @error('password')
            <p class="auth-error">{{ $message }}</p>
        @enderror
        <button type="submit" class="auth-button">Zapisz nowe hasło</button>
    </form>
</div>
@endsection
