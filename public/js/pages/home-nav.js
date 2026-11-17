/**
 * home-nav.js — AFRO ESSÊNCIA
 * Controla o menu hamburguer mobile da página home.
 */
(function () {
  "use strict";

  document.addEventListener("DOMContentLoaded", function () {
    var btn = document.getElementById("home-hamburger");
    var nav = document.getElementById("home-nav");
    var overlay = document.getElementById("home-nav-overlay");
    if (!btn || !nav) return;

    if (window.lucide) window.lucide.createIcons();

    function openMenu() {
      btn.classList.add("open");
      nav.classList.add("open");
      if (overlay) overlay.classList.add("open");
      btn.setAttribute("aria-expanded", "true");
      document.body.style.overflow = "hidden";
    }

    function closeMenu() {
      btn.classList.remove("open");
      nav.classList.remove("open");
      if (overlay) overlay.classList.remove("open");
      btn.setAttribute("aria-expanded", "false");
      document.body.style.overflow = "";
    }

    btn.addEventListener("click", function () {
      nav.classList.contains("open") ? closeMenu() : openMenu();
    });

    if (overlay) overlay.addEventListener("click", closeMenu);

    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape") closeMenu();
    });

    // Fechar ao clicar em link âncora
    nav.querySelectorAll("a").forEach(function (a) {
      a.addEventListener("click", closeMenu);
    });
  });
})();
