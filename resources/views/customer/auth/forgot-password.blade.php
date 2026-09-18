@extends('layouts.main')
@section('title', 'Reset hasła | CopyCabana')
@section('content')
<main class="cc-container" style="padding:9rem 1.5rem 6rem">
  <div class="cc-local-card mx-auto max-w-xl">
    <h1 class="text-3xl font-bold">Reset hasła</h1>
    <p class="mt-2 text-slate-600">Wyślemy instrukcję na adres przypisany do konta.</p>
    @if(session('status'))<p class="mt-4 rounded-lg bg-green-50 p-3 text-green-800">{{ session('status') }}</p>@endif
    @if($errors->any())<div class="mt-4 rounded-lg bg-red-50 p-3 text-red-800">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('customer.password.email') }}" class="mt-6 space-y-4">
      @csrf
      <label class="block">E-mail<input class="mt-1 w-full rounded-lg border p-3" type="email" name="email" value="{{ old('email') }}" required autofocus></label>
      <button class="btn-magenta w-full" type="submit">Wyślij instrukcję</button>
    </form>
    <p class="mt-5 text-center text-sm"><a class="text-[#D51A70]" href="{{ route('login') }}">Wróć do logowania</a></p>
  </div>
</main>
@endsection
