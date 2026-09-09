/* ============================================================
   CopyCabana — Concept JS (isolated experimental frontend)
   Loaded only on /concept/* pages.
   ============================================================ */
(function () {
  'use strict';

  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduceMotion) document.documentElement.classList.add('cc-reduced-motion');

  function fmtPL(n) {
    return Number(n).toLocaleString('pl-PL', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' zł';
  }

  /* --- Header scrolled state --- */
  var header = document.querySelector('.cc-header');
  function onScroll() {
    if (header) header.classList.toggle('is-scrolled', window.scrollY > 24);
  }
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  /* --- Scroll reveal (reuses .reveal / .visible pattern) --- */
  var reveals = document.querySelectorAll('.reveal');
  if (reveals.length) {
    if (reduceMotion || !('IntersectionObserver' in window)) {
      reveals.forEach(function (el) { el.classList.add('visible'); });
    } else {
      var revealObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add('visible');
            revealObserver.unobserve(entry.target);
          }
        });
      }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });
      reveals.forEach(function (el) { revealObserver.observe(el); });
    }
  }

  /* --- Homepage process section activation --- */
  var process = document.querySelector('.cc-process');
  if (process && 'IntersectionObserver' in window) {
    var processObserver = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        process.classList.toggle('is-in-view', entry.isIntersecting);
      });
    }, { threshold: 0.3 });
    processObserver.observe(process);
  }

  /* --- Marqee subtle drift is handled in CSS. --- */

  /* --- Alpine components (v3 registration hook) --- */
  document.addEventListener('alpine:init', function () {
    var Alpine = window.Alpine;
    if (!Alpine) return;

    /* Dropzone: empty / drag-over / uploading / analyzing / ready / invalid.
       Emits bubbling `cc-file` CustomEvent the parent configurator consumes. */
    Alpine.data('ccDropzone', function (key, opts) {
      opts = opts || {};
      var defaults = { pages: 84, colored: 12, invalid: false };
      var cfg = Object.assign(defaults, opts);

      return {
        dragging: false,
        name: null,
        status: 'empty',
        pages: 0,
        colored: 0,
        timers: [],

        destroy: function () {
          this.timers.forEach(function (t) { clearTimeout(t); });
          this.timers = [];
        },

        change: function (event) {
          var file = event.target.files && event.target.files[0];
          event.target.value = '';
          this.pick(file);
        },

        dragenter: function () { this.dragging = true; },
        dragleave: function () { this.dragging = false; },
        drop: function (event) {
          this.dragging = false;
          var file = event.dataTransfer && event.dataTransfer.files[0];
          this.pick(file);
        },

        pick: function (file) {
          if (!file) return;
          var ok = file.type === 'application/pdf' || /\.pdf$/i.test(file.name || '');
          if (!ok) {
            this.name = file.name || 'plik.pdf';
            this.status = 'invalid';
            this.emit();
            return;
          }
          this.name = file.name;
          this.start();
        },

        start: function () {
          var self = this;
          self.status = 'uploading';
          self.pages = 0;
          self.colored = 0;
          this.timers.forEach(function (t) { clearTimeout(t); });
          this.timers = [];
          this.timers.push(setTimeout(function () {
            self.status = 'analyzing';
            self.timers.push(setTimeout(function () {
              self.pages = cfg.pages;
              self.colored = cfg.colored;
              self.status = 'ready';
              self.emit();
            }, cfg.pages ? 1900 : 1500));
          }, 1400));
        },

        simulate: function () {
          this.name = cfg.pages ? 'praca-dyplomowa.pdf' : 'dokument.pdf';
          this.start();
        },

        emit: function () {
          this.$el.dispatchEvent(new CustomEvent('cc-file', {
            bubbles: true,
            detail: {
              key: key,
              name: this.name,
              status: this.status,
              pages: this.pages,
              colored: this.colored
            }
          }));
        }
      };
    });

    /* Quantity stepper: nice +/- for small integers. */
    Alpine.data('ccCounter', function (initial) {
      return {
        value: initial,
        min: 1,
        max: 10,
        inc: function () { if (this.value < this.max) this.value += 1; },
        dec: function () { if (this.value > this.min) this.value -= 1; }
      };
    });

    /* Homepage "Price + control" interactive example. Clearly example data. */
    Alpine.data('ccExample', function () {
      return {
        pages: 84,
        colored: 12,
        copies: 2,
        colorMode: 'mieszane',
        binding: 'twarda',
        bw: 0.2,
        color: 0.5,
        bindings: { 'miękka': 0, 'kanałowa': 25, 'twarda': 50 },
        setColor: function (mode) { this.colorMode = mode; },
        setBinding: function (mode) { this.binding = mode; },
        printTotal: function () {
          var per = this.colorMode === 'czarno-biały'
            ? this.pages * this.bw
            : this.colored * this.color + (this.pages - this.colored) * this.bw;
          return per * this.copies;
        },
        total: function () {
          return this.printTotal() + this.bindings[this.binding];
        },
        fmt: fmtPL
      };
    });

    /* Shared helpers used by configurators. */
    function ccScroll(sel) {
      var el = typeof sel === 'string' ? document.querySelector(sel) : sel;
      if (!el) return;
      try {
        if (reduceMotion) { el.scrollIntoView(); return; }
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
      } catch (e) {
        el.scrollIntoView();
      }
    }
    window.ccGo = ccScroll;

    function ccDateLbl(iso) {
      if (!iso) return 'wymaga wyboru';
      var d = new Date(iso + 'T00:00:00');
      if (isNaN(d.getTime())) return 'wymaga wyboru';
      return d.toLocaleDateString('pl-PL', { day: 'numeric', month: 'long' });
    }

    function ccDaysUntil(iso) {
      if (!iso) return null;
      var d = new Date(iso + 'T00:00:00');
      if (isNaN(d.getTime())) return null;
      var now = new Date();
      now.setHours(0, 0, 0, 0);
      return Math.round((d - now) / 86400000);
    }

    /* --- Thesis configurator --- */
    Alpine.data('ccConfigurator', function () {
      var bw = 0.2;
      var color = 0.5;
      return {
        file: { name: null, pages: 0, colored: 0 },
        print: { color: 'bw', sided: 'duplex', copies: 1 },
        binding: 'hard',
        cover: 'none',
        coverText: '',
        coverColor: 'granat',
        titleVariant: 0,
        coverColors: [
          { id: 'granat', hex: '#063A60' },
          { id: 'magenta', hex: '#D51A70' },
          { id: 'zolty', hex: '#FFED00' },
          { id: 'zielony', hex: '#7FBF45' },
          { id: 'niebieski', hex: '#00456F' }
        ],
        titleVariants: [
          { degree: 'Praca magisterska', title: 'Analiza rynku e-commerce w Polsce', author: 'Jan Kowalski' },
          { degree: 'Praca licencjacka', title: 'Projekt systemu informatycznego dla biblioteki', author: 'Anna Nowak' },
          { degree: 'Praca inżynierska', title: 'Wykorzystanie sztucznej inteligencji w logistyce', author: 'Michał Wiśniewski' }
        ],
        delivery: null,
        byDate: '',
        form: { name: '', email: '', phone: '', invoice: false, company: '', nip: '' },

        bindings: [
          { id: 'soft', name: 'Oprawa miękka', price: 0, hint: 'Klasyczna broszura. Częsty standard wydziałów.', thickness: 'soft' },
          { id: 'channel', name: 'Oprawa kanałowa', price: 25, hint: 'Klejony blok, równy grzbiet.', thickness: 'channel' },
          { id: 'hard', name: 'Oprawa twarda', price: 50, hint: 'Sztywna oprawa. Premium w obronie.', thickness: 'hard' }
        ],
        covers: [
          { id: 'none', name: 'Bez napisu', price: 0, hint: 'Czysta okładka.' },
          { id: 'standard', name: 'Standardowy napis', price: 15, hint: 'Tytuł pracy + imię i nazwisko.' },
          { id: 'custom', name: 'Własny napis', price: 10, hint: 'Wpisz dokładnie, co ma być na okładce.' }
        ],
        deliveries: [
          { id: 'pickup', name: 'Odbiór w Katowicach', price: 0, demo: false, hint: 'ul. Bankowa 11, 40-007 Katowice' },
          { id: 'parcel', name: 'Paczkomat', price: 12, demo: true, hint: 'demo — integracja do potwierdzenia' },
          { id: 'courier', name: 'Kurier', price: 18, demo: true, hint: 'demo — integracja do potwierdzenia' }
        ],

        go: ccScroll,

        onFile: function (detail) {
          if (!detail || detail.key !== 'thesis') return;
          this.file = { name: detail.name, pages: detail.pages, colored: detail.colored };
        },

        activeVariant: function () {
          return this.titleVariants[this.titleVariant] || this.titleVariants[0];
        },

        printTotal: function () {
          if (!this.file.pages) return 0;
          var pages = this.file.pages;
          var colored = this.file.colored;
          var rate = this.print.color === 'bw' ? bw : color;
          return (pages * rate) * this.print.copies;
        },
        bindingName: function () {
          var b = this.bindings.find(function (x) { return x.id === this.binding; });
          return b ? b.name : null;
        },
        coverName: function () {
          var c = this.covers.find(function (x) { return x.id === this.cover; });
          return c ? c.name : null;
        },
        bindingPrice: function () {
          var b = this.bindings.find(function (x) { return x.id === this.binding; });
          return b ? b.price : 0;
        },
        coverPrice: function () {
          var c = this.covers.find(function (x) { return x.id === this.cover; });
          return c ? c.price : 0;
        },
        deliveryPrice: function () {
          if (!this.delivery) return null;
          var d = this.deliveries.find(function (x) { return x.id === this.delivery; });
          return d ? d.price : 0;
        },
        deliveryName: function () {
          var d = this.deliveries.find(function (x) { return x.id === this.delivery; });
          return d ? d.name : null;
        },
        total: function () {
          return this.printTotal() + this.bindingPrice() + this.coverPrice() + (this.deliveryPrice() ?? 0);
        },
        dateLbl: function () { return ccDateLbl(this.byDate); },
        deliveryLate: function () {
          if (!this.delivery || this.delivery === 'pickup' || !this.byDate) return null;
          var days = ccDaysUntil(this.byDate);
          if (days === null) return null;
          return days < 3
            ? 'Przewidywane doręczenie może wypaść po wskazanej dacie. Rozważ odbiór osobisty.'
            : null;
        },
        fmt: fmtPL
      };
    });

    /* --- PDF configurator (simpler, no forced binding) --- */
    Alpine.data('ccPdfConfigurator', function () {
      var bw = 0.2;
      var color = 0.5;
      return {
        file: { name: null, pages: 0, colored: 0 },
        print: { color: 'bw', sided: 'duplex', copies: 1 },
        finish: 'none',
        delivery: null,
        byDate: '',
        form: { name: '', email: '', phone: '', invoice: false, company: '', nip: '' },

        finishes: [
          { id: 'none', name: 'Bez wykończenia', price: 0, hint: 'Wydruk + zwinięcie w rulon lub teczkę.' },
          { id: 'staples', name: 'Spinanie zeszytowe', price: 4, hint: 'Dwa zszywki wzdłuż grzbietu.' },
          { id: 'folder', name: 'Teczka', price: 8, hint: 'Gładka teczka zamykana na gumkę.' },
          { id: 'channel', name: 'Oprawa kanałowa', price: 18, hint: 'Klejony blok dla większych dokumentów.' }
        ],
        deliveries: [
          { id: 'pickup', name: 'Odbiór w Katowicach', price: 0, demo: false, hint: 'ul. Bankowa 11, 40-007 Katowice' },
          { id: 'parcel', name: 'Paczkomat', price: 12, demo: true, hint: 'demo — integracja do potwierdzenia' },
          { id: 'courier', name: 'Kurier', price: 18, demo: true, hint: 'demo — integracja do potwierdzenia' }
        ],

        go: ccScroll,

        onFile: function (detail) {
          if (!detail || detail.key !== 'pdf') return;
          this.file = { name: detail.name, pages: detail.pages, colored: detail.colored };
        },

        printTotal: function () {
          if (!this.file.pages) return 0;
          var rate = this.print.color === 'bw' ? bw : color;
          return (this.file.pages * rate) * this.print.copies;
        },
        finishPrice: function () {
          var f = this.finishes.find(function (x) { return x.id === this.finish; }.bind(this));
          return f ? f.price : 0;
        },
        deliveryPrice: function () {
          if (!this.delivery) return null;
          var d = this.deliveries.find(function (x) { return x.id === this.delivery; });
          return d ? d.price : 0;
        },
        total: function () {
          return this.printTotal() + this.finishPrice() + (this.deliveryPrice() ?? 0);
        },
        dateLbl: function () { return ccDateLbl(this.byDate); },
        deliveryLate: function () {
          if (!this.delivery || this.delivery === 'pickup' || !this.byDate) return null;
          var days = ccDaysUntil(this.byDate);
          if (days === null) return null;
          return days < 3
            ? 'Przewidywane doręczenie może wypaść po wskazanej dacie. Rozważ odbiór osobisty.'
            : null;
        },
        fmt: fmtPL
      };
    });

    /* --- B2B configurator (dynamic products, independent items) --- */
    var B2B_PRODUCTS = [
      {
        id: 'visits', name: 'Wizytówki', icon: 'fa-id-card',
        desc: '90×50 mm. Papier, wykończenie i zadruk.',
        params: [
          { key: 'qty', label: 'Nakład', type: 'chips', options: ['100', '250', '500', '1000', '2000'] },
          { key: 'format', label: 'Format', type: 'chips', options: ['90×50 mm', '54×85 mm'] },
          { key: 'printing', label: 'Zadruk', type: 'chips', options: ['jednostronnie', 'dwustronnie'] },
          { key: 'paper', label: 'Papier', type: 'chips', options: ['300 g jedwab', '350 g biały', '350 g tworzywo'] },
          { key: 'finish', label: 'Wykończenie', type: 'chips', options: ['bez', 'lakier UV', 'folia matowa'] },
          { key: 'designs', label: 'Liczba projektów', type: 'number', min: 1, max: 10 }
        ]
      },
      {
        id: 'leaflets', name: 'Ulotki', icon: 'fa-folder-open',
        desc: 'Ulotki A6–A4 z opcją składania.',
        params: [
          { key: 'qty', label: 'Nakład', type: 'chips', options: ['250', '500', '1000', '2500', '5000'] },
          { key: 'format', label: 'Format', type: 'chips', options: ['A6', 'A5', 'A4'] },
          { key: 'paper', label: 'Papier', type: 'chips', options: ['170 g', '250 g kreda', '350 g'] },
          { key: 'printing', label: 'Zadruk', type: 'chips', options: ['1/1 czarno-biały', '4/4 kolor'] },
          { key: 'fold', label: 'Składanie', type: 'chips', options: ['bez składania', 'pół na pół', 'łamane na 3'] }
        ]
      },
      {
        id: 'posters', name: 'Plakaty', icon: 'fa-image',
        desc: 'Od A3 do B2, papier lub karton.',
        params: [
          { key: 'qty', label: 'Liczba sztuk', type: 'chips', options: ['1', '10', '25', '50', '100'] },
          { key: 'format', label: 'Format', type: 'chips', options: ['A3', 'A2', 'A1', 'B2'] },
          { key: 'substrate', label: 'Podłoże', type: 'chips', options: ['papier 170 g', 'papier 250 g', 'karton 300 g'] }
        ]
      },
      {
        id: 'banners', name: 'Banery', icon: 'fa-flag',
        desc: 'Wg wymiarów, z wykończeniem pod montaż.',
        params: [
          { key: 'width', label: 'Szerokość (cm)', type: 'number', min: 50, max: 500 },
          { key: 'height', label: 'Wysokość (cm)', type: 'number', min: 50, max: 300 },
          { key: 'material', label: 'Materiał', type: 'chips', options: ['siateczka', 'baner 510 g', 'baner 440 g'] },
          { key: 'finish', label: 'Wykończenie', type: 'chips', options: ['szwy z oczkami', 'taśma 4 cm', 'bez wykończenia'] },
          { key: 'qty', label: 'Liczba sztuk', type: 'chips', options: ['1', '2', '5', '10'] }
        ]
      },
      {
        id: 'rollups', name: 'Rollupy', icon: 'fa-user-tie',
        desc: '85×200 i 100×200 cm.',
        params: [
          { key: 'size', label: 'Rozmiar', type: 'chips', options: ['85×200 cm', '100×200 cm'] },
          { key: 'package', label: 'Zakres', type: 'chips', options: ['konstrukcja + grafika', 'sama grafika'] },
          { key: 'qty', label: 'Liczba sztuk', type: 'chips', options: ['1', '2', '5'] }
        ]
      },
      {
        id: 'documents', name: 'Dokumenty PDF', icon: 'fa-file-lines',
        desc: 'Materiały szkoleniowe i dokumenty.',
        params: [
          { key: 'color', label: 'Kolor', type: 'chips', options: ['czarno-biały', 'kolor'] },
          { key: 'sided', label: 'Strony kartki', type: 'chips', options: ['jednostronnie', 'dwustronnie'] },
          { key: 'qty', label: 'Egzemplarze', type: 'number', min: 1, max: 50 },
          { key: 'finish', label: 'Wykończenie', type: 'chips', options: ['bez', 'spinanie', 'oprawa kanałowa', 'teczka'] }
        ]
      }
    ];

    Alpine.data('ccB2bConfigurator', function () {
      return {
        products: B2B_PRODUCTS,

        selected: null,
        params: {},
        file: { name: null, status: 'empty' },
        helpWanted: false,
        items: [],

        briefOpen: false,
        brief: { type: '', desc: '', qty: '', date: '' },
        briefSent: false,

        delivery: null,
        byDate: '',
        company: { person: '', name: '', email: '', phone: '', invoice: false, nip: '' },

        deliveries: [
          { id: 'pickup', name: 'Odbiór w Katowicach', price: 0, demo: false, hint: 'ul. Bankowa 11, 40-007 Katowice' },
          { id: 'parcel', name: 'Paczkomat', price: 12, demo: true, hint: 'demo — integracja do potwierdzenia' },
          { id: 'courier', name: 'Kurier', price: 18, demo: true, hint: 'demo — integracja do potwierdzenia' }
        ],

        go: ccScroll,

        selectedProduct: function () {
          return this.products.find(function (p) { return p.id === this.selected; }.bind(this));
        },

        selectProduct: function (id) {
          this.selected = id;
          this.helpWanted = false;
          this.file = { name: null, status: 'empty' };
          var ds = this.selectedProduct();
          this.params = {};
          if (ds) {
            ds.params.forEach(function (p) {
              var def = p.type === 'chips' ? p.options[0] : String(p.min);
              this.params[p.key] = def;
            }.bind(this));
          }
        },

        setParam: function (key, value) { this.params[key] = value; },
        isParam: function (key, value) { return this.params[key] === value; },

        onFile: function (detail) {
          if (!detail || !this.selected) return;
          if (detail.key !== 'b2b-' + this.selected) return;
          this.file = { name: detail.name, status: detail.status };
        },

        commitItem: function () {
          var p = this.selectedProduct();
          if (!p) return;
          var snap = Object.assign({}, this.params);
          this.items.push({
            id: 'item-' + Date.now() + '-' + Math.random().toString(36).slice(2, 7),
            product: p.id,
            name: p.name,
            icon: p.icon,
            params: snap,
            file: this.file.name,
            designs: Number(snap.designs || snap.qty || 1),
            helpWanted: this.helpWanted
          });
          this.selected = null;
          this.params = {};
          this.file = { name: null, status: 'empty' };
          this.helpWanted = false;
        },

        removeItem: function (index) {
          this.items.splice(index, 1);
        },

        itemSummary: function (item) {
          var parts = [];
          var p = this.products.find(function (x) { return x.id === item.product; });
          if (!p) return parts;
          p.params.forEach(function (param) {
            var v = item.params[param.key];
            if (v !== undefined && v !== null && v !== '') {
              parts.push(param.label + ': ' + v);
            }
          });
          return parts.join(' · ');
        },

        resetDraft: function () {
          this.selected = null;
          this.params = {};
          this.file = { name: null, status: 'empty' };
          this.helpWanted = false;
        },

        toggleBrief: function () {
          this.briefOpen = !this.briefOpen;
          this.briefSent = false;
        },

        sendBrief: function () {
          this.briefSent = true;
        },

        dateLbl: function () { return ccDateLbl(this.byDate); },
        deliveryName: function () {
          var d = this.deliveries.find(function (x) { return x.id === this.delivery; });
          return d ? d.name : null;
        },
        deliveryLate: function () {
          if (!this.delivery || this.delivery === 'pickup' || !this.byDate) return null;
          var days = ccDaysUntil(this.byDate);
          if (days === null) return null;
          return days < 3
            ? 'Przewidywane doręczenie może wypaść po wskazanej dacie. Rozważ odbiór osobisty.'
            : null;
        }
      };
    });
  });
})();
