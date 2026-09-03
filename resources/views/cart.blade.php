<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Koszyk — CopyCabana</title>
  <link rel="icon" href="images/favicon.png" type="image/png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@500;600;700;800&amp;family=Caveat:wght@700&amp;family=Inter:wght@400;500;600;700&amp;family=Montserrat:wght@300;400;600;700;800&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            donkerblauw: '#063A60',
            blauw: '#00456F',
            geel: '#FFED00',
            wit: '#F5F5F5',
            lichtgrijs: '#D9D9DD',
            magenta: '#D51A70',
            groen: '#7FBF45',
            paars: '#6B3FA0',
          },
          fontFamily: {
            heading: ['Montserrat', 'sans-serif'],
            body: ['Montserrat', 'sans-serif'],
            logo: ['Caveat', 'cursive'],
          },
        },
      },
    }
  </script>
  <link rel="stylesheet" href="css/custom.css">
  <link rel="stylesheet" href="css/dynamic-local-service.css">
  <link rel="stylesheet" href="css/dynamic-site.css">
</head>
<body class="concept-dynamic" x-data="cartPage()" x-init="init()">

  <!-- ========== NAVBAR ========== -->
  <nav id="navbar" class="navbar fixed top-0 left-0 right-0 z-50 bg-donkerblauw/95 backdrop-blur-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
      <a href="index.html" class="font-logo text-2xl text-geel no-underline">CopyCabana</a>
      <div class="hidden md:flex items-center gap-8">
        <a href="index.html" class="nav-link text-white text-sm no-underline">Home</a>
        <a href="produkty.html" class="nav-link text-white text-sm no-underline">Produkty</a>
        <a href="o-nas.html" class="nav-link text-white text-sm no-underline">O nas</a>
        <a href="kontakt.html" class="nav-link text-white text-sm no-underline">Kontakt</a>
        <a href="koszyk.html" class="nav-link text-white text-sm no-underline relative">
          <i class="fas fa-shopping-cart"></i>
          <span class="cart-badge absolute -top-2 -right-3 bg-magenta text-white text-xs w-5 h-5 rounded-full flex items-center justify-center" x-text="cartCount" x-show="cartCount > 0"></span>
        </a>
      </div>
      <button @click="mobileNav = true" class="md:hidden text-white text-xl bg-transparent border-none cursor-pointer">
        <i class="fas fa-bars"></i>
      </button>
    </div>
  </nav>

  <!-- ========== MOBILE NAV ========== -->
  <div class="mobile-nav-overlay" :class="{ 'open': mobileNav }" @click="mobileNav = false"></div>
  <div class="mobile-nav" :class="{ 'open': mobileNav }">
    <button @click="mobileNav = false" class="close-btn"><i class="fas fa-times"></i></button>
    <div class="mt-12">
      <a href="index.html">Home</a>
      <a href="produkty.html">Produkty</a>
      <a href="o-nas.html">O nas</a>
      <a href="kontakt.html">Kontakt</a>
      <a href="koszyk.html">Koszyk</a>
    </div>
  </div>

  <!-- ========== PAGE HEADER ========== -->
  <section class="page-header mt-16">
    <h1>Twój koszyk</h1>
    <div class="underline"></div>
  </section>

  <!-- ========== CART CONTENT ========== -->
  <main class="section-wit py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

      <!-- Empty Cart -->
      <template x-if="items.length === 0">
        <div class="text-center py-16">
          <i class="fas fa-shopping-cart text-6xl text-lichtgrijs mb-4"></i>
          <h2 class="text-xl font-semibold mb-2">Koszyk jest pusty</h2>
          <p class="text-gray-500 mb-6">Dodaj produkty z naszej oferty, aby zobaczyć tutaj swoje zamówienie.</p>
          <a href="produkty.html" class="btn-magenta inline-block">Przeglądaj produkty <i class="fas fa-arrow-right ml-2"></i></a>
        </div>
      </template>

      <!-- Cart Items -->
      <template x-if="items.length > 0">
        <div>
          <div class="space-y-4 mb-8">
            <template x-for="item in items" :key="item.id">
              <div class="admin-card p-5 flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                  <div class="flex items-start justify-between">
                    <div>
                      <h3 class="font-semibold" x-text="item.productName"></h3>
                      <p class="text-sm text-gray-500 mt-1" x-text="item.summary"></p>
                    </div>
                    <button @click="removeItem(item.id)" class="text-gray-400 hover:text-magenta transition">
                      <i class="fas fa-trash-alt"></i>
                    </button>
                  </div>
                  <div class="flex items-center gap-4 mt-3">
                    <div class="flex items-center border border-lichtgrijs rounded-lg overflow-hidden">
                      <button @click="updateQty(item.id, item.quantity - 1)" class="px-3 py-1 hover:bg-gray-100 transition text-sm">−</button>
                      <span class="px-3 py-1 text-sm font-medium min-w-[40px] text-center" x-text="item.quantity"></span>
                      <button @click="updateQty(item.id, item.quantity + 1)" class="px-3 py-1 hover:bg-gray-100 transition text-sm">+</button>
                    </div>
                    <span class="font-bold text-magenta" x-text="(item.price * item.quantity).toFixed(2).replace('.',',') + ' zł'"></span>
                  </div>
                </div>
              </div>
            </template>
          </div>

          <!-- Summary -->
          <div class="admin-card p-6 mb-8">
            <div class="flex justify-between text-lg font-bold mb-4">
              <span>Suma:</span>
              <span class="text-magenta" x-text="total.toFixed(2).replace('.',',') + ' zł'"></span>
            </div>
            <p class="text-xs text-gray-400 mb-4">Ceny są orientacyjne. Ostateczna wycena nastąpi po kontakcie z naszym biurem.</p>

            <!-- Order Form -->
            <div class="border-t pt-4 mt-4">
              <h3 class="font-semibold mb-3">Dane do zamówienia</h3>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="text-xs text-gray-500">Imię i nazwisko *</label>
                  <input type="text" x-model="customer.name" class="w-full px-3 py-2 border border-lichtgrijs rounded-lg text-sm mt-1">
                </div>
                <div>
                  <label class="text-xs text-gray-500">Email *</label>
                  <input type="email" x-model="customer.email" class="w-full px-3 py-2 border border-lichtgrijs rounded-lg text-sm mt-1">
                </div>
                <div>
                  <label class="text-xs text-gray-500">Telefon</label>
                  <input type="tel" x-model="customer.phone" class="w-full px-3 py-2 border border-lichtgrijs rounded-lg text-sm mt-1">
                </div>
                <div>
                  <label class="text-xs text-gray-500">Firma</label>
                  <input type="text" x-model="customer.company" class="w-full px-3 py-2 border border-lichtgrijs rounded-lg text-sm mt-1">
                </div>
              </div>
              <div class="mt-4">
                <label class="text-xs text-gray-500">Uwagi do zamówienia</label>
                <textarea x-model="customer.notes" rows="3" class="w-full px-3 py-2 border border-lichtgrijs rounded-lg text-sm mt-1" placeholder="Termin realizacji, dodatkowe informacje..."></textarea>
              </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 mt-6">
              <button @click="submitOrder()" class="bg-groen text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-600 transition flex-1">
                <i class="fas fa-paper-plane mr-2"></i> Wyślij zamówienie
              </button>
              <button @click="clearCart()" class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 transition">
                <i class="fas fa-trash mr-2"></i> Wyczyść koszyk
              </button>
            </div>
          </div>

          <!-- Success Message -->
          <template x-if="orderSubmitted">
            <div class="bg-green-50 border border-green-200 rounded-xl p-6 text-center">
              <i class="fas fa-check-circle text-green-500 text-4xl mb-3"></i>
              <h3 class="text-lg font-bold text-green-700 mb-2">Zamówienie wysłane!</h3>
              <p class="text-sm text-green-600">Dziękujemy! Skontaktujemy się z Tobą w ciągu 24 godzin z potwierdzeniem i finalną wyceną.</p>
              <a href="produkty.html" class="inline-block mt-4 text-donkerblauw font-semibold hover:underline">Wróć do produktów →</a>
            </div>
          </template>
        </div>
      </template>
    </div>
  </main>

  <!-- ========== FOOTER ========== -->
  <footer class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
        <div>
          <a href="index.html" class="font-logo text-2xl text-geel no-underline block mb-4">CopyCabana</a>
          <div class="flex flex-col gap-2">
            <a href="index.html">Home</a>
            <a href="produkty.html">Produkty</a>
            <a href="o-nas.html">O nas</a>
            <a href="kontakt.html">Kontakt</a>
          </div>
        </div>
        <div>
          <h4 class="text-geel font-semibold text-sm mb-4">Popularne produkty</h4>
          <div class="flex flex-col gap-2">
            <a href="produkt.html?slug=wizytowki">Wizytówki</a>
            <a href="produkt.html?slug=ulotki">Ulotki</a>
            <a href="produkt.html?slug=plakaty">Plakaty</a>
            <a href="produkt.html?slug=banery">Banery</a>
            <a href="produkt.html?slug=oprawa_prac">Oprawa prac</a>
          </div>
        </div>
        <div>
          <h4 class="text-geel font-semibold text-sm mb-4">Kontakt</h4>
          <div class="flex flex-col gap-2 text-white/75 text-sm">
            <p><i class="fas fa-map-marker-alt mr-2 text-geel"></i>ul. Bankowa 11, 40-007 Katowice</p>
            <p><i class="fas fa-phone mr-2 text-geel"></i><a href="tel:502293849">502 293 849</a> / <a href="tel:504939094">504 939 094</a></p>
            <p><i class="fas fa-envelope mr-2 text-geel"></i><a href="mailto:biuro@copycabana.pl">biuro@copycabana.pl</a></p>
            <p><i class="fas fa-clock mr-2 text-geel"></i>Pn–Pt 8:00–16:00, Sob 9:00–15:00</p>
          </div>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <div class="max-w-7xl mx-auto px-4">&copy; 2024 CopyCabana.pl — Wszelkie prawa zastrzeżone.</div>
    </div>
  </footer>

  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <script src="data/prices.js"></script>
  <script src="js/cart.js"></script>
  <script>
    function cartPage() {
      return {
        mobileNav: false,
        items: [],
        cartCount: 0,
        total: 0,
        customer: { name: '', email: '', phone: '', company: '', notes: '' },
        orderSubmitted: false,

        init() {
          this.loadCart();
          window.addEventListener('cart-updated', () => this.loadCart());
        },

        loadCart() {
          this.items = Cart.getItems();
          this.cartCount = Cart.getCount();
          this.total = Cart.getTotal();
        },

        updateQty(id, newQty) {
          if (newQty < 1) return;
          Cart.updateItem(id, { quantity: newQty });
          this.loadCart();
        },

        removeItem(id) {
          Cart.removeItem(id);
          this.loadCart();
        },

        clearCart() {
          if (confirm('Na pewno wyczyścić koszyk?')) {
            Cart.clear();
            this.items = [];
            this.cartCount = 0;
            this.total = 0;
          }
        },

        submitOrder() {
          if (!this.customer.name || !this.customer.email) {
            alert('Podaj imię i nazwisko oraz adres email.');
            return;
          }
          const order = {
            id: 'CC-' + Date.now(),
            date: new Date().toISOString(),
            items: this.items.map(i => ({
              id: i.id,
              productName: i.productName,
              summary: i.summary,
              price: i.price,
              quantity: i.quantity
            })),
            total: this.total,
            customer: { ...this.customer }
          };

          const orders = JSON.parse(localStorage.getItem('copycabana_orders') || '[]');
          orders.push(order);
          localStorage.setItem('copycabana_orders', JSON.stringify(orders));

          Cart.clear();
          this.items = [];
          this.cartCount = 0;
          this.total = 0;
          this.orderSubmitted = true;
        }
      };
    }
  </script>
</body>
</html>
