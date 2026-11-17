/* =====================================================
   AUTH.JS - CONTROLES DE AUTENTICAÇÃO
   (Login / Cadastro)
===================================================== */


/* 🔐 MOSTRAR / OCULTAR SENHA */
function togglePassword(id, eyeOpenId, eyeClosedId) {

    const input = document.getElementById(id);
    const eyeOpen = document.getElementById(eyeOpenId);
    const eyeClosed = document.getElementById(eyeClosedId);

    if (!input) return;

    const isPassword = input.type === "password";

    // alterna tipo do input
    input.type = isPassword ? "text" : "password";

    // alterna ícones (se existirem)
    if (eyeOpen && eyeClosed) {
        eyeOpen.style.display = isPassword ? "none" : "block";
        eyeClosed.style.display = isPassword ? "block" : "none";
    }
}


/* 🚀 UX: EFEITO DE LOADING NO BOTÃO (opcional, mas profissional) */
function setLoading(button, estado = true) {

    if (!button) return;

    if (estado) {
        button.dataset.originalText = button.innerHTML;
        button.innerHTML = "Entrando...";
        button.disabled = true;
        button.style.opacity = "0.7";
    } else {
        button.innerHTML = button.dataset.originalText || "ACESSAR";
        button.disabled = false;
        button.style.opacity = "1";
    }
}


/* 🎯 INICIALIZAÇÃO DA PÁGINA */
document.addEventListener('DOMContentLoaded', () => {

    // aplica loading no submit (SEM bloquear PHP)
    const form = document.querySelector('form');

    if (form) {
        form.addEventListener('submit', () => {

            const btn = form.querySelector('button[type="submit"]');
            setLoading(btn, true);

        });
    }

});