@extends('layouts.auth')
@section('title', 'Reset hasła | CopyCabana')
@section('content')
<div class="flex flex-col gap-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 dark:ring-zinc-700 sm:p-8">
    <div>
        <flux:heading size="xl">Reset hasła</flux:heading>
        <flux:text class="mt-2">Podaj e-mail konta, a wyślemy link do ustawienia nowego hasła.</flux:text>
    </div>

    @if(session('status'))
        <flux:callout variant="success">{{ session('status') }}</flux:callout>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-5">
        @csrf
        <flux:input name="email" label="E-mail" type="email" :value="old('email')" required autofocus autocomplete="email" />
        @error('email')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
        <flux:button variant="primary" type="submit" class="w-full">Wyślij link</flux:button>
    </form>

    <flux:link href="{{ route('login') }}">Wróć do logowania</flux:link>
</div>
@endsection
