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

   /* Dropzone: upload a PDF and emit its server-side analysis. */
    Alpine.data('ccDropzone', function (key, opts) {
      opts = opts || {};

      return {
        dragging: false,
        name: null,
        status: 'empty',
        pages: 0,
        colored: 0,
        uploadToken: null,
        message: null,

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
          var allowedExtensions = opts.extensions || ['pdf'];
          var extension = String(file.name || '').split('.').pop().toLowerCase();
          var ok = allowedExtensions.indexOf(extension) !== -1;
          if (!ok) {
            this.name = file.name || 'plik.pdf';
            this.status = 'invalid';
            this.message = opts.invalidMessage || 'Wybierz obsługiwany format pliku.';
            this.emit();
            return;
          }
          this.name = file.name;
          this.upload(file);
        },

        upload: function (file) {
          var self = this;
          self.status = 'uploading';
          self.pages = 0;
          self.colored = 0;
          self.uploadToken = null;
          self.message = null;
          var formData = new FormData();
          formData.append('file', file);

          fetch(opts.endpoint || '/api/v1/uploads', {
            method: 'POST',
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
            body: formData
          }).then(function (response) {
            return response.json().then(function (payload) {
              if (!response.ok) throw new Error(payload.message || payload.errors?.file?.[0] || 'Nie udało się wysłać pliku.');
              return payload;
            });
          }).then(function (payload) {
            self.uploadToken = payload.upload_token;
            self.pages = payload.file.pages;
            self.colored = 0;
            self.status = 'ready';
            self.emit();
          }).catch(function (error) {
            self.status = 'invalid';
            self.message = error.message;
            self.emit();
          });
        },

        simulate: function () {
          this.name = opts.pages ? 'praca-dyplomowa.pdf' : 'dokument.pdf';
          this.pages = opts.pages || 0;
          this.colored = opts.colored || 0;
          this.status = 'ready';
          this.emit();
        },

        emit: function () {
          this.$el.dispatchEvent(new CustomEvent('cc-file', {
            bubbles: true,
            detail: {
              key: key,
              name: this.name,
              status: this.status,
              uploadToken: this.uploadToken,
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

    var CC_INPOST_POINTS_ENDPOINT = '/api/v1/inpost/points';

    function ccSearchInpostPoints(searchTerm) {
      var term = String(searchTerm || '').trim();
      var compactTerm = term.replace(/\s+/g, '');
      var isPostCode = /^\d{2}-?\d{3}$/.test(compactTerm);
      var parameter = isPostCode ? 'post_code' : 'city';
      var value = isPostCode
        ? compactTerm.slice(0, 2) + '-' + compactTerm.slice(2)
        : term;

      return fetch(CC_INPOST_POINTS_ENDPOINT + '?' + parameter + '=' + encodeURIComponent(value), {
        headers: { Accept: 'application/json' },
        credentials: 'same-origin'
      }).then(function (response) {
        if (!response.ok) throw new Error('InPost points request failed');
        return response.json();
      }).then(function (payload) {
        return Array.isArray(payload.points) ? payload.points : [];
      });
    }

    function ccInpostState() {
      return {
        parcelLocker: null,
        lockerSearch: '',
        lockers: [],
        lockerLoading: false,
        lockerError: null,
        lockerRequest: 0,

        init: function () {
          var self = this;
          this.$watch('delivery', function (value) {
            if (value !== 'parcel') self.resetLocker();
          });
        },

        resetLocker: function () {
          this.parcelLocker = null;
          this.lockerSearch = '';
          this.lockers = [];
          this.lockerLoading = false;
          this.lockerError = null;
          this.lockerRequest += 1;
        },

        searchLockers: function () {
          var term = String(this.lockerSearch || '').trim();
          if (term.length < 3) {
            this.lockers = [];
            this.parcelLocker = null;
            this.lockerError = 'Wpisz co najmniej 3 znaki: kod pocztowy albo miasto.';
            return;
          }

          var self = this;
          var requestId = ++this.lockerRequest;
          this.parcelLocker = null;
          this.lockers = [];
          this.lockerLoading = true;
          this.lockerError = null;

          ccSearchInpostPoints(term).then(function (points) {
            if (requestId !== self.lockerRequest) return;
            self.lockers = points;
            if (!points.length) self.lockerError = 'Nie znaleziono paczkomatów dla podanej lokalizacji.';
          }).catch(function () {
            if (requestId === self.lockerRequest) {
              self.lockerError = 'Nie udało się pobrać listy paczkomatów. Spróbuj ponownie.';
            }
          }).finally(function () {
            if (requestId === self.lockerRequest) self.lockerLoading = false;
          });
        },

        pickLocker: function (point) {
          this.parcelLocker = point;
          this.lockerError = null;
        },

        lockerSummary: function () {
          if (!this.parcelLocker) return '';
          return this.parcelLocker.name + ' · ' + this.parcelLocker.address;
        }
      };
    }

    /* --- Thesis configurator --- */
    Alpine.data('ccConfigurator', function () {
      var pricing = window.copyCabanaThesisPricing || {};
      var pagePrices = pricing.page_prices || {};
      var bindingPrices = pricing.bindings || {};
      var coverPrices = pricing.covers || {};
      var deliveryPrices = pricing.shipping || {};
      var bw = Number(pagePrices.bw ?? 0.2);
      var color = Number(pagePrices.color ?? 0.5);
      return Object.assign(ccInpostState(), {
        file: { name: null, pages: 0, colored: 0, uploadToken: null },
        quote: null,
        quoteLoading: false,
        quoteError: null,
        quoteRequest: 0,
        quoteTimer: null,
        submitting: false,
        orderError: null,
        idempotencyKey: null,
        privacyAccepted: false,
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
          { id: 'soft', name: 'Oprawa miękka', price: Number(bindingPrices.soft?.price ?? 0), hint: 'Klasyczna broszura. Częsty standard wydziałów.', thickness: 'soft' },
          { id: 'channel', name: 'Oprawa kanałowa', price: Number(bindingPrices.channel?.price ?? 25), hint: 'Klejony blok, równy grzbiet.', thickness: 'channel' },
          { id: 'hard', name: 'Oprawa twarda', price: Number(bindingPrices.hard?.price ?? 50), hint: 'Sztywna oprawa. Premium w obronie.', thickness: 'hard' }
        ],
        covers: [
          { id: 'none', name: 'Bez napisu', price: Number(coverPrices.none?.price ?? 0), hint: 'Czysta okładka.' },
          { id: 'standard', name: 'Standardowy napis', price: Number(coverPrices.standard?.price ?? 15), hint: 'Tytuł pracy + imię i nazwisko.' },
          { id: 'custom', name: 'Własny napis', price: Number(coverPrices.custom?.price ?? 10), hint: 'Wpisz dokładnie, co ma być na okładce.' }
        ],
        deliveries: [
          { id: 'pickup', name: 'Odbiór w Katowicach', price: Number(deliveryPrices.pickup ?? 0), hint: 'ul. Bankowa 11, 40-007 Katowice' },
          { id: 'parcel', name: 'Paczkomat', price: Number(deliveryPrices.parcel ?? 12), hint: 'Wybierz punkt z listy InPost' },
        ],

         init: function () {
           var self = this;
           this.idempotencyKey = window.crypto?.randomUUID?.() || String(Date.now()) + Math.random();
           ['file.uploadToken', 'print.color', 'print.sided', 'binding', 'cover', 'coverText', 'coverColor', 'print.copies', 'delivery', 'parcelLocker'].forEach(function (path) {
             self.$watch(path, function () { self.scheduleQuote(); });
           });
         },

         scheduleQuote: function () {
           var self = this;
           clearTimeout(this.quoteTimer);
           this.quoteTimer = setTimeout(function () { self.refreshQuote(); }, 250);
         },

         quotePayload: function () {
           return {
             upload_token: this.file.uploadToken,
             color_mode: this.print.color,
             sided: this.print.sided,
             binding: this.binding,
             cover: this.cover,
             cover_text: this.coverText,
             cover_color: this.coverColor,
              copies: this.print.copies,
              shipping_method: this.delivery,
              requested_by_date: this.byDate || null
           };
         },

         refreshQuote: function () {
           if (!this.file.uploadToken || !this.delivery) return;
           var self = this;
           var requestId = ++this.quoteRequest;
           this.quoteLoading = true;
           this.quoteError = null;

           return fetch('/api/v1/thesis/quote', {
             method: 'POST',
             headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
             credentials: 'same-origin',
             body: JSON.stringify(this.quotePayload())
           }).then(function (response) {
             return response.json().then(function (payload) {
               if (!response.ok) throw new Error(payload.message || Object.values(payload.errors || {}).flat()[0] || 'Nie udało się wyliczyć ceny.');
               return payload;
             });
           }).then(function (payload) {
             if (requestId === self.quoteRequest) self.quote = payload.quote;
           }).catch(function (error) {
             if (requestId === self.quoteRequest) {
               self.quote = null;
               self.quoteError = error.message;
             }
           }).finally(function () {
             if (requestId === self.quoteRequest) self.quoteLoading = false;
           });
         },

        quoteValue: function (key, fallback) {
          return this.quote && this.quote[key] !== undefined ? this.quote[key] : fallback;
        },

        shippingAddress: function () {
          if (this.delivery !== 'parcel' || !this.parcelLocker) return null;

          return {
            point_code: this.parcelLocker.name,
            name: this.parcelLocker.name,
            address: this.parcelLocker.address,
            city: this.parcelLocker.city,
            post_code: this.parcelLocker.post_code
          };
        },

        async submitOrder() {
          if (!this.file.uploadToken) {
            this.orderError = 'Najpierw dodaj plik PDF.';
            return;
          }
          if (!this.delivery || (this.delivery === 'parcel' && !this.parcelLocker)) {
            this.orderError = 'Wybierz sposób odbioru i paczkomat, jeśli jest potrzebny.';
            return;
          }
          if (!this.form.name || !this.form.email || !this.privacyAccepted) {
            this.orderError = 'Podaj dane kontaktowe i zaakceptuj politykę prywatności.';
            return;
          }

          this.orderError = null;
          this.submitting = true;

          try {
            await this.refreshQuote();
            if (!this.quote) throw new Error('Nie udało się potwierdzić ceny. Spróbuj ponownie.');

            var response = await fetch('/api/v1/thesis/orders', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'Idempotency-Key': this.idempotencyKey
              },
              credentials: 'same-origin',
              body: JSON.stringify(Object.assign(this.quotePayload(), {
                customer: {
                  name: this.form.name,
                  email: this.form.email,
                  phone: this.form.phone,
                  company: this.form.company,
                  nip: this.form.nip
                },
                shipping_address: this.shippingAddress(),
                requested_by_date: this.byDate || null,
                invoice_required: this.form.invoice,
                marketing_consent: false,
                privacy_policy_accepted: this.privacyAccepted
              }))
            });
            var payload = await response.json();
            if (!response.ok) throw new Error(payload.message || Object.values(payload.errors || {}).flat()[0] || 'Nie udało się utworzyć zamówienia.');
            window.location.assign(payload.payment_url);
          } catch (error) {
            this.orderError = error.message;
          } finally {
            this.submitting = false;
          }
        },

        go: ccScroll,

        onFile: function (detail) {
          if (!detail || detail.key !== 'thesis') return;
          this.file = { name: detail.name, pages: detail.pages, colored: 0, uploadToken: detail.uploadToken };
          this.print.color = 'bw';
        },

        activeVariant: function () {
          return this.titleVariants[this.titleVariant] || this.titleVariants[0];
        },

        printTotal: function () {
          if (!this.file.pages) return 0;
          var pages = this.file.pages;
          var total = this.print.color === 'bw' ? pages * bw : pages * color;
          return total * this.print.copies;
        },
        printColorName: function () {
          return this.print.color === 'bw'
            ? 'całość czarno-biała'
            : 'całość kolorowa';
        },
        bindingName: function () {
          var b = this.bindings.find(function (x) { return x.id === this.binding; }.bind(this));
          return b ? b.name : null;
        },
        coverName: function () {
          var c = this.covers.find(function (x) { return x.id === this.cover; }.bind(this));
          return c ? c.name : null;
        },
        bindingPrice: function () {
          var b = this.bindings.find(function (x) { return x.id === this.binding; }.bind(this));
          return b ? b.price : 0;
        },
        coverPrice: function () {
          var c = this.covers.find(function (x) { return x.id === this.cover; }.bind(this));
          return c ? c.price : 0;
        },
        deliveryPrice: function () {
          if (!this.delivery) return null;
          var d = this.deliveries.find(function (x) { return x.id === this.delivery; }.bind(this));
          return d ? d.price : 0;
        },
        deliveryName: function () {
          var d = this.deliveries.find(function (x) { return x.id === this.delivery; }.bind(this));
          return d ? d.name : null;
        },
         total: function () {
           return this.printTotal() + ((this.bindingPrice() + this.coverPrice()) * this.print.copies) + (this.deliveryPrice() ?? 0);
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
       });
     });

     /* --- PDF configurator backed by the real upload, quote, and checkout flow. --- */
     Alpine.data('ccPdfConfigurator', function () {
       var pricing = window.copyCabanaPdfPricing || {};
       var pagePrices = pricing.page_prices || {};
       var finishes = pricing.finishes || {};
       var deliveryPrices = window.copyCabanaShipping || {};
       var bw = Number(pagePrices.bw ?? 0.2);
       var color = Number(pagePrices.color ?? 0.5);

       return Object.assign(ccInpostState(), {
         file: { name: null, pages: 0, colored: 0, uploadToken: null },
         quote: null,
         quoteLoading: false,
         quoteError: null,
         quoteRequest: 0,
         quoteTimer: null,
         submitting: false,
         orderError: null,
         idempotencyKey: null,
         privacyAccepted: false,
         print: { color: 'bw', sided: 'duplex', copies: 1 },
         finish: 'none',
         delivery: null,
         byDate: '',
         courierAddress: { address: '', city: '', post_code: '' },
         form: { name: '', email: '', phone: '', invoice: false, company: '', nip: '' },

         finishes: Object.keys(finishes).map(function (id) {
           return { id: id, name: finishes[id].label, price: Number(finishes[id].price || 0), hint: '' };
         }),
         deliveries: [
           { id: 'pickup', name: 'Odbiór w Katowicach', price: Number(deliveryPrices.pickup || 0), hint: 'ul. Bankowa 11, 40-007 Katowice' },
           { id: 'parcel', name: 'Paczkomat', price: Number(deliveryPrices.parcel || 0), hint: 'Wybierz punkt z listy InPost' },
           { id: 'courier', name: 'Kurier', price: Number(deliveryPrices.courier || 0), hint: 'Dostawa pod wskazany adres' }
         ],

         init: function () {
           var self = this;
           this.idempotencyKey = window.crypto?.randomUUID?.() || String(Date.now()) + Math.random();
           ['file.uploadToken', 'print.color', 'print.sided', 'print.copies', 'finish', 'delivery', 'parcelLocker', 'byDate'].forEach(function (path) {
             self.$watch(path, function () { self.scheduleQuote(); });
           });
           this.$watch('delivery', function (value) {
             if (value !== 'parcel') self.resetLocker();
           });
         },

         scheduleQuote: function () {
           var self = this;
           clearTimeout(this.quoteTimer);
           this.quoteTimer = setTimeout(function () { self.refreshQuote(); }, 250);
         },

         quotePayload: function () {
           return {
             upload_token: this.file.uploadToken,
             color_mode: this.print.color,
             sided: this.print.sided,
             finish: this.finish,
             copies: this.print.copies,
             shipping_method: this.delivery
           };
         },

         refreshQuote: function () {
           if (!this.file.uploadToken || !this.delivery) return;
           var self = this;
           var requestId = ++this.quoteRequest;
           this.quoteLoading = true;
           this.quoteError = null;

           return fetch('/api/v1/pdf/quote', {
             method: 'POST',
             headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
             credentials: 'same-origin',
             body: JSON.stringify(this.quotePayload())
           }).then(function (response) {
             return response.json().then(function (payload) {
               if (!response.ok) throw new Error(payload.message || Object.values(payload.errors || {}).flat()[0] || 'Nie udało się wyliczyć ceny.');
               return payload;
             });
           }).then(function (payload) {
             if (requestId === self.quoteRequest) self.quote = payload.quote;
           }).catch(function (error) {
             if (requestId === self.quoteRequest) {
               self.quote = null;
               self.quoteError = error.message;
             }
           }).finally(function () {
             if (requestId === self.quoteRequest) self.quoteLoading = false;
           });
         },

         shippingAddress: function () {
           if (this.delivery === 'parcel' && this.parcelLocker) {
             return { point_code: this.parcelLocker.name, name: this.parcelLocker.name, address: this.parcelLocker.address, city: this.parcelLocker.city, post_code: this.parcelLocker.post_code };
           }
           return this.delivery === 'courier' ? this.courierAddress : null;
         },

         submitOrder: async function () {
           if (!this.file.uploadToken) { this.orderError = 'Najpierw dodaj plik PDF.'; return; }
           if (!this.delivery || (this.delivery === 'parcel' && !this.parcelLocker)) { this.orderError = 'Wybierz sposób odbioru i paczkomat, jeśli jest potrzebny.'; return; }
           if (this.delivery === 'courier' && (!this.courierAddress.address || !this.courierAddress.city || !this.courierAddress.post_code)) { this.orderError = 'Uzupełnij adres dostawy kurierskiej.'; return; }
           if (!this.form.name || !this.form.email || !this.privacyAccepted) { this.orderError = 'Podaj dane kontaktowe i zaakceptuj politykę prywatności.'; return; }

           this.orderError = null;
           this.submitting = true;
           try {
             await this.refreshQuote();
             if (!this.quote) throw new Error('Nie udało się potwierdzić ceny. Spróbuj ponownie.');
             var response = await fetch('/api/v1/pdf/orders', {
               method: 'POST',
               headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'Idempotency-Key': this.idempotencyKey },
               credentials: 'same-origin',
               body: JSON.stringify({
                 upload_token: this.file.uploadToken,
                 color_mode: this.print.color,
                 sided: this.print.sided,
                 finish: this.finish,
                 copies: this.print.copies,
                 shipping_method: this.delivery,
                 shipping_address: this.shippingAddress(),
                 requested_by_date: this.byDate || null,
                 invoice_required: this.form.invoice,
                 privacy_policy_accepted: this.privacyAccepted,
                 customer: { name: this.form.name, email: this.form.email, phone: this.form.phone, company: this.form.company, nip: this.form.nip }
               })
             });
             var payload = await response.json();
             if (!response.ok) throw new Error(payload.message || Object.values(payload.errors || {}).flat()[0] || 'Nie udało się utworzyć zamówienia.');
             window.location.assign(payload.payment_url);
           } catch (error) {
             this.orderError = error.message;
           } finally {
             this.submitting = false;
           }
         },

         go: ccScroll,
         onFile: function (detail) {
           if (!detail || detail.key !== 'pdf') return;
           this.file = { name: detail.name, pages: detail.pages, colored: detail.colored, uploadToken: detail.uploadToken };
         },
         printTotal: function () { return this.file.pages ? this.file.pages * (this.print.color === 'bw' ? bw : color) * this.print.copies : 0; },
         finishPrice: function () { var f = this.finishes.find(function (x) { return x.id === this.finish; }.bind(this)); return f ? f.price * this.print.copies : 0; },
         deliveryPrice: function () { if (!this.delivery) return null; var d = this.deliveries.find(function (x) { return x.id === this.delivery; }.bind(this)); return d ? d.price : 0; },
         total: function () { return this.quote ? Number(this.quote.total) : this.printTotal() + this.finishPrice() + (this.deliveryPrice() ?? 0); },
         dateLbl: function () { return ccDateLbl(this.byDate); },
         deliveryLate: function () {
           if (!this.delivery || this.delivery === 'pickup' || !this.byDate) return null;
           var days = ccDaysUntil(this.byDate);
           return days !== null && days < 3 ? 'Przewidywane doręczenie może wypaść po wskazanej dacie. Rozważ odbiór osobisty.' : null;
         },
         fmt: fmtPL
       });
     });

    /* --- B2B configurator (dynamic products, independent items) --- */
    var B2B_PRODUCT_IMAGES = window.copyCabanaB2bImages || {};
    var B2B_GENERIC_PARAMS = [
      { key: 'qty', label: 'Liczba sztuk', type: 'number', min: 1, max: 1000 }
    ];
    var B2B_PRODUCTS = [
      {
        id: 'visits', name: 'Wizytówki', icon: 'fa-id-card', image: B2B_PRODUCT_IMAGES.visits || null,
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
        id: 'leaflets', name: 'Ulotki', icon: 'fa-folder-open', image: B2B_PRODUCT_IMAGES.leaflets || null,
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
        id: 'posters', name: 'Plakaty', icon: 'fa-image', image: B2B_PRODUCT_IMAGES.posters || null,
        desc: 'Od A3 do B2, papier lub karton.',
        params: [
          { key: 'qty', label: 'Liczba sztuk', type: 'chips', options: ['1', '10', '25', '50', '100'] },
          { key: 'format', label: 'Format', type: 'chips', options: ['A3', 'A2', 'A1', 'B2'] },
          { key: 'substrate', label: 'Podłoże', type: 'chips', options: ['papier 170 g', 'papier 250 g', 'karton 300 g'] }
        ]
      },
      {
        id: 'banners', name: 'Banery', icon: 'fa-flag', image: B2B_PRODUCT_IMAGES.banners || null,
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
        id: 'rollups', name: 'Rollupy', icon: 'fa-user-tie', image: B2B_PRODUCT_IMAGES.rollups || null,
        desc: '85×200 i 100×200 cm.',
        params: [
          { key: 'size', label: 'Rozmiar', type: 'chips', options: ['85×200 cm', '100×200 cm'] },
          { key: 'package', label: 'Zakres', type: 'chips', options: ['konstrukcja + grafika', 'sama grafika'] },
          { key: 'qty', label: 'Liczba sztuk', type: 'chips', options: ['1', '2', '5'] }
        ]
      },
      {
        id: 'documents', name: 'Dokumenty PDF', icon: 'fa-file-lines', image: B2B_PRODUCT_IMAGES.documents || null,
        desc: 'Materiały szkoleniowe i dokumenty.',
        params: [
          { key: 'color', label: 'Kolor', type: 'chips', options: ['czarno-biały', 'kolor'] },
          { key: 'sided', label: 'Strony kartki', type: 'chips', options: ['jednostronnie', 'dwustronnie'] },
          { key: 'qty', label: 'Egzemplarze', type: 'number', min: 1, max: 50 },
          { key: 'finish', label: 'Wykończenie', type: 'chips', options: ['bez', 'spinanie', 'oprawa kanałowa', 'teczka'] }
        ]
      },
      {
        id: 'billboards', name: 'Billboardy', icon: 'fa-rectangle-ad', image: B2B_PRODUCT_IMAGES.billboards || null,
        desc: 'Reklama zewnętrzna w dużym formacie na Śląsku.', params: B2B_GENERIC_PARAMS
      },
      {
        id: 'canvases', name: 'Fotoobrazy', icon: 'fa-image', image: B2B_PRODUCT_IMAGES.canvases || null,
        desc: 'Fotoobrazy na płótnie i w ramach.', params: B2B_GENERIC_PARAMS
      },
      {
        id: 'wallpapers', name: 'Fototapety', icon: 'fa-expand', image: B2B_PRODUCT_IMAGES.wallpapers || null,
        desc: 'Fototapety na wymiar do wnętrz i biur.', params: B2B_GENERIC_PARAMS
      },
      {
        id: 'calendars', name: 'Kalendarze spiralowane', icon: 'fa-calendar-days', image: B2B_PRODUCT_IMAGES.calendars || null,
        desc: 'Kalendarze ścienne i biurkowe z indywidualnym projektem.', params: B2B_GENERIC_PARAMS
      },
      {
        id: 'stickers', name: 'Naklejki', icon: 'fa-note-sticky', image: B2B_PRODUCT_IMAGES.stickers || null,
        desc: 'Naklejki w dowolnych kształtach i rozmiarach.', params: B2B_GENERIC_PARAMS
      },
      {
        id: 'plaques', name: 'Tabliczki grawerowane', icon: 'fa-sign', image: B2B_PRODUCT_IMAGES.plaques || null,
        desc: 'Tabliczki informacyjne i grawerowane laserowo.', params: B2B_GENERIC_PARAMS
      },
      {
        id: 'cad', name: 'Rysunki, plany, mapy', icon: 'fa-compass-drafting', image: B2B_PRODUCT_IMAGES.cad || null,
        desc: 'Wydruki CAD w formatach A0–A4.', params: B2B_GENERIC_PARAMS
      },
      {
        id: 'copies', name: 'Ksero', icon: 'fa-copy', image: B2B_PRODUCT_IMAGES.copies || null,
        desc: 'Kserokopie w czerni-bieli i kolorze.', params: B2B_GENERIC_PARAMS
      },
      {
        id: 'scans', name: 'Skanowanie', icon: 'fa-file-arrow-up', image: B2B_PRODUCT_IMAGES.scans || null,
        desc: 'Skanowanie dokumentów i zdjęć w wysokiej rozdzielczości.', params: B2B_GENERIC_PARAMS
      },
      {
        id: 'id-photos', name: 'Zdjęcia do dokumentów', icon: 'fa-id-card', image: B2B_PRODUCT_IMAGES['id-photos'] || null,
        desc: 'Fotoset do paszportu, dowodu i wizy.', params: B2B_GENERIC_PARAMS
      },
      {
        id: 'stamps', name: 'Pieczątki', icon: 'fa-stamp', image: B2B_PRODUCT_IMAGES.stamps || null,
        desc: 'Pieczątki, stemple i datowniki.', params: B2B_GENERIC_PARAMS
      },
      {
        id: 'design', name: 'Projektowanie graficzne', icon: 'fa-pen-ruler', image: B2B_PRODUCT_IMAGES.design || null,
        desc: 'Projekty graficzne materiałów reklamowych.', params: B2B_GENERIC_PARAMS
      },
      {
        id: 'extras', name: 'Usługi dodatkowe', icon: 'fa-layer-group', image: B2B_PRODUCT_IMAGES.extras || null,
        desc: 'Laminowanie, oprawianie i personalizacja.', params: B2B_GENERIC_PARAMS
      },
      {
        id: 'binding', name: 'Oprawa prac i bindowanie', icon: 'fa-book-open', image: B2B_PRODUCT_IMAGES.binding || null,
        desc: 'Oprawa twarda i miękka prac dyplomowych w 24h.', params: B2B_GENERIC_PARAMS
      }
    ];

    Alpine.data('ccB2bConfigurator', function () {
      return Object.assign(ccInpostState(), {
        products: B2B_PRODUCTS,

        selected: null,
        params: {},
         file: { name: null, status: 'empty', uploadToken: null },
         helpWanted: false,
         items: [],

        briefOpen: false,
        brief: { type: '', desc: '', qty: '', date: '' },
        briefSent: false,

         delivery: null,
         byDate: '',
         courierAddress: { address: '', city: '', post_code: '' },
         company: { person: '', name: '', email: '', phone: '', invoice: false, nip: '' },
         privacyAccepted: false,
         submitting: false,
         submitted: false,
         submitError: null,

        deliveries: [
          { id: 'pickup', name: 'Odbiór w Katowicach', price: 0, demo: false, hint: 'ul. Bankowa 11, 40-007 Katowice' },
          { id: 'parcel', name: 'Paczkomat', price: 12, demo: false, hint: 'Wybierz punkt z listy InPost' },
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
           this.file = { name: detail.name, status: detail.status, uploadToken: detail.uploadToken };
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
             helpWanted: this.helpWanted,
             uploadToken: this.file.uploadToken
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

         shippingAddress: function () {
           if (this.delivery === 'parcel' && this.parcelLocker) {
             return {
               point_code: this.parcelLocker.name,
               name: this.parcelLocker.name,
               address: this.parcelLocker.address,
               city: this.parcelLocker.city,
               post_code: this.parcelLocker.post_code
             };
           }

           if (this.delivery === 'courier') return this.courierAddress;

           return null;
         },

         submitQuoteRequest: async function () {
           if (this.submitted || this.submitting) return;
           if (!this.items.length) {
             this.submitError = 'Dodaj przynajmniej jedną pozycję.';
             return;
           }
           if (!this.delivery || (this.delivery === 'parcel' && !this.parcelLocker)) {
             this.submitError = 'Wybierz sposób odbioru i paczkomat, jeśli jest potrzebny.';
             return;
           }
           if (!this.company.person || !this.company.name || !this.company.email || !this.privacyAccepted) {
             this.submitError = 'Podaj dane kontaktowe i zaakceptuj politykę prywatności.';
             return;
           }

           this.submitError = null;
           this.submitting = true;
           try {
             var response = await fetch('/api/v1/b2b/quote-requests', {
               method: 'POST',
               headers: {
                 'Content-Type': 'application/json',
                 Accept: 'application/json',
                 'Idempotency-Key': this.idempotencyKey || (this.idempotencyKey = window.crypto?.randomUUID?.() || String(Date.now()) + Math.random())
               },
               credentials: 'same-origin',
               body: JSON.stringify({
                 customer: {
                   name: this.company.person,
                   email: this.company.email,
                   phone: this.company.phone,
                   company: this.company.name,
                   nip: this.company.nip
                 },
                 items: this.items.map(function (item) {
                   return {
                     product_key: item.product,
                     configuration: item.params,
                     quantity: Number(item.params.qty || item.quantity || 1),
                     help_wanted: item.helpWanted,
                     upload_token: item.uploadToken || null
                   };
                 }),
                 shipping_method: this.delivery,
                 shipping_address: this.shippingAddress(),
                 requested_by_date: this.byDate || null,
                 invoice_required: this.company.invoice,
                 privacy_policy_accepted: this.privacyAccepted,
                 brief: this.briefOpen ? {
                   type: this.brief.type,
                   description: this.brief.desc,
                   quantity: this.brief.qty,
                   requested_by_date: this.brief.date || null
                 } : null
               })
             });
             var payload = await response.json();
             if (!response.ok) throw new Error(payload.message || Object.values(payload.errors || {}).flat()[0] || 'Nie udało się wysłać zapytania.');
             this.submitted = true;
           } catch (error) {
             this.submitError = error.message;
           } finally {
             this.submitting = false;
           }
         },

        dateLbl: function () { return ccDateLbl(this.byDate); },
        deliveryName: function () {
          var d = this.deliveries.find(function (x) { return x.id === this.delivery; }.bind(this));
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
      });
    });
  });
})();
