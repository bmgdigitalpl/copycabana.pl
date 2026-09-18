@extends('layouts.main')
@section('title', 'Załóż konto | CopyCabana')
@section('content')
<main class="cc-container" style="padding:9rem 1.5rem 6rem">
  <div class="cc-local-card mx-auto max-w-xl">
    <h1 class="text-3xl font-bold">Załóż konto klienta</h1>
    <p class="mt-2 text-slate-600">Po rejestracji potwierdź adres e-mail, aby otworzyć portal.</p>
    @if($errors->any())<div class="mt-4 rounded-lg bg-red-50 p-3 text-red-800">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('customer.register.store') }}" class="mt-6 space-y-4">
      @csrf
      <label class="block">Imię i nazwisko<input class="mt-1 w-full rounded-lg border p-3" type="text" name="name" value="{{ old('name') }}" required autofocus></label>
      <label class="block">E-mail<input class="mt-1 w-full rounded-lg border p-3" type="email" name="email" value="{{ old('email') }}" required></label>
      <label class="block">Hasło<input class="mt-1 w-full rounded-lg border p-3" type="password" name="password" required></label>
      <label class="block">Powtórz hasło<input class="mt-1 w-full rounded-lg border p-3" type="password" name="password_confirmation" required></label>
      <label class="flex items-start gap-2"><input class="mt-1" type="checkbox" name="privacy_policy_accepted" value="1" required> Akceptuję <a class="text-[#D51A70]" href="{{ route('privacy') }}">politykę prywatności</a>.</label>
      <button class="btn-magenta w-full" type="submit">Utwórz konto</button>
    </form>
    <p class="mt-5 text-center text-sm">Masz już konto? <a class="text-[#D51A70]" href="{{ route('login') }}">Zaloguj się</a></p>
  </div>
</main>
@endsection
