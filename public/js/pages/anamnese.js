/**
 * anamnese.js
 * Comportamentos do formulário de anamnese.
 * Exibe/oculta campos condicionais conforme respostas do usuário.
 */

(function () {
    'use strict';

    // ── Campos condicionais: usa_quimica ──
    const chkQuimica       = document.getElementById('usa_quimica');
    const grupoQuimica     = document.getElementById('grupo-quimica');

    function toggleQuimica() {
        if (!chkQuimica || !grupoQuimica) return;
        grupoQuimica.style.display = chkQuimica.checked ? 'contents' : 'none';
    }

    if (chkQuimica) {
        chkQuimica.addEventListener('change', toggleQuimica);
        toggleQuimica(); // estado inicial
    }

    // ── Campos condicionais: usa_calor ──
    const chkCalor     = document.getElementById('usa_calor');
    const grupoCalor   = document.getElementById('grupo-calor');

    function toggleCalor() {
        if (!chkCalor || !grupoCalor) return;
        grupoCalor.style.display = chkCalor.checked ? 'block' : 'none';
    }

    if (chkCalor) {
        chkCalor.addEventListener('change', toggleCalor);
        toggleCalor(); // estado inicial
    }

    // ── Ativar ícones Lucide (caso a view não o faça) ──
    if (window.lucide) {
        lucide.createIcons();
    }

})();
