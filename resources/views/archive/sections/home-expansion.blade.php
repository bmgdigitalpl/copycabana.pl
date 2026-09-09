<section class="content-section content-section-muted">
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="section-intro">
      <p class="section-kicker">Następny krok</p>
      <h2 class="font-heading text-4xl font-bold uppercase text-donkerblauw">Wybierz sposób, który pasuje do Twojego zlecenia</h2>
       <p class="mt-4 max-w-3xl text-base leading-7 text-slate-600">Wybierz produkt, sprawdź opcje i przygotuj wiadomość do drukarni.</p>
    </div>
    <div class="content-grid">
      <a href="{{ route('services.diploma') }}" class="content-card">
        <span class="card-icon"><i class="fas fa-graduation-cap"></i></span>
        <h3>Prace dyplomowe</h3>
        <p>Oprawa, druk i bindowanie prac w Katowicach. Zobacz warianty i przygotuj plik do zamówienia.</p>
         <strong class="quick-card-cta">Wydrukuj i opraw pracę</strong>
      </a>
       <a href="#druk-pdf" class="content-card">
         <span class="card-icon"><i class="fas fa-file-pdf"></i></span>
         <h3>Dokumenty PDF</h3>
         <p>Materiały do nauki, instrukcje i dokumenty. Przygotuj plik i parametry do wyceny.</p>
         <strong class="quick-card-cta">Wydrukuj dokumenty PDF</strong>
       </a>
       <a href="{{ route('services.business') }}" class="content-card">
        <span class="card-icon"><i class="fas fa-building"></i></span>
        <h3>Dla firm i agencji</h3>
        <p>Materiały reklamowe, większe nakłady i wyceny dopasowane do konkretnej realizacji.</p>
         <strong class="quick-card-cta">Zobacz druk dla firm</strong>
      </a>
    </div>
     <div class="mt-10 text-center">
       <a href="{{ route('delivery') }}" class="btn-outline-light inline-block">Dostawa i odbiór</a>
      <a href="{{ route('portfolio') }}" class="btn-magenta inline-block">Zobacz przykładowe realizacje <i class="fas fa-arrow-right ml-2"></i></a>
      <a href="{{ route('faq') }}" class="btn-outline-light ml-2 inline-block">Przejdź do FAQ</a>
    </div>
  </div>
</section>
