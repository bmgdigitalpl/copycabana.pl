/* ===========================
   CopyCabana — App JS
   =========================== */

document.addEventListener('DOMContentLoaded', () => {
  initNavbar();
  initSwiper();
  initScrollReveal();
});

/* --- Sticky Navbar --- */
function initNavbar() {
  const navbar = document.getElementById('navbar');
  if (!navbar) return;

  const onScroll = () => {
    if (window.scrollY > 50) {
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

/* --- Mobile Nav Toggle (Alpine.js handles open/close) --- */
/* This is done via Alpine.js x-data on the body element */

/* --- Product Filter (Alpine.js handles this) --- */
/* Filter logic is inline on produkty.html using Alpine.js x-show */
