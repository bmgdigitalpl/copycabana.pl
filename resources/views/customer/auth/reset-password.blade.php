@extends('layouts.main')
@section('title', 'Ustaw nowe hasło | CopyCabana')
@section('content')
<main class="cc-container" style="padding:9rem 1.5rem 6rem">
  <div class="cc-local-card mx-auto max-w-xl">
    <h1 class="text-3xl font-bold">Ustaw nowe hasło</h1>
    @if($errors->any())<div class="mt-4 rounded-lg bg-red-50 p-3 text-red-800">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('customer.password.update') }}" class="mt-6 space-y-4">
      @csrf
      <input type="hidden" name="token" value="{{ $token }}">
      <label class="block">E-mail<input class="mt-1 w-full rounded-lg border p-3" type="email" name="email" value="{{ old('email', $email) }}" required autofocus></label>
      <label class="block">Nowe hasło<input class="mt-1 w-full rounded-lg border p-3" type="password" name="password" required></label>
      <label class="block">Powtórz hasło<input class="mt-1 w-full rounded-lg border p-3" type="password" name="password_confirmation" required></label>
      <button class="btn-magenta w-full" type="submit">Zmień hasło</button>
    </form>
  </div>
</main>
@endsection
