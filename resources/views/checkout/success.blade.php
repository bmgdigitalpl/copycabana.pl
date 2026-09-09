@extends('layouts.main')
@section('title', 'Zamówienie przyjęte | CopyCabana')
@section('content')
<main class="cc-container" style="padding:9rem 1.5rem 6rem"><div class="cc-local-card" style="max-width:700px;margin:auto;text-align:center"><h1>Zamówienie przyjęte</h1><p class="mt-4">Numer zamówienia: <strong>{{ $order->number }}</strong></p><p class="mt-2">Wysłaliśmy potwierdzenie na adres {{ $order->customer_email }}.</p><a class="btn-magenta inline-block mt-6" href="{{ route('home') }}">Wróć na stronę główną</a></div></main>
@endsection
