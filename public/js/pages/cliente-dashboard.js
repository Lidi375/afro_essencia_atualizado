/**
 * cliente-dashboard.js
 * Microinterações e comportamentos da Dashboard do Cliente v5.0
 * Afro Essência — NÃO altera regras de negócio, rotas ou controllers
 */
(function () {
    'use strict';

    /* ── Inicializar Lucide ──────────────────────────────────── */
    function initLucide() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    /* ── Fechar hero-proximo ao clicar fora ─────────────────── */
    function initHeroProximo() {
        var el = document.querySelector('.hero-proximo');
        if (!el) return;
        // Anima entrada com delay
        el.style.opacity = '0';
        el.style.transform = 'translateY(6px)';
        setTimeout(function () {
            el.style.transition = 'opacity .5s ease, transform .5s ease';
            el.style.opacity = '1';
            el.style.transform = 'translateY(0)';
        }, 300);
    }

    /* ── Ripple ao clicar nos cards ─────────────────────────── */
    function initCardRipple() {
        document.querySelectorAll('.dashboard-card').forEach(function (card) {
            card.addEventListener('click', function (e) {
                var ripple   = document.createElement('span');
                var rect     = card.getBoundingClientRect();
                var size     = Math.max(rect.width, rect.height) * 1.5;
                var x        = e.clientX - rect.left - size / 2;
                var y        = e.clientY - rect.top  - size / 2;
                ripple.style.cssText =
                    'position:absolute;width:' + size + 'px;height:' + size + 'px;' +
                    'left:' + x + 'px;top:' + y + 'px;border-radius:50%;' +
                    'background:rgba(212,169,106,.12);transform:scale(0);' +
                    'animation:ripple-anim .55s ease-out forwards;pointer-events:none;z-index:0;';
                card.appendChild(ripple);
                setTimeout(function () { ripple.remove(); }, 600);
            });
        });

        // Injetar keyframe de ripple dinamicamente (única vez)
        if (!document.getElementById('ripple-style')) {
            var s = document.createElement('style');
            s.id  = 'ripple-style';
            s.textContent = '@keyframes ripple-anim{to{transform:scale(1);opacity:0;}}';
            document.head.appendChild(s);
        }
    }

    /* ── Animação de entrada escalonada dos cards ────────────── */
    function initCardEntrance() {
        var cards = document.querySelectorAll('.dashboard-card, .resumo-widget, .cabelo-card, .momento-card');
        if (!cards.length) return;

        // IntersectionObserver: anima quando o card entra na tela
        if (!('IntersectionObserver' in window)) {
            cards.forEach(function (c) { c.style.opacity = '1'; });
            return;
        }

        cards.forEach(function (c, i) {
            c.style.opacity    = '0';
            c.style.transform  = 'translateY(14px)';
            c.style.transition = 'opacity .4s ease ' + (i * 55) + 'ms, transform .4s ease ' + (i * 55) + 'ms';
        });

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.style.opacity   = '1';
                    entry.target.style.transform = 'translateY(0)';
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.08 });

        cards.forEach(function (c) { observer.observe(c); });
    }

    /* ── DOMContentLoaded ────────────────────────────────────── */
    document.addEventListener('DOMContentLoaded', function () {
        initLucide();
        initHeroProximo();
        initCardRipple();
        initCardEntrance();
    });
})();
