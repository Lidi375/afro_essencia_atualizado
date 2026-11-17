/**
 * pdf-relatorio.js — AFRO ESSÊNCIA
 * Comportamento da página de relatório PDF:
 *  - Botão imprimir dispara window.print()
 *  - Atalho Ctrl+P / Cmd+P preservado pelo browser
 */
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    var btnPrint = document.querySelector('.pdf-btn-print');
    if (btnPrint) {
      btnPrint.addEventListener('click', function () {
        window.print();
      });
      // Remover onclick inline se existir
      btnPrint.removeAttribute('onclick');
    }
  });
}());
