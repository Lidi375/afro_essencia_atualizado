/**
 * financeiro.js — Módulo financeiro do Afro Essência
 *
 * Lógica de UI separada das views conforme padrão do projeto.
 * Dados dinâmicos (PHP) são passados via atributos data-* ou
 * variáveis globais declaradas na view antes deste script.
 */

(function () {
    'use strict';

    // ── Sidebar admin mobile ──────────────────────────────────────────
    const hamburger = document.getElementById('adm-hamburger');
    const admSidebar = document.querySelector('.adm-sidebar');
    const admOverlay = document.getElementById('adm-overlay');

    function toggleAdmSidebar() {
        if (!admSidebar) return;
        const isOpen = admSidebar.classList.toggle('open');
        admOverlay?.classList.toggle('active', isOpen);
        hamburger?.setAttribute('aria-expanded', String(isOpen));
    }
    hamburger?.addEventListener('click', toggleAdmSidebar);
    admOverlay?.addEventListener('click', toggleAdmSidebar);

    // ── Auto-dismiss alertas após 5s ──────────────────────────────────
    document.querySelectorAll('.alert').forEach(function (el) {
        setTimeout(function () {
            el.style.transition = 'opacity .4s';
            el.style.opacity = '0';
            setTimeout(function () { el.remove(); }, 400);
        }, 5000);
    });

    // ── Modal de pagamento de comissão (comissoes.php) ────────────────
    const modalPagar = document.getElementById('modalPagar');

    if (modalPagar) {
        document.querySelectorAll('.fin-btn-pagar').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.getElementById('modalComissaoId').value = this.dataset.id;
                document.getElementById('modalNome').textContent  = this.dataset.nome;
                document.getElementById('modalValor').textContent = this.dataset.valor;
                modalPagar.style.display = 'flex';
            });
        });

        ['fecharModal', 'cancelarModal'].forEach(function (id) {
            document.getElementById(id)?.addEventListener('click', function () {
                modalPagar.style.display = 'none';
            });
        });

        modalPagar.addEventListener('click', function (e) {
            if (e.target === modalPagar) modalPagar.style.display = 'none';
        });
    }

    // ── Config: toggle percentual / fixo (config.php) ────────────────
    document.querySelectorAll('input[name="tipo"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            const isPerc = this.value === 'percentual';
            const grupoPerc = document.getElementById('grupoPercentual');
            const grupoFixo = document.getElementById('grupoFixo');
            const inputPerc = document.getElementById('percentual');
            const inputFixo = document.getElementById('valor_fixo');

            if (grupoPerc) grupoPerc.style.display = isPerc ? '' : 'none';
            if (grupoFixo) grupoFixo.style.display  = isPerc ? 'none' : '';
            if (inputPerc) inputPerc.required = isPerc;
            if (inputFixo) inputFixo.required  = !isPerc;
        });
    });

    // ── Config: pré-carregar ao selecionar profissional ───────────────
    // configMap é passado pela view via window.FIN_CONFIG_MAP
    const selProfissional = document.getElementById('profissional_id');
    if (selProfissional && window.FIN_CONFIG_MAP) {
        selProfissional.addEventListener('change', function () {
            const cfg = window.FIN_CONFIG_MAP[this.value];
            if (!cfg) return;

            const radioTipo = document.querySelector('input[name="tipo"][value="' + cfg.tipo + '"]');
            if (radioTipo) {
                radioTipo.checked = true;
                radioTipo.dispatchEvent(new Event('change'));
            }

            const inputPerc = document.getElementById('percentual');
            const inputFixo = document.getElementById('valor_fixo');
            const inputObs  = document.getElementById('observacoes');

            if (inputPerc) inputPerc.value = cfg.tipo === 'percentual' ? (cfg.percentual || '') : '';
            if (inputFixo) inputFixo.value = cfg.tipo === 'fixo'       ? (cfg.valor_fixo || '') : '';
            if (inputObs)  inputObs.value  = cfg.observacoes || '';
        });
    }

    // ── Gráfico de evolução (index.php) ──────────────────────────────
    // Dados passados via window.FIN_CHART_DATA pela view
    const canvasEvolucao = document.getElementById('chartEvolucao');
    if (canvasEvolucao && window.FIN_CHART_DATA && window.FIN_CHART_DATA.labels.length > 0) {
        const d = window.FIN_CHART_DATA;
        new Chart(canvasEvolucao, {
            type: 'line',
            data: {
                labels: d.labels,
                datasets: [
                    {
                        label: 'Receita (R$)',
                        data: d.receitas,
                        borderColor: '#D4A96A',
                        backgroundColor: 'rgba(212,169,106,.12)',
                        tension: .4,
                        fill: true,
                        pointBackgroundColor: '#D4A96A',
                    },
                    {
                        label: 'Comissões (R$)',
                        data: d.comissoes,
                        borderColor: '#8C6673',
                        backgroundColor: 'rgba(140,102,115,.08)',
                        tension: .4,
                        fill: true,
                        pointBackgroundColor: '#8C6673',
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
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

    // ── Gráfico de ganhos do profissional (minhas.php) ────────────────
    // Dados passados via window.FIN_PROF_CHART_DATA
    const canvasMeus = document.getElementById('chartMeus');
    if (canvasMeus && window.FIN_PROF_CHART_DATA && window.FIN_PROF_CHART_DATA.labels.length > 0) {
        const d = window.FIN_PROF_CHART_DATA;
        new Chart(canvasMeus, {
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

    // ── Lucide icons ──────────────────────────────────────────────────
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

})();
