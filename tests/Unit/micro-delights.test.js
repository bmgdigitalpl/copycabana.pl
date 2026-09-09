import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { test } from 'node:test';
import { runInNewContext } from 'node:vm';

const script = readFileSync(new URL('../../public/js/app.js', import.meta.url), 'utf8');

function page({ reducedMotion = false, observerAvailable = true, withCart = true, withHeader = true } = {}) {
  const events = {};
  const classes = new Set();
  const revealed = new Set();
  const animations = [];
  const observed = [];
  const header = { classList: { add: (name) => classes.add(name), remove: (name) => classes.delete(name) } };
  const reveal = { classList: { add: (name) => revealed.add(name) } };
  const state = { count: 0, reducedMotion };
  const badge = { animate: (...args) => animations.push(args), getAnimations: () => [] };
  const window = {
    scrollY: 0,
    addEventListener: (name, callback) => { events[name] = callback; },
    matchMedia: () => ({ matches: state.reducedMotion })
  };
  const context = {
    window,
    document: {
      addEventListener: (name, callback) => { events[name] = callback; },
      querySelector: (selector) => selector.includes('#navbar') && withHeader ? header : null,
      querySelectorAll: (selector) => selector === '.reveal' ? [reveal] : [badge]
    }
  };
  if (withCart) context.Cart = { getCount: () => state.count };
  if (observerAvailable) {
    context.IntersectionObserver = window.IntersectionObserver = class {
      constructor(callback) { state.intersect = callback; }
      observe(element) { observed.push(element); }
      unobserve(element) { observed.splice(observed.indexOf(element), 1); }
    };
  }
  runInNewContext(script, context);
  events.DOMContentLoaded();
  return { events, classes, revealed, animations, observed, state, window, reveal };
}

function fakeElement({ dataset = {}, textContent = '', children = {}, lists = {}, animate } = {}) {
  const classes = new Set();
  const listeners = {};
  return {
    dataset,
    textContent,
    style: {},
    classList: {
      add: (name) => classes.add(name),
      remove: (name) => classes.delete(name),
      toggle: (name, force) => force ? classes.add(name) : classes.delete(name),
      contains: (name) => classes.has(name)
    },
    addEventListener: (name, callback) => { listeners[name] = callback; },
    dispatch: (name, event = {}) => listeners[name]?.(event),
    querySelector: (selector) => children[selector] || null,
    querySelectorAll: (selector) => lists[selector] || [],
    animate: animate || (() => ({ get onfinish() { return undefined; }, set onfinish(callback) { callback(); } }))
  };
}

function brandPage({ reducedMotion = false } = {}) {
  const events = {};
  const intervals = [];
  const counter = fakeElement({ dataset: { brandCounter: '1200000' } });
  const uploadButton = fakeElement();
  const uploadTitle = fakeElement({ textContent: 'Przeciągnij PDF albo wybierz plik' });
  const uploadStatus = fakeElement({ textContent: 'Format PDF, limit do ustalenia przed produkcją.' });
  const uploadBadge = fakeElement({ textContent: 'Oczekuje' });
  const upload = fakeElement({ children: {
    '[data-upload-next]': uploadButton,
    '[data-upload-title]': uploadTitle,
    '[data-upload-status]': uploadStatus,
    '[data-upload-badge]': uploadBadge
  } });
  const stepA = fakeElement({ dataset: { brandStep: '0' } });
  const stepB = fakeElement({ dataset: { brandStep: '1' } });
  const panelA = fakeElement({ dataset: { brandPanel: '0' } });
  const panelB = fakeElement({ dataset: { brandPanel: '1' } });
  const stepper = fakeElement({ lists: {
    '[data-brand-step]': [stepA, stepB],
    '[data-brand-panel]': [panelA, panelB]
  } });
  const root = fakeElement({ children: {
    '[data-brand-upload]': upload,
    '[data-brand-stepper]': stepper
  }, lists: {
    '[data-brand-counter]': [counter],
    '[data-tilt-card]': [],
    '.brand-process-line article': []
  } });
  const state = { reducedMotion };
  const context = {
    window: {
      scrollY: 0,
      addEventListener: (name, callback) => { events[name] = callback; },
      matchMedia: () => ({ matches: state.reducedMotion })
    },
    document: {
      addEventListener: (name, callback) => { events[name] = callback; },
      querySelector: (selector) => selector === '[data-brand-playground]' ? root : null,
      querySelectorAll: () => []
    },
    setInterval: (callback, delay) => { intervals.push({ callback, delay }); }
  };
  runInNewContext(script, context);
  events.DOMContentLoaded();
  return { counter, intervals, panelA, panelB, stepA, stepB, uploadBadge, uploadButton, uploadStatus, uploadTitle };
}

test('header gains its scrolled surface and resets at the top', () => {
  const view = page();
  assert.equal(view.classes.has('scrolled'), false);
  view.window.scrollY = 29;
  view.events.scroll();
  assert.equal(view.classes.has('scrolled'), true);
  view.window.scrollY = 0;
  view.events.scroll();
  assert.equal(view.classes.has('scrolled'), false);
});

test('cart feedback runs only on increases and honors changed motion preferences', () => {
  const view = page();
  view.events['cart-updated']();
  assert.equal(view.animations.length, 0);
  view.state.count = 1;
  view.events['cart-updated']();
  assert.equal(view.animations.length, 1);
  view.state.count = 0;
  view.events['cart-updated']();
  assert.equal(view.animations.length, 1);
  view.state.reducedMotion = true;
  view.state.count = 2;
  view.events['cart-updated']();
  assert.equal(view.animations.length, 1);
});

test('reduced motion and missing observer leave content visible', () => {
  for (const options of [{ reducedMotion: true }, { observerAvailable: false }]) {
    const view = page(options);
    assert.equal(view.revealed.has('visible'), true);
    assert.equal(view.observed.length, 0);
  }
});

test('scroll reveal observes once and leaves revealed content visible', () => {
  const view = page();
  view.state.intersect([{ target: view.reveal, isIntersecting: false }]);
  assert.equal(view.revealed.has('visible'), false);
  view.state.intersect([{ target: view.reveal, isIntersecting: true }]);
  assert.equal(view.revealed.has('visible'), true);
  assert.equal(view.observed.length, 0);
});

test('pages without a cart or header still initialize their content', () => {
  const view = page({ withCart: false, withHeader: false, reducedMotion: true });
  assert.equal(view.events.scroll, undefined);
  assert.equal(view.events['cart-updated'], undefined);
  assert.equal(view.revealed.has('visible'), true);
});

test('brand playground initializes reduced-motion counters and interactive mockups', () => {
  const view = brandPage({ reducedMotion: true });
  assert.equal(view.intervals.length, 0);
  assert.equal(view.counter.textContent.replace(/\D/g, ''), '1200000');
  view.stepB.dispatch('click');
  assert.equal(view.stepB.classList.contains('is-active'), true);
  assert.equal(view.panelB.classList.contains('is-active'), true);
  assert.equal(view.stepA.classList.contains('is-active'), false);
  assert.equal(view.panelA.classList.contains('is-active'), false);
  view.uploadButton.dispatch('click');
  assert.equal(view.uploadTitle.textContent, 'Przesyłamy PDF');
  assert.equal(view.uploadStatus.textContent, 'Przesyłanie: 62%. Pozostań na tej stronie.');
  assert.equal(view.uploadBadge.textContent, 'Upload');
});
