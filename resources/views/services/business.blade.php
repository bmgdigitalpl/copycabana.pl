@extends('layouts.site')

@section('title', 'Druk dla firm i agencji w Katowicach — CopyCabana')
@section('description', 'Druk dla firm, agencji reklamowych i organizacji w Katowicach. Wizytówki, ulotki, plakaty, banery i większe nakłady.')

@section('content')
<main class="page-content">
  <section class="page-header">
    <p class="section-kicker text-geel">Dla firm i agencji</p>
    <h1>Druk, który dowozi temat</h1>
    <div class="underline"></div>
  </section>

  <section class="content-section">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="content-copy"><p class="section-kicker">Współpraca</p><h2>Materiały reklamowe od pojedynczych sztuk po większe nakłady</h2><p>Pomagamy dobrać format, papier, wykończenie i technologię druku do celu realizacji. Jeśli projekt wymaga indywidualnej kalkulacji, opisz zakres i skontaktuj się z nami.</p><div class="eyebrow-list"><span>Wizytówki</span><span>Ulotki</span><span>Plakaty</span><span>Banery</span><span>Rollupy</span><span>Projekty graficzne</span></div><a href="{{ route('services.business') }}" class="btn-magenta mt-7 inline-block">Przejdź do konfiguratora <i class="fas fa-arrow-right ml-2"></i></a></div>
    </div>
  </section>

  <section class="content-section content-section-muted">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"><div class="section-intro"><p class="section-kicker">Zakres</p><h2 class="font-heading text-4xl font-bold uppercase text-donkerblauw">W czym możemy pomóc</h2></div><div class="content-grid"><article class="content-card"><span class="card-icon"><i class="fas fa-print"></i></span><h3>Druk materiałów</h3><p>Wizytówki, ulotki, plakaty, kalendarze i naklejki dopasowane do Twojej komunikacji.</p></article><article class="content-card"><span class="card-icon"><i class="fas fa-expand"></i></span><h3>Formaty duże</h3><p>Banery, billboardy, rollupy i inne materiały do ekspozycji w przestrzeni.</p></article><article class="content-card"><span class="card-icon"><i class="fas fa-pen-ruler"></i></span><h3>Projektowanie</h3><p>Jeśli nie masz gotowego pliku, możemy pomóc przygotować projekt do druku.</p></article></div></div>
  </section>

  <section class="content-section"><div class="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-[1fr_auto] lg:items-center lg:px-8"><div><p class="section-kicker">Niestandardowe zlecenie</p><h2 class="font-heading text-4xl font-bold uppercase text-donkerblauw">Masz konkretny brief?</h2><p class="mt-4 max-w-2xl leading-8 text-slate-600">Wyślij zakres, format, nakład i termin. Wrócimy z propozycją rozwiązania i wyceny.</p></div><a href="{{ route('contact') }}" class="btn-geel inline-block">Opisz realizację <i class="fas fa-arrow-right ml-2"></i></a></div></section>
</main>
@endsection
