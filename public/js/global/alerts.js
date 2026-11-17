/**
 * alerts.js — AFRO ESSÊNCIA
 * Sistema unificado de alertas:
 *  - Auto-dismiss após 6s (sucesso) / 9s (erro/warning)
 *  - Botão de fechar manual
 *  - Barra de progresso animada
 *  - Compatível com classes antigas e novas
 */
(function () {
  'use strict';

  // ── Ícones SVG por variante ──────────────────────────────────
  var ICONS = {
    success: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
    error:   '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
    warning: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
    info:    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
    close:   '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>'
  };

  // ── Títulos padrão por variante ──────────────────────────────
  var TITLES = {
    success: 'Tudo certo!',
    error:   'Algo deu errado',
    warning: 'Atenção',
    info:    'Informação'
  };

  // ── Duração auto-dismiss (ms) ────────────────────────────────
  var DURATION = {
    success: 6000,
    error:   0,      /* Erros não somem automaticamente */
    warning: 8000,
    info:    7000
  };

  // ── Detectar variante de um elemento ────────────────────────
  function getVariant(el) {
    if (el.classList.contains('af-alert--success') ||
        el.classList.contains('alert-success')     ||
        el.classList.contains('alert-sucesso')     ||
        el.classList.contains('alert-ok')          ||
        el.classList.contains('cl-alert-success')  ||
        el.classList.contains('sucesso'))            { return 'success'; }
    if (el.classList.contains('af-alert--error')   ||
        el.classList.contains('alert-danger')       ||
        el.classList.contains('alert-erro')         ||
        el.classList.contains('alert-err')          ||
        el.classList.contains('cl-alert-danger')    ||
        el.classList.contains('erro'))               { return 'error'; }
    if (el.classList.contains('af-alert--warning')) { return 'warning'; }
    return 'info';
  }

  // ── Dispensar alerta com animação ───────────────────────────
  function dismiss(el) {
    if (el.dataset.dismissing) return;
    el.dataset.dismissing = '1';
    el.classList.add('af-alert--dismissing');
    setTimeout(function () {
      if (el.parentNode) el.parentNode.removeChild(el);
    }, 380);
  }

  // ── Enriquecer alerta existente (classes antigas) ───────────
  function enrich(el) {
    if (el.dataset.afEnriched) return;
    el.dataset.afEnriched = '1';

    var variant = getVariant(el);
    var rawText = el.textContent.trim();
    if (!rawText) return;

    var duration = DURATION[variant];

    // Reconstruir HTML interno com ícone + corpo + botão fechar + barra
    el.innerHTML =
      '<span class="af-alert__icon">' + ICONS[variant] + '</span>' +
      '<span class="af-alert__body af-alert--simple">' +
        '<span class="af-alert__msg">' + rawText + '</span>' +
      '</span>' +
      '<button class="af-alert__close" aria-label="Fechar">' + ICONS.close + '</button>' +
      (duration > 0 ? '<span class="af-alert__bar" style="animation-duration:' + duration + 'ms"></span>' : '');

    // role de acessibilidade
    el.setAttribute('role', 'alert');
    el.setAttribute('aria-live', 'polite');

    // Botão fechar
    el.querySelector('.af-alert__close').addEventListener('click', function () {
      dismiss(el);
    });

    // Auto-dismiss
    if (duration > 0) {
      setTimeout(function () { dismiss(el); }, duration);
    }
  }

  // ── Enriquecer alertas .af-alert (HTML completo) ────────────
  function enrichNew(el) {
    if (el.dataset.afEnriched) return;
    el.dataset.afEnriched = '1';

    var variant = getVariant(el);
    var duration = DURATION[variant];

    // Injetar ícone se não existir
    if (!el.querySelector('.af-alert__icon')) {
      var iconEl = document.createElement('span');
      iconEl.className = 'af-alert__icon';
      iconEl.innerHTML = ICONS[variant];
      el.insertBefore(iconEl, el.firstChild);
    }

    // Injetar botão fechar se não existir
    if (!el.querySelector('.af-alert__close')) {
      var closeEl = document.createElement('button');
      closeEl.className = 'af-alert__close';
      closeEl.setAttribute('aria-label', 'Fechar');
      closeEl.innerHTML = ICONS.close;
      closeEl.addEventListener('click', function () { dismiss(el); });
      el.appendChild(closeEl);
    }

    // Barra de progresso
    if (duration > 0 && !el.querySelector('.af-alert__bar')) {
      var bar = document.createElement('span');
      bar.className = 'af-alert__bar';
      bar.style.animationDuration = duration + 'ms';
      el.appendChild(bar);
      setTimeout(function () { dismiss(el); }, duration);
    }

    el.setAttribute('role', 'alert');
    el.setAttribute('aria-live', 'polite');
  }

  // ── Seletor unificado de todos os alertas do projeto ────────
  var SELECTOR = [
    '.af-alert',
    '.alert-success', '.alert-sucesso', '.alert-ok', '.cl-alert-success', '.sucesso',
    '.alert-danger',  '.alert-erro',    '.alert-err', '.cl-alert-danger',  '.erro',
    '.af-alert--warning', '.af-alert--info',
    '.ds-alert.ds-alert-success', '.ds-alert.ds-alert-error'
  ].join(',');

  // ── Init ─────────────────────────────────────────────────────
  function init() {
    document.querySelectorAll(SELECTOR).forEach(function (el) {
      if (el.classList.contains('af-alert')) {
        enrichNew(el);
      } else {
        enrich(el);
      }
    });

    // MutationObserver para alertas injetados dinamicamente
    var observer = new MutationObserver(function (mutations) {
      mutations.forEach(function (m) {
        m.addedNodes.forEach(function (node) {
          if (node.nodeType !== 1) return;
          if (node.matches && node.matches(SELECTOR)) {
            node.classList.contains('af-alert') ? enrichNew(node) : enrich(node);
          }
          node.querySelectorAll && node.querySelectorAll(SELECTOR).forEach(function (el) {
            el.classList.contains('af-alert') ? enrichNew(el) : enrich(el);
          });
        });
      });
    });
    observer.observe(document.body, { childList: true, subtree: true });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
