@extends('layouts.auth')
@section('title', 'Reset hasła | CopyCabana')
@section('content')
<div class="auth-card">
    <h1>Reset hasła</h1>
    <p class="lead">Podaj e-mail konta, a wyślemy link do ustawienia nowego hasła.</p>

    @if(session('status'))
        <div class="auth-status">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="auth-form">
        @csrf
        <label>E-mail
            <input name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email">
        </label>
        @error('email')
            <p class="auth-error">{{ $message }}</p>
        @enderror
        <button type="submit" class="auth-button">Wyślij link</button>
    </form>

    <a href="{{ route('login') }}" class="auth-link">Wróć do logowania</a>
</div>
@endsection
