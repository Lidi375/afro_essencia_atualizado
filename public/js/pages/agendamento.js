// ============================================================
// agendamento.js — Afro Essência
// Gestão do formulário de agendamento:
//   - Selects nativos (profissional, serviço e horário)
//   - Flatpickr (calendário)
//   - Carregamento de horários disponíveis via API
//   - Exibição de duração do serviço via API
//   - Preview da imagem do serviço
//   - Cálculo de horário de término
// ============================================================

document.addEventListener('DOMContentLoaded', function () {

    // Calendário
    flatpickr('#data', {
        dateFormat: 'Y-m-d',
        minDate: 'today',
        locale: 'pt',
        disableMobile: false,
    });

    // Quando profissional ou serviço mudam, recarrega horários e duração
    var profissionalSelect = document.getElementById('profissional_id');
    var servicoSelect      = document.getElementById('servico_id');
    var dataInput          = document.getElementById('data');
    var horaSelect         = document.getElementById('hora');

    if (profissionalSelect) {
        profissionalSelect.addEventListener('change', function () {
            limparHorarios();
            document.getElementById('horario-termino').innerHTML = '';
            carregarHorarios();
            carregarDuracao();
        });
    }

    if (servicoSelect) {
        servicoSelect.addEventListener('change', function () {
            limparHorarios();
            document.getElementById('horario-termino').innerHTML = '';
            carregarHorarios();
            carregarDuracao();

            // Atualizar preview da imagem do serviço
            var selectedOption = servicoSelect.options[servicoSelect.selectedIndex];
            if (typeof atualizarPreviewServico === 'function') {
                atualizarPreviewServico(selectedOption);
            }
        });
    }

    if (dataInput) {
        dataInput.addEventListener('change', carregarHorarios);
    }

    if (horaSelect) {
        horaSelect.addEventListener('change', calcularHorarioFim);
    }

});


// ── Preview da imagem do serviço ─────────────────────────────

function atualizarPreviewServico(optionEl) {
    var preview = document.getElementById('servico-preview');
    var img     = document.getElementById('servico-preview-img');
    var nome    = document.getElementById('servico-preview-nome');
    if (!preview || !img || !nome || !optionEl) return;

    var imagem      = optionEl.getAttribute('data-imagem') || '';
    var nomeServico = optionEl.getAttribute('data-nome')   || '';

    if (imagem && imagem.trim() !== '') {
        img.src = '/afro_essencia/public/imagens/' + imagem;
        img.alt = nomeServico;
        nome.textContent = nomeServico;
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
}


// ── Limpar horários ──────────────────────────────────────────

function limparHorarios() {
    var horaSelect = document.getElementById('hora');
    if (horaSelect) {
        horaSelect.innerHTML = '<option value="" disabled selected>Selecione um horário</option>';
    }
}


// ── Carregar horários disponíveis ────────────────────────────

function carregarHorarios() {
    var profissional = document.getElementById('profissional_id') ? document.getElementById('profissional_id').value : '';
    var servico      = document.getElementById('servico_id')      ? document.getElementById('servico_id').value      : '';
    var data         = document.getElementById('data')            ? document.getElementById('data').value            : '';
    var horaSelect   = document.getElementById('hora');

    if (!profissional || !servico || !data) return;

    // Feedback visual de carregamento
    horaSelect.innerHTML = '<option value="" disabled selected>Carregando horários…</option>';
    horaSelect.disabled  = true;

    fetch('/afro_essencia/agendamento/horarios?profissional_id=' + profissional +
          '&data=' + data + '&servico_id=' + servico)
        .then(function (r) { return r.json(); })
        .then(function (horarios) {
            horaSelect.innerHTML = '';
            horaSelect.disabled  = false;

            var placeholder      = document.createElement('option');
            placeholder.value    = '';
            placeholder.disabled = true;
            placeholder.selected = true;
            horaSelect.appendChild(placeholder);

            var temDisponivel = false;

            horarios.forEach(function (h) {
                var opt = document.createElement('option');

                // Compatível com ambos os formatos:
                // [{hora, disponivel}] ou ["09:00", "09:30"]
                if (typeof h === 'string') {
                    opt.value       = h;
                    opt.textContent = h;
                    temDisponivel   = true;
                } else {
                    opt.value = h.hora;
                    if (!h.disponivel) {
                        opt.disabled    = true;
                        opt.textContent = h.hora + ' (indisponível)';
                    } else {
                        opt.textContent = h.hora;
                        temDisponivel   = true;
                    }
                }
                horaSelect.appendChild(opt);
            });

            placeholder.textContent = (horarios.length && temDisponivel)
                ? 'Selecione um horário'
                : 'Nenhum horário disponível nesta data';
        })
        .catch(function (err) {
            console.error('Erro ao carregar horários:', err);
            horaSelect.disabled  = false;
            horaSelect.innerHTML = '<option value="" disabled selected>Erro ao carregar horários</option>';
        });
}


// ── Carregar duração do serviço ──────────────────────────────

function carregarDuracao() {
    var profissional = document.getElementById('profissional_id') ? document.getElementById('profissional_id').value : '';
    var servico      = document.getElementById('servico_id')      ? document.getElementById('servico_id').value      : '';
    var divDuracao   = document.getElementById('duracao-servico');

    if (!divDuracao) return;

    if (!profissional || !servico) {
        divDuracao.innerHTML = '';
        return;
    }

    fetch('/afro_essencia/agendamento/duracao?profissional_id=' + profissional +
          '&servico_id=' + servico)
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (!data.duracao) {
                divDuracao.innerHTML = '';
                return;
            }

            var horas   = Math.floor(data.duracao / 60);
            var minutos = data.duracao % 60;

            // Salva duração para calcular horário de término
            window.duracaoTotalMinutos = data.duracao;

            var texto = '⏱ Duração estimada: ';
            if (horas > 0)   texto += horas + 'h ';
            if (minutos > 0) texto += minutos + 'min';

            divDuracao.innerHTML = texto;

            // Recalcula término se já houver horário selecionado
            calcularHorarioFim();
        })
        .catch(function (err) {
            console.error('Erro ao carregar duração:', err);
        });
}


// ── Calcular horário de término ──────────────────────────────

function calcularHorarioFim() {
    var horaInicio = document.getElementById('hora') ? document.getElementById('hora').value : '';
    var divFinal   = document.getElementById('horario-termino');

    if (!divFinal) return;

    if (!horaInicio || !window.duracaoTotalMinutos) {
        divFinal.innerHTML = '';
        return;
    }

    var partes    = horaInicio.split(':').map(Number);
    var totalMin  = (partes[0] * 60 + partes[1]) + window.duracaoTotalMinutos;
    var horaFim   = Math.floor(totalMin / 60);
    var minFim    = totalMin % 60;
    var formatado = String(horaFim).padStart(2, '0') + ':' + String(minFim).padStart(2, '0');

    divFinal.innerHTML = '✅ Término previsto: ' + formatado;
}

/* ================================================================
   Custom dropdown de serviços com scroll — extraído de create.php
================================================================ */
(function () {
    var wrap   = document.getElementById('cs-servico');
    var btn    = document.getElementById('cs-servico-btn');
    var label  = document.getElementById('cs-servico-label');
    var list   = document.getElementById('cs-servico-list');
    var hidden = document.getElementById('servico_id');

    if (!wrap || !btn || !hidden) return;

    function open()  { wrap.setAttribute('aria-expanded', 'true'); }
    function close() { wrap.setAttribute('aria-expanded', 'false'); }
    function toggle() { wrap.getAttribute('aria-expanded') === 'true' ? close() : open(); }

    btn.addEventListener('click', function (e) { e.stopPropagation(); toggle(); });

    document.addEventListener('click', function (e) { if (!wrap.contains(e.target)) close(); });

    wrap.addEventListener('keydown', function (e) {
        var items   = list.querySelectorAll('.cs-option');
        var current = list.querySelector('.cs-option:focus');
        var idx     = Array.prototype.indexOf.call(items, current);
        if (e.key === 'Escape')    { close(); btn.focus(); }
        if (e.key === 'ArrowDown') { e.preventDefault(); open(); items[idx < items.length - 1 ? idx + 1 : 0].focus(); }
        if (e.key === 'ArrowUp')   { e.preventDefault(); if (idx > 0) items[idx - 1].focus(); }
        if (e.key === 'Enter' || e.key === ' ') {
            if (document.activeElement.classList.contains('cs-option')) { e.preventDefault(); document.activeElement.click(); }
        }
    });

    list.querySelectorAll('.cs-option').forEach(function (opt) {
        opt.setAttribute('tabindex', '0');
        opt.addEventListener('click', function () {
            list.querySelectorAll('.cs-option').forEach(function (o) { o.setAttribute('aria-selected', 'false'); });
            opt.setAttribute('aria-selected', 'true');

            var val    = opt.getAttribute('data-value');
            var nome   = opt.getAttribute('data-nome') || opt.querySelector('.cs-opt-nome').textContent;
            var preco  = opt.querySelector('.cs-opt-preco').textContent;
            var imagem = opt.getAttribute('data-imagem') || '';

            label.textContent = nome + ' — ' + preco;
            label.classList.add('selected');
            hidden.value = val;

            var optionEl = hidden.querySelector('option[value="' + val + '"]');
            if (optionEl) { optionEl.setAttribute('data-imagem', imagem); optionEl.setAttribute('data-nome', nome); }

            hidden.dispatchEvent(new Event('change', { bubbles: true }));
            close();
            btn.focus();
        });
    });

    if (hidden.value) {
        var pre = list.querySelector('.cs-option[data-value="' + hidden.value + '"]');
        if (pre) pre.click();
    }
})();

/* ── Pré-seleção de serviço via data-pre-id (sem script inline na view) ── */
(function () {
  var selectEl = document.getElementById('servico_id');
  if (!selectEl) return;
  var preId = parseInt(selectEl.getAttribute('data-pre-id') || '0', 10);
  if (!preId) return;
  for (var i = 0; i < selectEl.options.length; i++) {
    if (parseInt(selectEl.options[i].value) === preId) {
      selectEl.selectedIndex = i;
      selectEl.dispatchEvent(new Event('change'));
      break;
    }
  }
}());
