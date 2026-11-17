/**
 * financeiro-profissional.js — Módulo financeiro, painel do profissional
 *
 * Controla sidebar mobile e gráfico de ganhos.
 * Dados do gráfico são recebidos via window.FIN_PROF_CHART_DATA.
 */

(function () {
    'use strict';

    // ── Sidebar mobile do profissional ───────────────────────────────
    var hamburger = document.getElementById('prof-hamburger');
    var sidebar   = document.getElementById('prof-sidebar');
    var overlay   = document.getElementById('prof-overlay');

    function toggleSidebar() {
        if (!sidebar) return;
        var isOpen = sidebar.classList.toggle('open');
        if (overlay) overlay.classList.toggle('open', isOpen);
        if (hamburger) hamburger.setAttribute('aria-expanded', String(isOpen));
    }

    if (hamburger) hamburger.addEventListener('click', toggleSidebar);
    if (overlay)   overlay.addEventListener('click',   toggleSidebar);

    // ── Auto-dismiss alertas após 5s ─────────────────────────────────
    document.querySelectorAll('.alert').forEach(function (el) {
        setTimeout(function () {
            el.style.transition = 'opacity .4s';
            el.style.opacity = '0';
            setTimeout(function () { el.remove(); }, 400);
        }, 5000);
    });

    // ── Gráfico de ganhos (minhas.php) ───────────────────────────────
    // Dados passados via window.FIN_PROF_CHART_DATA antes deste script
    var canvas = document.getElementById('chartMeus');
    if (canvas && window.FIN_PROF_CHART_DATA && window.FIN_PROF_CHART_DATA.labels.length > 0) {
        var d = window.FIN_PROF_CHART_DATA;
        new Chart(canvas, {
            type: 'bar',
            data: {
                labels: d.labels,
                datasets: [{
                    label: 'Comissões (R$)',
                    data: d.comissoes,
                    backgroundColor: 'rgba(212,169,106,.7)',
                    borderColor: '#D4A96A',
                    borderWidth: 1,
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                return ' R$ ' + parseFloat(ctx.raw).toLocaleString('pt-BR', { minimumFractionDigits: 2 });
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        ticks: {
                            callback: function (v) {
                                return 'R$ ' + parseFloat(v).toLocaleString('pt-BR', { minimumFractionDigits: 2 });
                            }
                        }
                    }
                }
            }
        });
    }

    // ── Lucide icons ─────────────────────────────────────────────────
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

})();
