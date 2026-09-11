/* ============================================================
 * SENYUM intro — canvas smoke particle ringan
 * ~60 partikel, cyan/gray di atas black. Tanpa gambar orang;
 * siluet generik digambar via CSS/SVG di overlay, bukan canvas.
 * Performa: DPR dibatasi, particle di-recycle, rAF di-stop
 * saat selesai / saat tab hidden.
 * ============================================================ */

(function () {
  'use strict';

  function prefersReducedMotion() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  }

  function createSmoke(canvas, opts) {
    var options = Object.assign(
      { particles: 60, duration: 2500, cyanRatio: 0.35 },
      opts || {}
    );

    var ctx = canvas.getContext('2d');
    if (!ctx) return { start: function () {}, stop: function () {} };

    var dpr = Math.min(window.devicePixelRatio || 1, 1.5);
    var w = 0;
    var h = 0;
    var parts = [];
    var rafId = 0;
    var running = false;
    var startAt = 0;

    function resize() {
      var rect = canvas.getBoundingClientRect();
      w = Math.max(1, Math.floor(rect.width || window.innerWidth));
      h = Math.max(1, Math.floor(rect.height || window.innerHeight));
      canvas.width = Math.floor(w * dpr);
      canvas.height = Math.floor(h * dpr);
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    }

    function spawn(anywhere) {
      var cyan = Math.random() < options.cyanRatio;
      return {
        x: Math.random() * w,
        y: anywhere ? Math.random() * h : h + 20 + Math.random() * 60,
        r: 12 + Math.random() * 42,
        vy: 0.4 + Math.random() * 1.1,
        vx: (Math.random() - 0.5) * 0.5,
        life: 0,
        maxLife: 180 + Math.random() * 160,
        cyan: cyan,
        alpha: 0.05 + Math.random() * 0.12,
      };
    }

    function init() {
      parts = [];
      for (var i = 0; i < options.particles; i++) {
        parts.push(spawn(true));
      }
    }

    function tick(now) {
      if (!running) return;
      var elapsed = now - startAt;

      ctx.clearRect(0, 0, w, h);

      for (var i = 0; i < parts.length; i++) {
        var p = parts[i];
        p.life += 1;
        p.x += p.vx + Math.sin((p.life / 40) + i) * 0.3;
        p.y -= p.vy;
        p.r += 0.08;

        if (p.y < -80 || p.life > p.maxLife) {
          parts[i] = spawn(false);
          continue;
        }

        var fade = 1 - p.life / p.maxLife;
        var a = p.alpha * fade;
        ctx.beginPath();
        ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
        ctx.fillStyle = p.cyan
          ? 'rgba(11, 188, 214,' + a.toFixed(3) + ')'
          : 'rgba(148, 163, 184,' + a.toFixed(3) + ')';
        ctx.fill();
      }

      if (elapsed < options.duration) {
        rafId = requestAnimationFrame(tick);
      } else {
        stop();
      }
    }

    function start() {
      if (running || prefersReducedMotion()) return;
      resize();
      init();
      running = true;
      startAt = performance.now();
      rafId = requestAnimationFrame(tick);
    }

    function stop() {
      running = false;
      if (rafId) cancelAnimationFrame(rafId);
      rafId = 0;
    }

    function handleVisibility() {
      if (document.hidden) stop();
    }

    window.addEventListener('resize', resize);
    document.addEventListener('visibilitychange', handleVisibility);

    return { start: start, stop: stop, resize: resize };
  }

  // Expose global untuk dipakai app.js
  window.SenyumSmoke = { create: createSmoke };
})();
