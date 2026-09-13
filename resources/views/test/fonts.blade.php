@extends('layouts.main')

@section('title', 'Test fontów | CopyCabana')
@section('description', 'Porównanie font pairingów dla identyfikacji CopyCabana.')

@section('content')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo:wght@500;600;700;800&amp;family=Inter:wght@400;500;600;700&amp;family=Roboto:wght@400;500;700&amp;family=Roboto+Slab:wght@500;600;700&amp;family=Playfair+Display:wght@500;600;700&amp;family=Montserrat:wght@400;500;600;700&amp;family=Cormorant+Garamond:wght@500;600;700&amp;family=Lora:wght@400;500;600;700&amp;family=Libre+Baskerville:wght@400;700&amp;family=Merriweather:wght@400;700;900&amp;family=Source+Sans+3:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/font-test.css') }}">
<main class="font-test-page">
    <header class="font-test-intro">
        <span class="font-test-kicker">CopyCabana / test typografii</span>
        <h1>Jeden hero.<br>Sześć charakterów.</h1>
        <p>Ta sama treść, te same kolory i ten sam rytm sekcji. Zmieniamy tylko parę fontów, żeby łatwo ocenić, który kierunek najlepiej pasuje do drukarni.</p>
    </header>

    <section class="font-test-grid" aria-label="Porównanie par fontów">
        @php($pairings = [
            ['class' => 'pair-archivo-inter', 'name' => 'Archivo + Inter', 'mood' => 'Obecny kierunek', 'title' => 'Ty masz plik. My zajmiemy się drukiem.', 'text' => 'Nowoczesny, konkretny i dobrze czytelny. Najbardziej produktowy wariant dla całego serwisu.', 'score' => 'energetyczny\nbezpieczny'],
            ['class' => 'pair-roboto-slab', 'name' => 'Roboto Slab + Roboto', 'mood' => 'Solidny / usługowy', 'title' => 'Ty masz plik. My zajmiemy się drukiem.', 'text' => 'Przyjazny i bardzo praktyczny. Slab dodaje drukarskiego skojarzenia bez utraty współczesnego rytmu.', 'score' => 'czytelny\nprofesjonalny'],
            ['class' => 'pair-playfair', 'name' => 'Playfair Display + Montserrat', 'mood' => 'Premium / editorial', 'title' => 'Ty masz plik. My zajmiemy się drukiem.', 'text' => 'Elegancki kontrast dla prac dyplomowych i usług premium. Mocniej przesuwa markę w stronę studia projektowego.', 'score' => 'elegancki\nwyrazisty'],
            ['class' => 'pair-cormorant', 'name' => 'Cormorant + Cormorant Garamond', 'mood' => 'Artystyczny / papier', 'title' => 'Ty masz plik. My zajmiemy się drukiem.', 'text' => 'Najbardziej redakcyjny i miękki wariant. Piękny przy dużych nagłówkach, mniej praktyczny dla konfiguratorów.', 'score' => 'charakterystyczny\nsubtelny'],
            ['class' => 'pair-lora', 'name' => 'Lora + Libre Baskerville', 'mood' => 'Klasyczny / zaufany', 'title' => 'Ty masz plik. My zajmiemy się drukiem.', 'text' => 'Spokojny i edukacyjny. Dobrze pasuje do treści o pracach dyplomowych, lokalności i jakości wykonania.', 'score' => 'klasyczny\nwiarygodny'],
            ['class' => 'pair-merriweather', 'name' => 'Source Sans 3 + Merriweather', 'mood' => 'Nowoczesny / content', 'title' => 'Ty masz plik. My zajmiemy się drukiem.', 'text' => 'Mocny nagłówek bezszeryfowy i tekst z szeryfem. Daje bardziej magazynowy, spokojny ton komunikacji.', 'score' => 'redakcyjny\nspokojny'],
        ])
        @foreach($pairings as $index => $pair)
            <article class="font-test-card {{ $pair['class'] }}">
                <div class="font-test-meta"><span>{{ $pair['name'] }}</span><span class="font-test-number">0{{ $index + 1 }}</span></div>
                <div class="font-test-hero">
                    <div><small>{{ $pair['mood'] }}</small><h2>{{ $pair['title'] }}</h2><p>{{ $pair['text'] }}</p></div>
                    <footer><a href="{{ route('services.diploma') }}" class="font-test-cta">Wybierz usługę <span aria-hidden="true">→</span></a><span class="font-test-score">{!! nl2br(e($pair['score'])) !!}</span></footer>
                </div>
            </article>
        @endforeach
    </section>
    <p class="font-test-note">Wskazówka: wybierając font, patrz nie tylko na pierwszy efekt. Sprawdź również dłuższe opisy, ceny, formularze i polskie znaki: ą ć ę ł ń ó ś ź ż.</p>
</main>
@endsection
