@extends('layouts.main')
@section('title', 'Logowanie | CopyCabana')
@section('content')
<main class="cc-container" style="padding:9rem 1.5rem 6rem">
  <div class="cc-local-card mx-auto max-w-xl">
    <h1 class="text-3xl font-bold">Zaloguj się</h1>
    <p class="mt-2 text-slate-600">Sprawdź status zamówień i swoich wycen.</p>
    @if(session('status'))<p class="mt-4 rounded-lg bg-green-50 p-3 text-green-800">{{ session('status') }}</p>@endif
    @if($errors->any())<div class="mt-4 rounded-lg bg-red-50 p-3 text-red-800">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-4">
      @csrf
      <label class="block">E-mail<input class="mt-1 w-full rounded-lg border p-3" type="email" name="email" value="{{ old('email') }}" required autofocus></label>
      <label class="block">Hasło<input class="mt-1 w-full rounded-lg border p-3" type="password" name="password" required></label>
      <label class="flex items-center gap-2"><input type="checkbox" name="remember" value="1"> Zapamiętaj mnie</label>
      <button class="btn-magenta w-full" type="submit">Zaloguj się</button>
    </form>
    <div class="mt-5 flex justify-between gap-4 text-sm">
      <a class="text-[#D51A70]" href="{{ route('customer.password.request') }}">Nie pamiętam hasła</a>
      <a class="text-[#D51A70]" href="{{ route('customer.register') }}">Załóż konto</a>
    </div>
  </div>
</main>
@endsection
