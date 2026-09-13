@extends('layouts.main')
@section('title', 'Potwierdź e-mail | CopyCabana')
@section('content')
<main class="cc-container" style="padding:9rem 1.5rem 6rem">
  <div class="cc-local-card mx-auto max-w-xl text-center">
    <h1 class="text-3xl font-bold">Potwierdź adres e-mail</h1>
    <p class="mt-3 text-slate-600">Wysłaliśmy link weryfikacyjny na adres {{ auth()->user()->email }}.</p>
    @if(session('status'))<p class="mt-4 rounded-lg bg-green-50 p-3 text-green-800">{{ session('status') }}</p>@endif
    <form method="POST" action="{{ route('verification.send') }}" class="mt-6">@csrf<button class="btn-magenta" type="submit">Wyślij link ponownie</button></form>
    <form method="POST" action="{{ route('customer.logout') }}" class="mt-4">@csrf<button class="text-sm text-slate-600 underline" type="submit">Wyloguj się</button></form>
  </div>
</main>
@endsection
