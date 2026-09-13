@extends('layouts.main')

@section('title', 'Płatność testowa | CopyCabana')

@section('content')
<main class="cc-container" style="padding:9rem 1.5rem 6rem">
  <article class="cc-local-card mx-auto max-w-xl text-center">
    <p class="cc-section-label">Tylko lokalnie</p>
    <h1 class="mt-2 text-3xl font-bold">Płatność testowa</h1>
    <p class="mt-4 text-slate-600">Symulujesz potwierdzenie płatności za zamówienie <strong>{{ $payment->order->number }}</strong>.</p>
    <p class="mt-2 text-2xl font-bold">{{ number_format((float) $payment->amount, 2, ',', ' ') }} {{ $payment->currency }}</p>

    @if ($payment->status === 'pending')
      <form method="POST" action="{{ route('local-payments.store', $payment) }}" class="mt-8">
        @csrf
        <button type="submit" class="btn-magenta">Potwierdź płatność testową</button>
      </form>
    @else
      <p class="mt-8 rounded-lg bg-green-50 p-3 text-green-800">Płatność została już potwierdzona.</p>
    @endif
  </article>
</main>
@endsection
