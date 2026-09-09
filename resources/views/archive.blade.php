@extends('layouts.site')

@section('title', 'Archiwum strony — CopyCabana')
@section('description', 'Archiwum starszych wersji stron CopyCabana — strona główna, oprawa prac dyplomowych i usługi dla firm.')

@section('content')
<section class="content-section">
  <div class="mx-auto max-w-4xl px-4 pt-10 pb-6 text-center sm:px-6 lg:px-8">
    <p class="section-kicker">Archiwum</p>
    <h1 class="font-heading text-4xl font-bold uppercase text-donkerblauw">Starsze wersje stron</h1>
    <p class="mt-4 leading-8 text-slate-600">Nowa strona działa pod głównymi adresami. Tu znajdziesz poprzednie wersje stron, które koncept zastąpił — na razie zachowane pod tymi linkami.</p>
  </div>
</section>

<section class="content-section content-section-muted">
  <div class="mx-auto grid max-w-4xl gap-6 px-4 pb-10 sm:px-6 lg:px-8">
    <a href="{{ route('archive.home') }}" class="content-card">
      <h3>Poprzednia strona główna</h3>
      <p>Stara wersja strony głównej z przekierowaniem na wybrane produkty i usługi.</p>
    </a>
    <a href="{{ route('archive.diploma') }}" class="content-card">
      <h3>Oprawa prac dyplomowych — stara strona usługi</h3>
      <p>Poprzednia strona usługi oprawy prac dyplomowych. Nowa wersja: /prace-dyplomowe.</p>
    </a>
    <a href="{{ route('archive.business') }}" class="content-card">
      <h3>Druk dla firm — stara strona usługi</h3>
      <p>Poprzednia strona usługi dla firm. Nowa wersja: /druk-dla-firm.</p>
    </a>
    <p class="text-sm text-slate-500">Strony archiwalne działają na starym wyglądzie i nie są traktowane jako główny design.</p>
  </div>
</section>
@endsection
