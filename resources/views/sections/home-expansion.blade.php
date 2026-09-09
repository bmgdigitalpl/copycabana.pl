<section class="content-section content-section-muted">
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="section-intro">
      <p class="section-kicker">Następny krok</p>
      <h2 class="font-heading text-4xl font-bold uppercase text-donkerblauw">Wybierz sposób, który pasuje do Twojego zlecenia</h2>
      <p class="mt-4 max-w-3xl text-base leading-7 text-slate-600">Możesz skonfigurować popularny produkt online, sprawdzić informacje o odbiorze albo opisać większą realizację dla firmy.</p>
    </div>
    <div class="content-grid">
      <a href="{{ route('services.diploma') }}" class="content-card">
        <span class="card-icon"><i class="fas fa-graduation-cap"></i></span>
        <h3>Prace dyplomowe</h3>
        <p>Oprawa, druk i bindowanie prac w Katowicach. Zobacz warianty i przygotuj plik do zamówienia.</p>
        <strong class="quick-card-cta">Przejdź do informacji →</strong>
      </a>
      <a href="{{ route('services.business') }}" class="content-card">
        <span class="card-icon"><i class="fas fa-building"></i></span>
        <h3>Dla firm i agencji</h3>
        <p>Materiały reklamowe, większe nakłady i wyceny dopasowane do konkretnej realizacji.</p>
        <strong class="quick-card-cta">Zobacz ofertę dla firm →</strong>
      </a>
      <a href="{{ route('delivery') }}" class="content-card">
        <span class="card-icon"><i class="fas fa-truck"></i></span>
        <h3>Dostawa i odbiór</h3>
        <p>Sprawdź, jak wygląda odbiór osobisty w Katowicach i przygotowanie zamówienia z wysyłką.</p>
        <strong class="quick-card-cta">Sprawdź szczegóły →</strong>
      </a>
    </div>
    <div class="mt-10 text-center">
      <a href="{{ route('portfolio') }}" class="btn-magenta inline-block">Zobacz przykładowe realizacje <i class="fas fa-arrow-right ml-2"></i></a>
      <a href="{{ route('faq') }}" class="btn-outline-light ml-2 inline-block">Przejdź do FAQ</a>
    </div>
  </div>
</section>
