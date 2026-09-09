/* ===========================
   CopyCabana — App JS
   =========================== */

document.addEventListener('DOMContentLoaded', () => {
  initNavbar();
  initSwiper();
  initScrollReveal();
  initCartFeedback();
  initBrandPlayground();
});

/* --- Sticky Navbar --- */
function initNavbar() {
  const navbar = document.querySelector('#navbar, body.concept-dynamic > header');
  if (!navbar) return;

  const onScroll = () => {
    if (window.scrollY > 28) {
      navbar.classList.add('scrolled');
    } else {
      navbar.classList.remove('scrolled');
    }
  };

  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();
}

/* --- Swiper Carousel --- */
function initSwiper() {
  const el = document.querySelector('.hero-carousel');
  if (!el) return;

  new Swiper('.hero-carousel', {
    loop: true,
    autoplay: {
      delay: 5000,
      disableOnInteraction: false,
    },
    effect: 'fade',
    fadeEffect: { crossFade: true },
    pagination: {
      el: '.swiper-pagination',
      clickable: true,
    },
    speed: 800,
  });
}

/* --- Scroll Reveal --- */
function initScrollReveal() {
  const reveals = document.querySelectorAll('.reveal');
  if (!reveals.length) return;

  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches || !('IntersectionObserver' in window)) {
    reveals.forEach((el) => el.classList.add('visible'));
    return;
  }

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.15, rootMargin: '0px 0px -40px 0px' }
  );

  reveals.forEach((el) => observer.observe(el));
}

function initCartFeedback() {
  if (typeof Cart === 'undefined') return;

  let previousCount = Cart.getCount();
  window.addEventListener('cart-updated', () => {
    const count = Cart.getCount();
    if (count > previousCount && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      document.querySelectorAll('.cart-badge').forEach((badge) => {
        if (!badge.animate) return;
        badge.getAnimations().forEach((animation) => animation.cancel());
        badge.animate([
          { transform: 'scale(1)' },
          { transform: 'scale(1.18)', offset: 0.4 },
          { transform: 'scale(1)' }
        ], { duration: 320, easing: 'cubic-bezier(0.22, 1, 0.36, 1)' });
      });
    }
    previousCount = count;
  });
}

function initBrandPlayground() {
  const root = document.querySelector('[data-brand-playground]');
  if (!root) return;

  const isReducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  initBrandCounters(root, isReducedMotion);
  initBrandStepper(root);
  initBrandUpload(root);
  initBrandTilt(root, isReducedMotion);
  initBrandProcess(root, isReducedMotion);
}

function initBrandCounters(root, isReducedMotion) {
  const counters = root.querySelectorAll('[data-brand-counter]');
  if (!counters.length) return;

  const setFinalValue = (counter) => {
    counter.textContent = Number(counter.dataset.brandCounter || 0).toLocaleString('pl-PL');
  };

  if (isReducedMotion() || !('IntersectionObserver' in window)) {
    counters.forEach(setFinalValue);
    return;
  }

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (!entry.isIntersecting) return;

      const counter = entry.target;
      const target = Number(counter.dataset.brandCounter || 0);
      const start = performance.now();
      const duration = 900;

      const tick = (now) => {
        const progress = Math.min((now - start) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        counter.textContent = Math.round(target * eased).toLocaleString('pl-PL');
        if (progress < 1) requestAnimationFrame(tick);
      };

      requestAnimationFrame(tick);
      observer.unobserve(counter);
    });
  }, { threshold: 0.4 });

  counters.forEach((counter) => observer.observe(counter));
}

function initBrandStepper(root) {
  const stepper = root.querySelector('[data-brand-stepper]');
  if (!stepper) return;

  const buttons = stepper.querySelectorAll('[data-brand-step]');
  const panels = stepper.querySelectorAll('[data-brand-panel]');

  buttons.forEach((button) => {
    button.addEventListener('click', () => {
      const step = button.dataset.brandStep;
      buttons.forEach((item) => item.classList.toggle('is-active', item === button));
      panels.forEach((panel) => panel.classList.toggle('is-active', panel.dataset.brandPanel === step));
    });
  });
}

function initBrandUpload(root) {
  const upload = root.querySelector('[data-brand-upload]');
  if (!upload) return;

  const button = upload.querySelector('[data-upload-next]');
  const title = upload.querySelector('[data-upload-title]');
  const status = upload.querySelector('[data-upload-status]');
  const badge = upload.querySelector('[data-upload-badge]');
  const states = [
    ['Przesyłamy PDF', 'Przesyłanie: 62%. Pozostań na tej stronie.', 'Upload'],
    ['Analizujemy dokument', 'Sprawdzamy liczbę stron, format i strony kolorowe.', 'Analiza'],
    ['PDF gotowy do konfiguracji', '86 stron, 12 kolorowych. Teraz wybierz druk i oprawę.', 'Gotowe'],
    ['Przeciągnij PDF albo wybierz plik', 'Format PDF, limit do ustalenia przed produkcją.', 'Oczekuje']
  ];
  let index = 0;

  button?.addEventListener('click', () => {
    const [nextTitle, nextStatus, nextBadge] = states[index];
    title.textContent = nextTitle;
    status.textContent = nextStatus;
    badge.textContent = nextBadge;
    index = (index + 1) % states.length;
  });
}

function initBrandTilt(root, isReducedMotion) {
  if (isReducedMotion()) return;

  root.querySelectorAll('[data-tilt-card]').forEach((card) => {
    card.addEventListener('pointermove', (event) => {
      const rect = card.getBoundingClientRect();
      const x = ((event.clientX - rect.left) / rect.width - 0.5) * 8;
      const y = ((event.clientY - rect.top) / rect.height - 0.5) * -8;
      card.style.transform = `rotateX(${y}deg) rotateY(${x}deg)`;
    });

    card.addEventListener('pointerleave', () => {
      card.style.transform = '';
    });
  });
}

function initBrandProcess(root, isReducedMotion) {
  const cards = root.querySelectorAll('.brand-process-line article');
  if (!cards.length || isReducedMotion() || !('IntersectionObserver' in window)) return;

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (!entry.isIntersecting) return;
      cards.forEach((card) => card.classList.remove('is-active'));
      entry.target.classList.add('is-active');
    });
  }, { threshold: 0.62 });

  cards.forEach((card) => observer.observe(card));
}

/* --- Mobile Nav Toggle (Alpine.js handles open/close) --- */
/* This is done via Alpine.js x-data on the body element */

/* --- Product Filter (Alpine.js handles this) --- */
/* Filter logic is inline on produkty.html using Alpine.js x-show */
