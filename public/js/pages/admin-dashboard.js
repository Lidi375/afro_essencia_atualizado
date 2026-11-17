/**
 * admin-dashboard.js
 * Gráficos do painel administrativo (Chart.js) + inicialização Lucide.
 * Extraído de views/dashboard/admin/index.php
 *
 * Depende de:
 *   - window.faturamentoDias  (injetado pelo PHP via data attribute ou variável global)
 *   - window.topServicos      (idem)
 *   - Chart.js carregado antes deste script
 *   - lucide carregado antes deste script
 */
(function () {
    'use strict';

    var faturamentoDias = window.adminFaturamentoDias || [];
    var topServicos     = window.adminTopServicos     || [];

    // Paleta terrosa
    var cores = {
        ouro:     '#A56758',
        ourogen:  'rgba(165,103,88,0.15)',
        terra:    '#6A3550',
        terraGen: 'rgba(106,53,80,0.15)',
        areia:    '#d3c7ae',
        grid:     'rgba(255,255,255,0.07)',
        text:     '#c8b9a2',
    };

    // ---- Gráfico de faturamento (linha) ----
    var ctxFat = document.getElementById('chartFaturamento');
    if (ctxFat && typeof Chart !== 'undefined') {
        var labels  = faturamentoDias.map(function (r) {
            var d = new Date(r.dia + 'T00:00:00');
            return d.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' });
        });
        var valores = faturamentoDias.map(function (r) { return parseFloat(r.total); });

        new Chart(ctxFat, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Faturamento (R$)',
                    data: valores,
                    borderColor: cores.ouro,
                    backgroundColor: cores.ourogen,
                    borderWidth: 2.5,
                    pointBackgroundColor: cores.ouro,
                    pointRadius: 4,
                    fill: true,
                    tension: 0.4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                return ' R$ ' + ctx.parsed.y.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: { color: cores.text, font: { size: 11 } },
                        grid:  { color: cores.grid }
                    },
                    y: {
                        ticks: {
                            color: cores.text,
                            font:  { size: 11 },
                            callback: function (v) { return 'R$ ' + v.toLocaleString('pt-BR'); }
                        },
                        grid: { color: cores.grid }
                    }
                }
            }
        });
    }

    // ---- Gráfico de serviços (barras horizontais) ----
    var ctxSrv = document.getElementById('chartServicos');
    if (ctxSrv && typeof Chart !== 'undefined') {
        // Ordena do maior para o menor, assim a barra mais longa fica no topo
        var servicosOrdenados = topServicos.slice().sort(function (a, b) {
            return parseInt(b.total, 10) - parseInt(a.total, 10);
        });
        var nomesServicos = servicosOrdenados.map(function (r) { return r.nome; });
        var totServicos   = servicosOrdenados.map(function (r) { return parseInt(r.total, 10); });
        var paletaBarras  = ['#6A3550','#A56758','#8C4A5E','#B87F6A','#D9B38A','#c9a77e'];

        new Chart(ctxSrv, {
            type: 'bar',
            data: {
                labels: nomesServicos,
                datasets: [{
                    data: totServicos,
                    backgroundColor: nomesServicos.map(function (_, i) { return paletaBarras[i % paletaBarras.length]; }),
                    borderRadius: 6,
                    barPercentage: .65,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                return ' ' + ctx.parsed.x + ' agendamento' + (ctx.parsed.x === 1 ? '' : 's');
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { color: cores.text, font: { size: 11 }, precision: 0 },
                        grid:  { color: cores.grid }
                    },
                    y: {
                        ticks: { color: cores.text, font: { size: 12 } },
                        grid:  { display: false }
                    }
                }
            }
        });
    }

    // Ativar ícones Lucide
    if (typeof lucide !== 'undefined') { lucide.createIcons(); }
})();
