@extends('layouts.main')

@section('title', 'Polityka cookies | CopyCabana')
@section('description', 'Polityka cookies CopyCabana: cookies niezbędne, analityczne, Google Analytics, zgody użytkownika i zarządzanie ustawieniami.')

@section('content')
<main class="cc-container" style="padding:9rem 1.5rem 6rem">
  <article class="cc-local-card space-y-8" style="max-width:950px;margin:auto">
    <header class="space-y-4">
      <p class="text-sm font-semibold uppercase tracking-[0.2em] text-[#D51A70]">CopyCabana</p>
      <h1>Polityka cookies</h1>
      <p class="text-lg text-slate-600">Ten dokument wyjaśnia, czym są cookies, jakie kategorie cookies może wykorzystywać CopyCabana i jak użytkownik może zarządzać zgodami.</p>
      <p class="text-sm text-slate-500">Wersja dokumentu: {{ config('privacy.cookies_policy_version') }}</p>
    </header>

    <section class="space-y-3">
      <h2 class="text-2xl font-semibold">1. Czym są cookies</h2>
      <p>Cookies to niewielkie pliki zapisywane w przeglądarce lub na urządzeniu użytkownika. Podobne funkcje mogą pełnić także inne technologie lokalnego przechowywania danych, np. local storage. W tej polityce określamy je wspólnie jako cookies.</p>
    </section>

    <section class="space-y-3">
      <h2 class="text-2xl font-semibold">2. Cookies niezbędne</h2>
      <p>Cookies niezbędne są wymagane do prawidłowego działania serwisu. Umożliwiają m.in. utrzymanie sesji, logowanie, obsługę koszyka, składanie zamówień, zapamiętanie ustawień technicznych oraz zapewnienie bezpieczeństwa.</p>
      <p>Te cookies mogą działać bez dodatkowej zgody użytkownika, ponieważ są konieczne do świadczenia usługi drogą elektroniczną.</p>
    </section>

    <section class="space-y-3">
      <h2 class="text-2xl font-semibold">3. Cookies analityczne</h2>
      <p>Cookies analityczne pomagają nam zrozumieć, jak użytkownicy korzystają z serwisu, które strony są odwiedzane, skąd pochodzi ruch i które elementy wymagają poprawy.</p>
      <p>Na start planujemy wykorzystywać Google Analytics. Cookies analityczne są uruchamiane dopiero po wyrażeniu zgody w panelu cookies.</p>
    </section>

    <section class="space-y-3">
      <h2 class="text-2xl font-semibold">4. Cookies marketingowe</h2>
      <p>Na moment publikacji tej polityki CopyCabana nie zakłada aktywnego wykorzystywania cookies marketingowych, takich jak Meta Pixel, Google Ads remarketing lub podobne narzędzia reklamowe.</p>
      <p>Jeżeli takie narzędzia zostaną wdrożone w przyszłości, będą wymagały aktualizacji ustawień zgód oraz odpowiedniej informacji w polityce prywatności i cookies.</p>
    </section>

    <section class="space-y-3">
      <h2 class="text-2xl font-semibold">5. Zarządzanie zgodami</h2>
      <p>Użytkownik może zaakceptować, odrzucić lub dostosować kategorie cookies innych niż niezbędne. Zgoda może zostać wycofana lub zmieniona w dowolnym momencie.</p>
      <p><button type="button" class="text-[#D51A70] font-semibold" data-cookie-settings>Otwórz ustawienia cookies</button></p>
      <p>Użytkownik może także ograniczyć cookies z poziomu ustawień swojej przeglądarki. Ograniczenie cookies niezbędnych może jednak spowodować, że konto, koszyk lub zamówienia nie będą działały prawidłowo.</p>
    </section>

    <section class="space-y-3">
      <h2 class="text-2xl font-semibold">6. Dostawcy zewnętrzni</h2>
      <p>W związku z cookies i analityką możemy korzystać z usług Google Analytics. Dostawcy zewnętrzni mogą przetwarzać dane zgodnie ze swoimi zasadami prywatności i ustawieniami zgód użytkownika.</p>
    </section>

    <section class="space-y-3">
      <h2 class="text-2xl font-semibold">7. Kontakt</h2>
      <p>W sprawach dotyczących cookies i prywatności możesz skontaktować się z nami pod adresem <a class="text-[#D51A70]" href="mailto:biuro@copycabana.pl">biuro@copycabana.pl</a>.</p>
      <p>Więcej informacji o przetwarzaniu danych osobowych znajduje się w <a class="text-[#D51A70]" href="{{ route('privacy') }}">Polityce prywatności</a>.</p>
    </section>
  </article>
</main>
@endsection
