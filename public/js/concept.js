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

    function addConfiguredItemToCart(item) {
      if (Cart.getItems().length) {
        return 'Koszyk zawiera już pozycje. Dokończ obecne zamówienie lub opróżnij koszyk przed dodaniem skonfigurowanego druku.';
      }

      Cart.addItem(item);
      window.location.assign('/koszyk');

      return null;
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
        bwPages: 0,
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
          self.bwPages = 0;
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
            self.colored = payload.file.color_pages || 0;
            self.bwPages = payload.file.bw_pages || Math.max(0, self.pages - self.colored);
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
          this.bwPages = Math.max(0, this.pages - this.colored);
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
              colored: this.colored,
              bwPages: this.bwPages
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

    /**
     * The InPost Geowidget SDK (window.easyPack) reports a raw point in the same shape as
     * InPost's own Points API. Normalize it to what the configurators/checkout already expect.
     */
    function ccNormalizeGeowidgetPoint(raw) {
      raw = raw || {};
      var addressDetails = raw.address_details || {};
      var address = raw.address || {};
      var location = raw.location || {};
      var addressLine = String(address.line1 || '').trim();
      var street = String(addressDetails.street || '').trim();
      var buildingNumber = String(addressDetails.building_number || '').trim();

      return {
        name: raw.name,
        address: addressLine || (street + ' ' + buildingNumber).trim(),
        city: String(addressDetails.city || '').trim(),
        post_code: String(addressDetails.post_code || '').trim(),
        opening_hours: raw.opening_hours || null,
        description: raw.location_description || null,
        latitude: location.latitude != null ? parseFloat(location.latitude) : null,
        longitude: location.longitude != null ? parseFloat(location.longitude) : null
      };
    }

    /**
     * The Geowidget SDK loads asynchronously and signals readiness by calling
     * window.easyPackAsyncInit once its own internals (fonts, config) are set up — it polls
     * for that hook every 250ms until it's defined, so this must be assigned eagerly. Mount
     * requests made before that point are queued and flushed once the SDK is ready.
     */
    var ccEasyPackReady = false;
    var ccEasyPackQueue = [];

    window.easyPackAsyncInit = function () {
      window.easyPack.init({
        defaultLocale: 'pl',
        points: { types: ['parcel_locker'] },
        map: { initialTypes: ['parcel_locker'] }
      });
      ccEasyPackReady = true;
      ccEasyPackQueue.forEach(function (mount) { mount(); });
      ccEasyPackQueue = [];
    };

    /**
     * The small "pick a point" popup the widget shows on marker click has no photo — that
     * only appears in the separate "Szczegóły" panel. Points do carry a photo though, at a
     * predictable static.easypack24.net URL keyed by point code (confirmed against the point
     * payloads the widget's own selection callback returns), so this adds it straight into
     * the popup instead of making the customer click through for it. Leaflet reuses one popup
     * DOM node and swaps its content per marker, so watch for the ".point-wrapper" it renders
     * fresh into that content each time rather than the (stable) popup container itself.
     */
    function ccEnhanceInpostPopup(wrapper) {
      /* Leaflet builds the popup as a detached tree and attaches it level by level, so this
         observer sees several ancestors "added" in the same content swap — guard against
         enhancing the same wrapper more than once per render. */
      if (wrapper.dataset.ccPhotoAdded) return;

      var heading = wrapper.querySelector('h1');
      var codeEl = heading ? heading.nextElementSibling : null;
      var code = codeEl ? codeEl.textContent.trim() : '';
      if (!code) return;

      wrapper.dataset.ccPhotoAdded = '1';

      var img = document.createElement('img');
      img.className = 'cc-inpost-popup-photo';
      img.loading = 'lazy';
      img.alt = 'Zdjęcie paczkomatu ' + code;
      img.src = 'https://static.easypack24.net/points/pl/images/' + encodeURIComponent(code) + '.jpg';
      img.onerror = function () { img.remove(); };
      wrapper.insertBefore(img, heading);
    }

    function ccObserveInpostPopups(container) {
      new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
          mutation.addedNodes.forEach(function (node) {
            if (node.nodeType !== 1) return;
            var wrapper = node.classList && node.classList.contains('point-wrapper')
              ? node
              : (node.querySelector ? node.querySelector('.point-wrapper') : null);
            if (wrapper) ccEnhanceInpostPopup(wrapper);
          });
        });
      }).observe(container, { childList: true, subtree: true });
    }

    function ccMountInpostMap(elementId, prefix) {
      var mount = function () {
        window.easyPack.mapWidget(elementId, function (point) {
          window.dispatchEvent(new CustomEvent('cc-inpost:point-selected', { detail: { prefix: prefix, point: point } }));
        });
        ccObserveInpostPopups(document.getElementById(elementId));
      };

      if (ccEasyPackReady) mount(); else ccEasyPackQueue.push(mount);
    }
    window.ccMountInpostMap = ccMountInpostMap;

    function ccInpostState(prefix) {
      return {
        parcelLocker: null,
        lockerError: null,

        init: function () {
          var self = this;
          this.$watch('delivery', function (value) {
            if (value !== 'parcel') self.resetLocker();
          });
          window.addEventListener('cc-inpost:point-selected', function (event) {
            if (!event.detail || event.detail.prefix !== prefix) return;
            self.pickLocker(ccNormalizeGeowidgetPoint(event.detail.point));
          });
        },

        resetLocker: function () {
          this.parcelLocker = null;
          this.lockerError = null;
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

    /**
     * Each configurator (ccConfigurator/ccPdfConfigurator/ccB2bConfigurator) defines its own
     * init() to set up its $watch()ers. A plain Object.assign(ccInpostState(prefix), config)
     * would let config's init silently replace ccInpostState's — dropping the locker
     * event listener — so both init()s are composed here instead of one overwriting the other.
     */
    function ccWithInpost(prefix, config) {
      var inpost = ccInpostState(prefix);
      var inpostInit = inpost.init;
      var configInit = config.init;

      return Object.assign(inpost, config, {
        init: function () {
          inpostInit.call(this);
          if (configInit) configInit.call(this);
        }
      });
    }

    /* --- Per-service AI chat (front-end scaffold, n8n RAG webhook to follow) --- */
    Alpine.data('ccServiceChat', function () {
      var nextId = 1;

      return {
        open: false,
        context: '',
        topic: '',
        messages: [],
        suggestedQuestions: [],
        draft: '',

        openFor: function (title, topic, questions) {
          if (this.context !== title) {
            this.context = title;
            this.topic = topic || title;
            this.suggestedQuestions = questions || [];
            this.messages = [];
          }

          this.open = true;
        },

        close: function () {
          this.open = false;
        },

        ask: function (question) {
          this.draft = question;
          this.send();
        },

        scrollToBottom: function () {
          var el = this.$refs.messages;
          this.$nextTick(function () {
            if (el) el.scrollTop = el.scrollHeight;
          });
        },

        send: function () {
          var text = this.draft.trim();
          if (! text) return;

          this.messages.push({ id: nextId++, role: 'user', text: text });
          this.draft = '';
          this.scrollToBottom();

          var self = this;
          // TODO: podłączyć webhook n8n (RAG) tutaj zamiast mocka poniżej.
          setTimeout(function () {
            self.messages.push({
              id: nextId++,
              role: 'assistant',
              text: 'Dzięki za pytanie! Prawdziwy asystent AI pojawi się tu wkrótce — na razie to podgląd interfejsu.'
            });
            self.scrollToBottom();
          }, 500);
        }
      };
    });

    /* --- Thesis configurator --- */
    Alpine.data('ccConfigurator', function () {
      var pricing = window.copyCabanaThesisPricing || {};
      var pagePrices = pricing.page_prices || {};
      var bindingPrices = pricing.bindings || {};
      var coverPrices = pricing.covers || {};
      var universities = pricing.universities || {};
      var coverTitles = pricing.cover_titles || {};
      var imprintColors = pricing.imprint_colors || {};
      var cdOption = pricing.cd || {};
      var spineEngravingOption = pricing.spine_engraving || {};
      var deliveryPrices = pricing.shipping || {};
      var bw = Number(pagePrices.bw);
      var color = Number(pagePrices.color);
      var firstKey = function (items, fallback) { return Object.keys(items)[0] || fallback; };
      return ccWithInpost('thesis', {
          file: { name: null, pages: 0, colored: 0, bwPages: 0, uploadToken: null },
        quote: null,
        quoteLoading: false,
        quoteError: null,
        quoteRequest: 0,
        quoteTimer: null,
        orderError: null,
           print: { color: 'mixed', sided: 'duplex', copies: 1 },
        maxCopies: Number(pricing.max_copies),
        binding: bindingPrices.hard ? 'hard' : firstKey(bindingPrices, ''),
        cover: coverPrices.none ? 'none' : firstKey(coverPrices, ''),
        coverText: '',
        coverColor: firstKey(pricing.cover_colors || {}, ''),
        coverTitle: firstKey(coverTitles, 'magisterska'),
        imprintColor: firstKey(imprintColors, 'gold'),
        university: firstKey(universities, 'us'),
        burnCd: 'false',
        spineEngraving: 'false',
        spineEngravingName: '',
        coverColors: Object.entries(pricing.cover_colors || {}).map(function ([id, value]) { return { id: id, hex: value.hex, label: value.label }; }),
        coverPhotos: window.copyCabanaThesisCoverPhotos || {},
        delivery: null,
        byDate: '',

        bindings: Object.entries(bindingPrices).map(function ([id, value]) { return { id: id, name: value.label, price: Number(value.price), hint: value.hint || '', thickness: id }; }),
        covers: Object.entries(coverPrices).map(function ([id, value]) { return { id: id, name: value.label, price: Number(value.price), hint: value.hint || '' }; }),
        deliveries: [
          { id: 'pickup', name: 'Odbiór w Katowicach', price: Number(deliveryPrices.pickup ?? 0), hint: 'ul. Bankowa 11, 40-007 Katowice' },
          { id: 'parcel', name: 'Paczkomat', price: Number(deliveryPrices.parcel ?? 12), hint: 'Wybierz paczkomat na mapie InPost' },
        ],

         init: function () {
           var self = this;
            ['file.uploadToken', 'print.color', 'print.sided', 'binding', 'cover', 'coverText', 'coverColor', 'coverTitle', 'imprintColor', 'university', 'burnCd', 'spineEngraving', 'spineEngravingName', 'print.copies', 'delivery', 'parcelLocker', 'byDate'].forEach(function (path) {
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
              cover_text: this.cover === 'custom' ? this.coverText : null,
              cover_color: this.coverColor,
              cover_title: this.cover === 'standard' ? this.coverTitle : null,
              imprint_color: this.cover !== 'none' ? this.imprintColor : null,
               university: this.cover === 'standard' ? this.university : null,
               burn_cd: this.burnCd === 'true',
               spine_engraving: this.spineEngraving === 'true',
               spine_engraving_name: this.spineEngraving === 'true' ? this.spineEngravingName : null,
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

        addToCart: function () {
          if (!this.file.uploadToken) {
            this.orderError = 'Najpierw dodaj plik PDF.';

            return;
          }
          if (!this.delivery || (this.delivery === 'parcel' && !this.parcelLocker)) {
            this.orderError = 'Wybierz sposób odbioru i paczkomat, jeśli jest potrzebny.';

            return;
          }
          if (this.cover === 'custom' && !this.coverText) {
            this.orderError = 'Podaj treść własnego napisu na okładce.';

            return;
          }
          if (this.spineEngraving === 'true' && !this.spineEngravingName) {
            this.orderError = 'Podaj imię i nazwisko do grawerowania na grzbiecie.';

            return;
          }

          this.orderError = addConfiguredItemToCart({
            productId: 'praca-dyplomowa',
            productName: 'Praca dyplomowa',
            quantity: 1,
            price: this.printTotal() + (this.bindingPrice() * this.print.copies) + (this.coverPrice() * this.print.copies) + this.spineEngravingPrice() + this.cdPrice(),
            summary: this.file.name + ' · ' + this.bindingName() + ' · ' + this.coverSummary() + ' · ' + (this.spineEngraving === 'true' ? 'grawer grzbietu: ' + this.spineEngravingName + ' · ' : '') + this.print.copies + ' egz.',
            orderType: 'thesis',
            configuration: {
              uploadToken: this.file.uploadToken,
              color_mode: this.print.color,
              sided: this.print.sided,
              binding: this.binding,
              cover: this.cover,
              cover_text: this.cover === 'custom' ? this.coverText : null,
              cover_color: this.coverColor,
              cover_title: this.cover === 'standard' ? this.coverTitle : null,
              imprint_color: this.cover !== 'none' ? this.imprintColor : null,
              university: this.cover === 'standard' ? this.university : null,
              burn_cd: this.burnCd === 'true',
              spine_engraving: this.spineEngraving === 'true',
              spine_engraving_name: this.spineEngraving === 'true' ? this.spineEngravingName : null,
              copies: this.print.copies,
              requestedByDate: this.byDate || null,
              cartCustomer: {
                shipping_method: this.delivery,
                shipping_address: this.shippingAddress() || {},
              },
            },
          });
        },

        go: ccScroll,

        onFile: function (detail) {
          if (!detail || detail.key !== 'thesis') return;
          this.file = { name: detail.name, pages: detail.pages, colored: detail.colored, bwPages: detail.bwPages, uploadToken: detail.uploadToken };
          this.print.color = detail.colored > 0 ? 'mixed' : 'bw';
        },

        coverPhotoUrl: function () {
          return this.coverPhotos[this.coverColor] || null;
        },

        coverColorName: function () {
          var c = this.coverColors.find(function (x) { return x.id === this.coverColor; }.bind(this));
          return c ? c.label : '';
        },

        universityName: function () {
          return universities[this.university] || '';
        },

        coverTitleName: function () {
          return coverTitles[this.coverTitle] || '';
        },

        coverHeading: function () {
          if (this.cover === 'custom') return this.coverText || 'Własny napis';
          return this.coverTitleName();
        },

        coverSubheading: function () {
          return this.cover === 'standard' ? this.universityName() : '';
        },

        coverSummary: function () {
          if (this.cover === 'none') return 'bez napisu';
          if (this.cover === 'custom') return this.coverText || 'własny napis';
          return this.coverTitleName();
        },

        imprintColorName: function () {
          return (imprintColors[this.imprintColor] && imprintColors[this.imprintColor].label) || '';
        },

        imprintStyle: function () {
          var color = imprintColors[this.imprintColor] && imprintColors[this.imprintColor].hex;
          return color ? 'color:' + color : '';
        },

        printTotal: function () {
          if (!this.file.pages) return 0;
          var pages = this.file.pages;
          var total = this.print.color === 'mixed'
            ? (this.file.bwPages * bw) + (this.file.colored * color)
            : pages * bw;
          return total * this.print.copies;
        },
        printColorName: function () {
          return this.print.color === 'bw'
            ? 'wszystko czarno-białe'
            : 'kolorowe jako kolorowe';
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
        cdPrice: function () {
          return this.burnCd === 'true' ? Number(cdOption.price) : 0;
        },
        spineEngravingPrice: function () {
          return this.spineEngraving === 'true' ? Number(spineEngravingOption.price) * this.print.copies : 0;
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
           return this.printTotal() + ((this.bindingPrice() + this.coverPrice()) * this.print.copies) + this.spineEngravingPrice() + this.cdPrice() + (this.deliveryPrice() ?? 0);
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

       return ccWithInpost('pdf', {
         file: { name: null, pages: 0, colored: 0, uploadToken: null },
         quote: null,
         quoteLoading: false,
         quoteError: null,
         quoteRequest: 0,
         quoteTimer: null,
          orderError: null,
          print: { color: 'bw', sided: 'duplex', copies: 1 },
         finish: 'none',
         delivery: null,
         byDate: '',
         courierAddress: { address: '', city: '', post_code: '' },

         finishes: Object.keys(finishes).map(function (id) {
           return { id: id, name: finishes[id].label, price: Number(finishes[id].price || 0), hint: '' };
         }),
         deliveries: [
           { id: 'pickup', name: 'Odbiór w Katowicach', price: Number(deliveryPrices.pickup || 0), hint: 'ul. Bankowa 11, 40-007 Katowice' },
           { id: 'parcel', name: 'Paczkomat', price: Number(deliveryPrices.parcel || 0), hint: 'Wybierz paczkomat na mapie InPost' },
           { id: 'courier', name: 'Kurier', price: Number(deliveryPrices.courier || 0), hint: 'Dostawa pod wskazany adres' }
         ],

         init: function () {
           var self = this;
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

          addToCart: function () {
            if (!this.file.uploadToken) {
              this.orderError = 'Najpierw dodaj plik PDF.';

              return;
            }
            if (!this.delivery || (this.delivery === 'parcel' && !this.parcelLocker)) {
              this.orderError = 'Wybierz sposób odbioru i paczkomat, jeśli jest potrzebny.';

              return;
            }
            if (this.delivery === 'courier' && (!this.courierAddress.address || !this.courierAddress.city || !this.courierAddress.post_code)) {
              this.orderError = 'Uzupełnij adres dostawy kurierskiej.';

              return;
            }
            this.orderError = addConfiguredItemToCart({
              productId: 'druk',
              productName: 'Druk PDF',
              quantity: 1,
              price: this.printTotal() + this.finishPrice(),
              summary: this.file.name + ' · ' + (this.print.sided === 'simplex' ? 'jednostronnie' : 'dwustronnie') + ' · ' + this.print.copies + ' egz.',
              orderType: 'pdf',
              configuration: {
                uploadToken: this.file.uploadToken,
                color_mode: this.print.color,
                sided: this.print.sided,
                finish: this.finish,
                copies: this.print.copies,
                requestedByDate: this.byDate || null,
                cartCustomer: {
                  shipping_method: this.delivery,
                  shipping_address: this.shippingAddress() || {},
                },
              },
            });
          },

         go: ccScroll,
          onFile: function (detail) {
            if (!detail || detail.key !== 'pdf') return;
            this.file = { name: detail.name, pages: detail.pages, colored: detail.colored, bwPages: detail.bwPages, uploadToken: detail.uploadToken };
            this.print.color = detail.colored > 0 ? 'mixed' : 'bw';
          },
          printTotal: function () { return this.file.pages ? (this.print.color === 'mixed' ? (this.file.bwPages * bw) + (this.file.colored * color) : this.file.pages * bw) * this.print.copies : 0; },
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
    var B2B_PRODUCTS = window.copyCabanaB2bCatalog || [];
    var B2B_DELIVERIES = window.copyCabanaB2bDeliveries || [];

    Alpine.data('ccB2bConfigurator', function () {
      return ccWithInpost('b2b', {
        products: B2B_PRODUCTS,
        activeCategory: 'all',

        selected: null,
        params: {},
         file: { name: null, status: 'empty', uploadToken: null },
         helpWanted: false,
         items: [],

         delivery: null,
         byDate: '',
         courierAddress: { address: '', city: '', post_code: '' },
         company: { person: '', name: '', email: '', phone: '', invoice: false, nip: '' },
         privacyAccepted: false,
         submitting: false,
         submitted: false,
         submitError: null,

        deliveries: B2B_DELIVERIES,

        categoryLabel: function (category) {
          return {
            all: 'Wszystkie',
            druk: 'Druk',
            reklama: 'Reklama',
            uslugi: 'Usługi',
            foto: 'Foto'
          }[category] || category;
        },

        categories: function () {
          var categories = this.products.map(function (product) { return product.category || 'inne'; });
          return ['all'].concat(categories.filter(function (category, index, list) { return list.indexOf(category) === index; }));
        },

        visibleProducts: function () {
          if (this.activeCategory === 'all') return this.products;

          return this.products.filter(function (product) { return product.category === this.activeCategory; }.bind(this));
        },

        init: function () {
          var product = new URLSearchParams(window.location.search).get('product');
          if (product && this.products.some(function (item) { return item.id === product; })) {
            this.selectProduct(product);
          }
        },

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
              var def = p.type === 'chips' ? p.values[0].value : String(p.min);
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
              var display = param.type === 'chips'
                ? param.values.find(function (option) { return option.value === v; })?.label || v
                : v;
              parts.push(param.label + ': ' + display);
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
                      product_slug: item.product,
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
                  privacy_policy_accepted: this.privacyAccepted
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
