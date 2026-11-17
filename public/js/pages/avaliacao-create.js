/**
 * avaliacao-create.js
 * Lógica do formulário de avaliação pós-atendimento.
 * Extraído de views/avaliacao/create.php
 */
(function () {
    'use strict';

    var stars    = document.querySelectorAll('.av-star');
    var hidden   = document.getElementById('nota-hidden');
    var texto    = document.getElementById('av-texto');
    var textarea = document.getElementById('comentario');
    var charEl   = document.getElementById('av-chars');

    var labels = ['', 'Péssimo', 'Ruim', 'Regular', 'Bom', 'Excelente!'];

    function pintar(n, modo) {
        stars.forEach(function (s, i) {
            s.classList.toggle(modo === 'hover' ? 'hover' : 'ativa', i < n);
            if (modo !== 'hover') s.classList.remove('hover');
            s.setAttribute('aria-checked', i < n && modo !== 'hover' ? 'true' : 'false');
        });
        if (texto) {
            texto.textContent = n > 0 ? labels[n] : 'Toque em uma estrela para avaliar';
            texto.classList.toggle('selecionada', modo !== 'hover' && n > 0);
        }
    }

    var selecionada = 0;

    stars.forEach(function (s, i) {
        s.addEventListener('mouseenter', function () { pintar(i + 1, 'hover'); });
        s.addEventListener('mouseleave', function () { pintar(selecionada, 'sel'); });
        s.addEventListener('click', function () {
            selecionada = i + 1;
            if (hidden) hidden.value = selecionada;
            pintar(selecionada, 'sel');
        });
        s.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                selecionada = i + 1;
                if (hidden) hidden.value = selecionada;
                pintar(selecionada, 'sel');
            }
            if (e.key === 'ArrowRight' && i < 4) stars[i + 1].focus();
            if (e.key === 'ArrowLeft'  && i > 0) stars[i - 1].focus();
        });
    });

    if (textarea && charEl) {
        textarea.addEventListener('input', function () {
            charEl.textContent = textarea.value.length;
        });
    }
})();
