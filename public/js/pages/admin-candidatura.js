/**
 * admin-candidatura.js
 * Confirmação de ação de aprovação de candidatura.
 * Extraído de views/dashboard/admin/candidatura-detalhe.php
 */
function confirmarAcao(form) {
    var select = form.querySelector('select[name="status"]');
    if (select && select.value === 'aprovado') {
        return confirm(
            'Confirmar APROVAÇÃO?\n\n' +
            'Um perfil profissional será criado automaticamente para este candidato ' +
            'e um e-mail de boas-vindas será enviado.\n\n' +
            'Esta ação não pode ser desfeita facilmente.'
        );
    }
    return true;
}

document.addEventListener('DOMContentLoaded', function () {
    if (typeof lucide !== 'undefined') { lucide.createIcons(); }
});
