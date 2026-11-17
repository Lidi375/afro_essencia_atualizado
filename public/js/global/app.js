/**
 * AFRO ESSÊNCIA — Global App JS
 * Microinterações, hamburger menu, feedback visual
 */

document.addEventListener('DOMContentLoaded', function () {

    // ── Hamburger menu Admin ─────────────────────────────────────
    const admHamburger = document.getElementById('adm-hamburger');
    const admSidebar   = document.querySelector('.adm-sidebar');
    const admOverlay   = document.getElementById('adm-overlay');

    if (admHamburger && admSidebar) {
        admHamburger.addEventListener('click', function () {
            const isOpen = admSidebar.classList.toggle('open');
            admHamburger.setAttribute('aria-expanded', isOpen);
            admOverlay && admOverlay.classList.toggle('active');
        });
        admOverlay && admOverlay.addEventListener('click', function () {
            admSidebar.classList.remove('open');
            admHamburger.setAttribute('aria-expanded', 'false');
            admOverlay.classList.remove('active');
        });
        // Fechar com ESC
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && admSidebar.classList.contains('open')) {
                admSidebar.classList.remove('open');
                admHamburger.setAttribute('aria-expanded', 'false');
                admOverlay && admOverlay.classList.remove('active');
                admHamburger.focus();
            }
        });
        // Fechar ao clicar em link da sidebar (mobile)
        admSidebar.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.innerWidth <= 768) {
                    admSidebar.classList.remove('open');
                    admHamburger && admHamburger.setAttribute('aria-expanded', 'false');
                    admOverlay && admOverlay.classList.remove('active');
                }
            });
        });
    }

    // Observação: o toggle da sidebar do Profissional/Cliente é feito
    // exclusivamente por sidebar.js (carregado nessas páginas). Manter
    // essa lógica duplicada aqui fazia dois listeners alternarem a
    // classe "open" no mesmo clique, cancelando um ao outro e deixando
    // o menu sanduíche sem reação nenhuma ao toque/clique.

    // ── Feedback visual nos botões de submit ─────────────────────
    document.querySelectorAll('form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            const btn = form.querySelector('[type="submit"]');
            if (btn && !btn.disabled) {
                btn.textContent = 'Aguarde...';
                btn.disabled = true;
                btn.style.opacity = '0.75';
            }
        });
    });

    // ── Auto-dismiss alerts ──────────────────────────────────────
    const alerts = document.querySelectorAll('.alert-success, .alert-error, .sucesso, .erro');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity .5s';
            alert.style.opacity = '0';
            setTimeout(function () { alert.style.display = 'none'; }, 500);
        }, 5000);
    });

    // ── Highlight nav item ativo ─────────────────────────────────
    const currentPath = window.location.pathname;
    document.querySelectorAll('.nav-item, .prof-nav-item').forEach(function (link) {
        if (link.getAttribute('href') && currentPath.includes(link.getAttribute('href').replace('/afro_essencia', ''))) {
            // não sobrescrever .active já definido no PHP
        }
    });

});
