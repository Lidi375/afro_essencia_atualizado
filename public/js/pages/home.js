/* ============================================================
   home.js — Afro Essência
   Carrossel: autoplay 5s + crossfade + Ken Burns + setas + dots + swipe
============================================================ */

(function () {

    function initCarousel() {
        var slides   = document.querySelectorAll('.carousel-slide');
        var dots     = document.querySelectorAll('.carousel-dot');
        var prevBtn  = document.querySelector('.carousel-arrow-prev');
        var nextBtn  = document.querySelector('.carousel-arrow-next');
        var hero     = document.getElementById('hero');

        if (!slides.length) return;

        var current  = 0;
        var total    = slides.length;
        var INTERVAL = 3000;   // 3 segundos
        var timer    = null;
        var running  = false;

        /* ── Ir para slide n ─────────────────────────────── */
        function goTo(n) {
            slides[current].classList.remove('active');
            if (dots[current]) dots[current].classList.remove('active');

            current = ((n % total) + total) % total;

            slides[current].classList.add('active');
            if (dots[current]) dots[current].classList.add('active');
        }

        function next() { goTo(current + 1); }
        function prev() { goTo(current - 1); }

        /* ── Autoplay ────────────────────────────────────── */
        function startAuto() {
            if (running) return;
            running = true;
            timer = setInterval(next, INTERVAL);
        }

        function stopAuto() {
            running = false;
            if (timer) { clearInterval(timer); timer = null; }
        }

        function resetAuto() { stopAuto(); startAuto(); }

        /* ── Setas ───────────────────────────────────────── */
        if (nextBtn) nextBtn.addEventListener('click', function () { next(); resetAuto(); });
        if (prevBtn) prevBtn.addEventListener('click', function () { prev(); resetAuto(); });

        /* ── Dots ────────────────────────────────────────── */
        dots.forEach(function (dot) {
            dot.addEventListener('click', function () {
                goTo(parseInt(this.dataset.index, 10));
                resetAuto();
            });
        });

        /* ── Swipe (touch) ───────────────────────────────── */
        if (hero) {
            var touchStartX = 0;
            hero.addEventListener('touchstart', function (e) {
                touchStartX = e.changedTouches[0].clientX;
            }, { passive: true });

            hero.addEventListener('touchend', function (e) {
                var dx = e.changedTouches[0].clientX - touchStartX;
                if (Math.abs(dx) > 48) {
                    dx < 0 ? next() : prev();
                    resetAuto();
                }
            }, { passive: true });

            /* ── Pausa ao passar o mouse — só em desktop com hero não-fullscreen ── */
            /* Hero ocupa a viewport inteira: mouseenter dispara ao carregar e
               mouseleave nunca dispara → autoplay congela. Solução: detecta se
               o hero tem altura < 90% da viewport antes de aplicar a pausa.   */
            var isFullscreenHero = hero.offsetHeight >= window.innerHeight * 0.85;
            if (!isFullscreenHero) {
                hero.addEventListener('mouseenter', stopAuto);
                hero.addEventListener('mouseleave', startAuto);
            }
        }

        /* ── Inicia autoplay imediatamente ───────────────── */
        startAuto();
    }

    /* ── Scroll suave para âncoras ──────────────────────── */
    function initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
            anchor.addEventListener('click', function (e) {
                var target = this.getAttribute('href');
                if (target !== '#' && document.querySelector(target)) {
                    e.preventDefault();
                    document.querySelector(target).scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    }

    /* ── Init ────────────────────────────────────────────── */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            initCarousel();
            initSmoothScroll();
        });
    } else {
        // DOM já pronto (script carregado com defer ou no fim do body)
        initCarousel();
        initSmoothScroll();
    }

})();
