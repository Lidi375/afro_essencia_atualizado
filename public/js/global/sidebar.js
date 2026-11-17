/**
 * sidebar.js — AFRO ESSÊNCIA
 * Controla abertura/fechamento da sidebar mobile
 * (hamburger, overlay, Escape, fechamento ao clicar em link)
 * Usado por: dashboard cliente, profissional e admin
 */
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    // Inicializar ícones Lucide se disponíveis
    if (window.lucide) lucide.createIcons();

    var btn     = document.getElementById('prof-hamburger');
    var sidebar = document.getElementById('prof-sidebar');
    var overlay = document.getElementById('prof-overlay');

    if (!btn || !sidebar) return;

    function openMenu() {
      sidebar.classList.add('open');
      if (overlay) overlay.classList.add('open');
      btn.setAttribute('aria-expanded', 'true');
      document.body.style.overflow = 'hidden';
    }

    function closeMenu() {
      sidebar.classList.remove('open');
      if (overlay) overlay.classList.remove('open');
      btn.setAttribute('aria-expanded', 'false');
      document.body.style.overflow = '';
    }

    btn.addEventListener('click', function () {
      sidebar.classList.contains('open') ? closeMenu() : openMenu();
    });

    if (overlay) overlay.addEventListener('click', closeMenu);

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') closeMenu();
    });

    sidebar.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        if (window.innerWidth <= 768) closeMenu();
      });
    });
  });
}());
