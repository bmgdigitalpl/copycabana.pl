@extends('layouts.main')

@section('title', 'Polityka prywatności | CopyCabana')
@section('description', 'Polityka prywatności CopyCabana: zasady przetwarzania danych osobowych, plików klientów, płatności PayU, dostawy i praw użytkownika.')

@section('content')
<main class="cc-container" style="padding:9rem 1.5rem 6rem">
  <article class="cc-local-card space-y-8" style="max-width:950px;margin:auto">
    <header class="space-y-4">
      <p class="text-sm font-semibold uppercase tracking-[0.2em] text-[#D51A70]">CopyCabana</p>
      <h1>Polityka prywatności</h1>
      <p class="text-lg text-slate-600">Ten dokument opisuje, jakie dane osobowe przetwarzamy w związku z korzystaniem z serwisu CopyCabana, składaniem zamówień, korzystaniem z konta klienta, płatnościami, dostawą oraz kontaktem z nami.</p>
      <p class="text-sm text-slate-500">Wersja dokumentu: {{ config('privacy.policy_version') }}</p>
    </header>

    <section class="space-y-3">
      <h2 class="text-2xl font-semibold">1. Administrator danych</h2>
      <p>Administratorem danych osobowych jest LITEKST Jarosław Lipiec, ul. Bankowa 11, 40-007 Katowice, NIP: 6342412192, prowadzący serwis internetowy CopyCabana dostępny pod adresem copycabana.pl.</p>
      <p>W sprawach dotyczących danych osobowych możesz skontaktować się z nami pod adresem <a class="text-[#D51A70]" href="mailto:biuro@copycabana.pl">biuro@copycabana.pl</a>.</p>
    </section>

    <section class="space-y-3">
      <h2 class="text-2xl font-semibold">2. Jakie dane przetwarzamy</h2>
      <p>W zależności od sposobu korzystania z serwisu możemy przetwarzać następujące kategorie danych:</p>
      <ul class="list-disc space-y-2 pl-6 text-slate-700">
        <li>dane identyfikacyjne i kontaktowe, takie jak imię, nazwisko, firma, adres email i numer telefonu,</li>
        <li>dane konta użytkownika, w tym adres email, hasło w postaci zaszyfrowanej oraz historia aktywności istotna dla obsługi konta,</li>
        <li>dane zamówienia, konfigurację produktu, wybrane opcje druku, dane odbioru lub dostawy oraz informacje o statusie realizacji,</li>
        <li>dane potrzebne do wystawienia faktury, w szczególności nazwę firmy, adres, NIP i dane zamówienia,</li>
        <li>dane dotyczące płatności online przekazywane przez operatora płatności PayU, bez przechowywania przez nas pełnych danych karty płatniczej,</li>
        <li>pliki przesyłane do wyceny lub realizacji zamówienia, w tym projekty graficzne, dokumenty PDF, prace dyplomowe, pliki produkcyjne i inne materiały klienta,</li>
        <li>dane techniczne, takie jak adres IP, identyfikatory cookies, informacje o urządzeniu, przeglądarce, logach bezpieczeństwa i sposobie korzystania z serwisu.</li>
      </ul>
    </section>

    <section class="space-y-3">
      <h2 class="text-2xl font-semibold">3. Cele i podstawy prawne przetwarzania</h2>
      <p>Dane osobowe przetwarzamy w następujących celach:</p>
      <ul class="list-disc space-y-2 pl-6 text-slate-700">
        <li>założenie i prowadzenie konta klienta - na podstawie wykonania umowy o świadczenie usługi konta,</li>
        <li>przyjęcie, wycena, przygotowanie i realizacja zamówienia - na podstawie wykonania umowy lub działań przed jej zawarciem,</li>
        <li>obsługa płatności online przez PayU - na podstawie wykonania umowy oraz obowiązków rozliczeniowych,</li>
        <li>wystawianie i przechowywanie faktur oraz dokumentów księgowych - na podstawie obowiązków prawnych,</li>
        <li>organizacja odbioru osobistego, dostawy kurierskiej lub dostawy do paczkomatu InPost - na podstawie wykonania umowy,</li>
        <li>kontakt w sprawie zapytań, zamówień, reklamacji i obsługi klienta - na podstawie wykonania umowy lub naszego uzasadnionego interesu,</li>
        <li>zapewnienie bezpieczeństwa serwisu, zapobieganie nadużyciom i prowadzenie logów - na podstawie naszego uzasadnionego interesu,</li>
        <li>dochodzenie lub obrona przed roszczeniami - na podstawie naszego uzasadnionego interesu,</li>
        <li>analityka działania serwisu przy użyciu Google Analytics - na podstawie zgody użytkownika, jeżeli taka zgoda jest wymagana.</li>
      </ul>
    </section>

    <section class="space-y-3">
      <h2 class="text-2xl font-semibold">4. Pliki przesyłane przez klientów</h2>
      <p>Pliki przesłane przez klienta wykorzystujemy wyłącznie w celu wyceny, przygotowania i realizacji zamówienia albo obsługi reklamacji. Pliki są przechowywane tymczasowo na serwerze i usuwane po upływie {{ config('privacy.uploaded_file_retention_days') }} dni od finalizacji zamówienia.</p>
      <p>Dłuższe przechowywanie plików może nastąpić wyłącznie wtedy, gdy jest to niezbędne do obsługi reklamacji, zabezpieczenia roszczeń, wyjaśnienia nadużycia lub wykonania obowiązku prawnego.</p>
      <p>Klient powinien przesyłać wyłącznie pliki, do których posiada odpowiednie prawa lub upoważnienie oraz które nie naruszają praw osób trzecich.</p>
    </section>

    <section class="space-y-3">
      <h2 class="text-2xl font-semibold">5. Płatności, faktury i dostawa</h2>
      <p>Płatności online obsługuje PayU. W ramach płatności możemy otrzymywać informacje o statusie transakcji, identyfikatorze płatności i kwocie płatności. Nie przechowujemy pełnych danych kart płatniczych.</p>
      <p>Jeżeli zamawiasz fakturę, przetwarzamy dane wymagane przepisami podatkowymi i rachunkowymi. Dane fakturowe przechowujemy przez okres wymagany przez obowiązujące przepisy prawa.</p>
      <p>Zamówienia mogą być odbierane osobiście albo dostarczane przez kuriera lub do paczkomatu InPost. W celu realizacji dostawy możemy przekazać dostawcy dane niezbędne do doręczenia przesyłki, takie jak imię i nazwisko, adres, numer telefonu, email oraz numer zamówienia.</p>
    </section>

    <section class="space-y-3">
      <h2 class="text-2xl font-semibold">6. Odbiorcy danych</h2>
      <p>Dane możemy przekazywać podmiotom, które pomagają nam prowadzić serwis i realizować zamówienia, w szczególności:</p>
      <ul class="list-disc space-y-2 pl-6 text-slate-700">
        <li>dostawcy hostingu i poczty elektronicznej UTI.pl,</li>
        <li>operatorowi płatności PayU,</li>
        <li>firmom kurierskim oraz InPost, jeżeli wybierzesz dostawę,</li>
        <li>podmiotom świadczącym obsługę księgową, podatkową, prawną lub techniczną,</li>
        <li>dostawcom narzędzi analitycznych, takich jak Google Analytics, jeżeli użytkownik wyrazi odpowiednią zgodę,</li>
        <li>organom publicznym, jeżeli wymagają tego obowiązujące przepisy prawa.</li>
      </ul>
    </section>

    <section class="space-y-3">
      <h2 class="text-2xl font-semibold">7. Jak długo przechowujemy dane</h2>
      <ul class="list-disc space-y-2 pl-6 text-slate-700">
        <li>dane konta klienta przechowujemy przez czas prowadzenia konta, a po jego usunięciu przez okres niezbędny do rozliczeń, obsługi roszczeń lub wykonania obowiązków prawnych,</li>
        <li>dane zamówień przechowujemy przez okres potrzebny do realizacji, rozliczenia, obsługi reklamacji i obrony przed roszczeniami,</li>
        <li>dane fakturowe i księgowe przechowujemy przez okres wymagany przepisami podatkowymi i rachunkowymi,</li>
        <li>pliki klientów usuwamy po {{ config('privacy.uploaded_file_retention_days') }} dniach od finalizacji zamówienia, z wyjątkami opisanymi w tej polityce,</li>
        <li>dane związane ze zgodami przechowujemy przez okres potrzebny do wykazania, kiedy i jak zgoda została udzielona lub wycofana,</li>
        <li>dane techniczne i logi bezpieczeństwa przechowujemy przez okres potrzebny do zapewnienia bezpieczeństwa serwisu i wyjaśnienia incydentów.</li>
      </ul>
    </section>

    <section class="space-y-3">
      <h2 class="text-2xl font-semibold">8. Usunięcie konta</h2>
      <p>Użytkownik może samodzielnie usunąć konto w panelu klienta. Usunięcie konta nie usuwa automatycznie danych, które musimy przechowywać z powodu obowiązków prawnych, rozliczeń, reklamacji lub zabezpieczenia roszczeń.</p>
    </section>

    <section class="space-y-3">
      <h2 class="text-2xl font-semibold">9. Cookies i analityka</h2>
      <p>Serwis wykorzystuje cookies niezbędne do działania strony, konta, koszyka, zamówień i bezpieczeństwa. Cookies analityczne, w tym Google Analytics, będą wykorzystywane zgodnie z ustawieniami zgód użytkownika.</p>
      <p>Szczegółowe informacje znajdują się w <a class="text-[#D51A70]" href="{{ route('cookies') }}">Polityce cookies</a>.</p>
    </section>

    <section class="space-y-3">
      <h2 class="text-2xl font-semibold">10. Prawa użytkownika</h2>
      <p>Masz prawo żądać dostępu do swoich danych, ich sprostowania, usunięcia, ograniczenia przetwarzania, przeniesienia danych, wniesienia sprzeciwu wobec przetwarzania oraz wycofania zgody, jeżeli przetwarzanie odbywa się na podstawie zgody.</p>
      <p>Masz także prawo wniesienia skargi do Prezesa Urzędu Ochrony Danych Osobowych, jeżeli uznasz, że przetwarzamy dane niezgodnie z prawem.</p>
    </section>

    <section class="space-y-3">
      <h2 class="text-2xl font-semibold">11. Zmiany polityki</h2>
      <p>Polityka prywatności może być aktualizowana w związku ze zmianami w serwisie, zmianami dostawców usług, zmianami prawnymi lub rozwojem funkcji CopyCabana. Aktualna wersja dokumentu jest zawsze dostępna na tej stronie.</p>
    </section>
  </article>
</main>
@endsection
