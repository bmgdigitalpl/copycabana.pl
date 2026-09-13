@extends('layouts.auth')
@section('title', 'Logowanie | CopyCabana')
@section('content')
<div class="flex flex-col gap-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 dark:ring-zinc-700 sm:p-8">
    <div>
        <flux:heading size="xl">Zaloguj się do panelu</flux:heading>
        <flux:text class="mt-2">Użyj konta administratora lub pracownika.</flux:text>
    </div>

    @if(session('status'))
        <flux:callout variant="success">{{ session('status') }}</flux:callout>
    @endif

    <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-5">
        @csrf
        <flux:input name="email" label="E-mail" type="email" :value="old('email')" required autofocus autocomplete="email" />
        @error('email')
            <p class="-mt-3 text-sm text-red-600">{{ $message }}</p>
        @enderror

        <div>
            <flux:input name="password" label="Hasło" type="password" required autocomplete="current-password" viewable />
            @if(Route::has('password.request'))
                <flux:link class="mt-2 block text-sm" href="{{ route('password.request') }}">Nie pamiętasz hasła?</flux:link>
            @endif
        </div>
        @error('password')
            <p class="-mt-3 text-sm text-red-600">{{ $message }}</p>
        @enderror

        <flux:checkbox name="remember" label="Zapamiętaj mnie" :checked="old('remember')" />
        <flux:button variant="primary" type="submit" class="w-full">Zaloguj się</flux:button>
    </form>
</div>
@endsection
