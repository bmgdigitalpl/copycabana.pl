@extends('layouts.auth')
@section('title', 'Ustaw nowe hasło | CopyCabana')
@section('content')
<div class="flex flex-col gap-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 dark:ring-zinc-700 sm:p-8">
    <div>
        <flux:heading size="xl">Ustaw nowe hasło</flux:heading>
        <flux:text class="mt-2">Wybierz nowe hasło do panelu administracyjnego.</flux:text>
    </div>

    <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-5">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <flux:input name="email" label="E-mail" type="email" :value="old('email', $request->email)" required autocomplete="email" />
        @error('email')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
        <flux:input name="password" label="Nowe hasło" type="password" required autocomplete="new-password" viewable />
        <flux:input name="password_confirmation" label="Powtórz hasło" type="password" required autocomplete="new-password" viewable />
        @error('password')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
        <flux:button variant="primary" type="submit" class="w-full">Zapisz nowe hasło</flux:button>
    </form>
</div>
@endsection
