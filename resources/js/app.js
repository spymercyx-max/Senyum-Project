import './bootstrap';
import './intro';

/* ============================================================
 * SENYUM app.js — interaksi vanilla minimal, tanpa framework.
 * - Mobile menu toggle (data-menu-btn / data-menu-panel)
 * - Smoke intro overlay sekali per session (sessionStorage)
 * - Featured product tap (5s reset)
 * - Current year, clipboard copy (data-copy), dismiss alert
 * ============================================================ */

(function () {
  'use strict';

  function prefersReducedMotion() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  }

  /* ---------- Mobile menu ---------- */
  function initMenu() {
    var btns = document.querySelectorAll('[data-menu-btn]');
    var panels = document.querySelectorAll('[data-menu-panel]');
    if (!btns.length || !panels.length) return;

    btns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var target = btn.getAttribute('data-menu-target');
        var panel = target
          ? document.querySelector(target)
          : panels[0];
        if (!panel) return;
        var open = panel.classList.toggle('hidden');
        // classList.toggle mengembalikan true jika class ada (hidden) → tertutup
        btn.setAttribute('aria-expanded', open ? 'false' : 'true');
      });
    });
  }

  /* ---------- Smoke intro ---------- */
  function initIntro() {
    var overlay = document.getElementById('sn-intro');
    if (!overlay) return;

    // Hormati reduced motion & hanya sekali per session
    if (prefersReducedMotion()) {
      overlay.remove();
      return;
    }
    try {
      if (sessionStorage.getItem('sn_intro_seen') === '1') {
        overlay.remove();
        return;
      }
    } catch (e) {
      overlay.remove();
      return;
    }

    var canvas = overlay.querySelector('canvas');
    var smoke = null;
    if (canvas && window.SenyumSmoke) {
      smoke = window.SenyumSmoke.create(canvas, { particles: 60, duration: 2500 });
      smoke.start();
    }

    function finish() {
      overlay.classList.add('is-done');
      try {
        sessionStorage.setItem('sn_intro_seen', '1');
      } catch (e) { /* abaikan */ }
      if (smoke) smoke.stop();
      window.setTimeout(function () {
        if (overlay.parentNode) overlay.parentNode.removeChild(overlay);
      }, 450);
    }

    // ~2.5s lalu fade; klik / Escape mempercepat
    var timer = window.setTimeout(finish, 2600);
    overlay.addEventListener('click', function () {
      window.clearTimeout(timer);
      finish();
    });
    document.addEventListener('keydown', function onKey(e) {
      if (e.key === 'Escape') {
        window.clearTimeout(timer);
        finish();
        document.removeEventListener('keydown', onKey);
      }
    });

    window.SenyumIntro = { finish: finish };
  }

  /* ---------- Featured product tap ---------- */
  function initFeatured() {
    var cards = document.querySelectorAll('[data-featured]');
    if (!cards.length) return;
    var timers = new WeakMap();

    cards.forEach(function (card) {
      card.addEventListener('click', function () {
        card.classList.add('is-active');
        var prev = timers.get(card);
        if (prev) window.clearTimeout(prev);
        timers.set(
          card,
          window.setTimeout(function () {
            card.classList.remove('is-active');
          }, 5000)
        );
      });
    });
  }

  /* ---------- Current year ---------- */
  function initYear() {
    var els = document.querySelectorAll('[data-year]');
    var year = String(new Date().getFullYear());
    els.forEach(function (el) {
      el.textContent = year;
    });
  }

  /* ---------- Clipboard copy ---------- */
  function initCopy() {
    var btns = document.querySelectorAll('[data-copy]');
    if (!btns.length) return;

    btns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var text = btn.getAttribute('data-copy') || '';
        if (!text) return;
        var done = function () {
          var label = btn.getAttribute('data-copy-label');
          if (label) {
            var original = btn.textContent;
            btn.textContent = label;
            window.setTimeout(function () {
              btn.textContent = original;
            }, 1500);
          }
        };
        if (navigator.clipboard && navigator.clipboard.writeText) {
          navigator.clipboard.writeText(text).then(done).catch(function () {});
        } else {
          var ta = document.createElement('textarea');
          ta.value = text;
          document.body.appendChild(ta);
          ta.select();
          try {
            document.execCommand('copy');
            done();
          } catch (e) { /* abaikan */ }
          document.body.removeChild(ta);
        }
      });
    });
  }

  /* ---------- Dismiss alert ---------- */
  function initAlerts() {
    document.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-alert-close]');
      if (!btn) return;
      var alert = btn.closest('.sn-alert');
      if (alert && alert.parentNode) alert.parentNode.removeChild(alert);
    });

    // Modal open/close generik (dipakai senyum-modal)
    document.addEventListener('click', function (e) {
      var openBtn = e.target.closest('[data-modal-open]');
      if (openBtn) {
        var sel = openBtn.getAttribute('data-modal-open');
        var modal = sel ? document.querySelector(sel) : null;
        if (modal) modal.classList.add('is-open');
        return;
      }
      var closeBtn = e.target.closest('[data-modal-close]');
      if (closeBtn) {
        var backdrop = closeBtn.closest('.sn-modal-backdrop');
        if (backdrop) backdrop.classList.remove('is-open');
      }
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        document.querySelectorAll('.sn-modal-backdrop.is-open').forEach(function (m) {
          m.classList.remove('is-open');
        });
      }
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    initMenu();
    initIntro();
    initFeatured();
    initYear();
    initCopy();
    initAlerts();
  });
})();
