/**
 * trabalhe-conosco.js
 * Lógica do formulário multi-step de candidatura.
 */
(function () {
  'use strict';

  // ─── Configuração ─────────────────────────────────────────────────────────
  const TOTAL_STEPS = 3;

  // ─── Estado ───────────────────────────────────────────────────────────────
  let currentStep = 1;

  // ─── Elementos ────────────────────────────────────────────────────────────
  const form          = document.getElementById('tc-form');
  const stepsNav      = document.querySelectorAll('.tc-step');
  const sections      = document.querySelectorAll('.tc-form-section');
  const btnNext       = document.getElementById('btn-next');
  const btnPrev       = document.getElementById('btn-prev');
  const btnSubmit     = document.getElementById('btn-submit');
  const cardTitle     = document.getElementById('card-title');
  const cardSubtitle  = document.getElementById('card-subtitle');

  const TITLES = [
    { t: 'Dados Pessoais', s: 'Passo 1 de 3 — Informações de contato' },
    { t: 'Dados Profissionais', s: 'Passo 2 de 3 — Sua experiência e especialidade' },
    { t: 'Documentos & Revisão', s: 'Passo 3 de 3 — Upload de arquivos e confirmação' },
  ];

  // ─── Navegação entre steps ────────────────────────────────────────────────
  function goTo(step) {
    if (step < 1 || step > TOTAL_STEPS) return;

    sections.forEach(function (s, i) {
      s.classList.toggle('active', i + 1 === step);
    });

    stepsNav.forEach(function (el, i) {
      el.classList.remove('active', 'completed');
      if (i + 1 === step)     el.classList.add('active');
      if (i + 1 < step)       el.classList.add('completed');
    });

    if (cardTitle)    cardTitle.textContent    = TITLES[step - 1].t;
    if (cardSubtitle) cardSubtitle.textContent = TITLES[step - 1].s;

    btnPrev.style.display    = step === 1 ? 'none' : 'inline-flex';
    btnNext.style.display    = step === TOTAL_STEPS ? 'none' : 'inline-flex';
    btnSubmit.style.display  = step === TOTAL_STEPS ? 'inline-flex' : 'none';

    currentStep = step;

    if (step === TOTAL_STEPS) renderSummary();

    // Limpa erros do step atual ao navegar para ele
    const currentSection = sections[step - 1];
    if (currentSection) bindClearOnInput(currentSection);

    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  // ─── Mensagens de erro por campo ────────────────────────────────────────
  const MSGS = {
    'campo-nome':         'Por favor, informe seu nome completo.',
    'campo-email':        'Informe um e-mail válido (ex: nome@email.com).',
    'campo-telefone':     'Informe um telefone com pelo menos 8 dígitos.',
    'campo-cidade':       'Informe sua cidade.',
    'campo-estado':       'Selecione seu estado (UF).',
    'campo-especialidade':'Selecione sua especialidade.',
    'campo-experiencia':  'Informe os anos de experiência.',
    'campo-descricao':    'A descrição precisa ter no mínimo 30 e no máximo 600 caracteres.',
  };

  function showFieldError(el, msg) {
    clearFieldError(el);
    el.classList.add('error');
    el.setAttribute('aria-invalid', 'true');
    const err = document.createElement('p');
    err.className = 'tc-field-error';
    err.setAttribute('role', 'alert');
    err.innerHTML =
      '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">' +
      '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/>' +
      '<line x1="12" y1="16" x2="12.01" y2="16"/></svg>' + msg;
    el.parentElement.appendChild(err);
  }

  function clearFieldError(el) {
    el.classList.remove('error');
    el.removeAttribute('aria-invalid');
    const prev = el.parentElement.querySelector('.tc-field-error');
    if (prev) prev.remove();
  }

  // Limpa erro ao editar o campo
  function bindClearOnInput(section) {
    section.querySelectorAll('.tc-input, .tc-select, .tc-textarea').forEach(function (el) {
      el.addEventListener('input', function () { clearFieldError(el); }, { once: false });
      el.addEventListener('change', function () { clearFieldError(el); }, { once: false });
    });
  }

  // ─── Validação por step ───────────────────────────────────────────────────
  function validateStep(step) {
    const section = sections[step - 1];
    if (!section) return true;

    // Limpa erros anteriores
    section.querySelectorAll('.tc-field-error').forEach(function (e) { e.remove(); });
    section.querySelectorAll('.error').forEach(function (e) { e.classList.remove('error'); });

    let ok = true;
    let firstErrEl = null;

    // ── Step 1: dados pessoais ─────────────────────────────────
    if (step === 1) {
      const fields = ['campo-nome', 'campo-email', 'campo-telefone', 'campo-cidade', 'campo-estado'];
      fields.forEach(function (id) {
        const el = section.querySelector('#' + id);
        if (!el) return;
        const v = el.value.trim();

        if (!v) {
          showFieldError(el, MSGS[id] || 'Campo obrigatório.');
          if (!firstErrEl) firstErrEl = el;
          ok = false;
          return;
        }

        if (id === 'campo-email') {
          const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          if (!re.test(v)) {
            showFieldError(el, MSGS['campo-email']);
            if (!firstErrEl) firstErrEl = el;
            ok = false;
          }
        }

        if (id === 'campo-telefone') {
          if (v.replace(/\D/g, '').length < 8) {
            showFieldError(el, MSGS['campo-telefone']);
            if (!firstErrEl) firstErrEl = el;
            ok = false;
          }
        }
      });
    }

    // ── Step 2: dados profissionais ───────────────────────────
    if (step === 2) {
      ['campo-especialidade', 'campo-experiencia'].forEach(function (id) {
        const el = section.querySelector('#' + id);
        if (!el) return;
        if (!el.value.trim()) {
          showFieldError(el, MSGS[id] || 'Campo obrigatório.');
          if (!firstErrEl) firstErrEl = el;
          ok = false;
        }
      });

      const desc = section.querySelector('#campo-descricao');
      if (desc) {
        const len = desc.value.trim().length;
        if (len < 30) {
          showFieldError(desc, 'A descrição está muito curta. Escreva pelo menos 30 caracteres.');
          if (!firstErrEl) firstErrEl = desc;
          ok = false;
        } else if (len > 600) {
          showFieldError(desc, 'A descrição excede 600 caracteres. Por favor, reduza o texto.');
          if (!firstErrEl) firstErrEl = desc;
          ok = false;
        }
      }
    }

    // ── Step 3: documentos ────────────────────────────────────
    if (step === 3) {
      const curric     = document.getElementById('curriculo-upload');
      const curricWrap = document.querySelector('.tc-upload[data-campo="curriculo"]');
      if (curric && curric.files.length === 0) {
        if (curricWrap) {
          curricWrap.classList.add('error');
          let errP = curricWrap.parentElement.querySelector('.tc-field-error');
          if (!errP) {
            errP = document.createElement('p');
            errP.className = 'tc-field-error';
            errP.setAttribute('role', 'alert');
            errP.innerHTML =
              '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">' +
              '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/>' +
              '<line x1="12" y1="16" x2="12.01" y2="16"/></svg>' +
              'O currículo em PDF é obrigatório.';
            curricWrap.parentElement.appendChild(errP);
          }
          if (!firstErrEl) firstErrEl = curricWrap;
        }
        ok = false;
      }

      const termos     = document.getElementById('aceitar-termos');
      const termosWrap = termos ? termos.closest('.tc-terms') : null;
      if (termos && !termos.checked) {
        if (termosWrap) {
          termosWrap.style.borderColor = 'var(--tc-error)';
          let errP = termosWrap.querySelector('.tc-field-error');
          if (!errP) {
            errP = document.createElement('p');
            errP.className = 'tc-field-error';
            errP.setAttribute('role', 'alert');
            errP.innerHTML =
              '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">' +
              '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/>' +
              '<line x1="12" y1="16" x2="12.01" y2="16"/></svg>' +
              'Você precisa confirmar os termos para enviar a candidatura.';
            termosWrap.appendChild(errP);
          }
          if (!firstErrEl) firstErrEl = termosWrap;
        }
        ok = false;

        termos.addEventListener('change', function () {
          if (termosWrap) {
            termosWrap.style.borderColor = '';
            const e = termosWrap.querySelector('.tc-field-error');
            if (e) e.remove();
          }
        }, { once: true });
      }
    }

    if (!ok) {
      shakeCard();
      if (firstErrEl) firstErrEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    return ok;
  }

  function shakeCard() {
    const card = document.querySelector('.tc-card');
    if (!card) return;
    card.style.animation = 'none';
    card.offsetHeight;
    card.style.animation = 'tc-shake .4s ease';
  }

  // ─── Resumo (step 3) ──────────────────────────────────────────────────────
  function renderSummary() {
    const map = {
      's-nome':          getValue('campo-nome'),
      's-email':         getValue('campo-email'),
      's-telefone':      getValue('campo-telefone'),
      's-cidade-estado': getValue('campo-cidade') + ' — ' + getValue('campo-estado'),
      's-especialidade': getValue('campo-especialidade'),
      's-experiencia':   getValue('campo-experiencia') + ' anos',
      's-curriculo':     getFileName('curriculo-upload'),
      's-certificado':   getFileName('certificado-upload') || '—',
    };

    Object.entries(map).forEach(function ([id, val]) {
      const el = document.getElementById(id);
      if (el) el.textContent = val || '—';
    });
  }

  function getValue(id) {
    const el = document.getElementById(id);
    return el ? el.value.trim() : '';
  }

  function getFileName(id) {
    const el = document.getElementById(id);
    return (el && el.files.length > 0) ? el.files[0].name : '';
  }

  // ─── Upload UX ────────────────────────────────────────────────────────────
  function initUpload(campo, inputId, wrapId) {
    const input = document.getElementById(inputId);
    const wrap  = document.getElementById(wrapId);
    if (!input || !wrap) return;

    const nameEl    = wrap.querySelector('.tc-upload-name');
    const progress  = wrap.nextElementSibling;

    // Drag & Drop
    wrap.addEventListener('dragover', function (e) {
      e.preventDefault();
      wrap.classList.add('dragover');
    });

    wrap.addEventListener('dragleave', function () {
      wrap.classList.remove('dragover');
    });

    wrap.addEventListener('drop', function (e) {
      e.preventDefault();
      wrap.classList.remove('dragover');
      if (e.dataTransfer.files.length > 0) {
        // Transfere arquivo para o input
        const dt = new DataTransfer();
        dt.items.add(e.dataTransfer.files[0]);
        input.files = dt.files;
        onFileSelect(input, wrap, nameEl, progress, e.dataTransfer.files[0].name);
      }
    });

    input.addEventListener('change', function () {
      if (input.files.length > 0) {
        onFileSelect(input, wrap, nameEl, progress, input.files[0].name);
      }
    });
  }

  function onFileSelect(input, wrap, nameEl, progress, name) {
    const file = input.files[0];

    // Validação client-side
    if (!file.name.toLowerCase().endsWith('.pdf')) {
      showFileError(wrap, 'Apenas arquivos PDF são aceitos.');
      input.value = '';
      return;
    }

    if (file.size > 5 * 1024 * 1024) {
      showFileError(wrap, 'Arquivo muito grande. Máximo: 5 MB.');
      input.value = '';
      return;
    }

    wrap.classList.remove('error');
    wrap.classList.add('has-file');
    if (nameEl) nameEl.textContent = '✓ ' + name;

    // Simula progresso visual
    if (progress) {
      const fill  = progress.querySelector('.tc-progress-fill');
      const label = progress.querySelector('.tc-progress-label');
      progress.classList.add('active');
      let pct = 0;
      const interval = setInterval(function () {
        pct += Math.random() * 20;
        if (pct >= 100) {
          pct = 100;
          clearInterval(interval);
          setTimeout(function () {
            if (label) label.textContent = '✓ Arquivo pronto para envio';
          }, 200);
        }
        if (fill) fill.style.width = pct + '%';
      }, 80);
    }
  }

  function showFileError(wrap, msg) {
    wrap.classList.add('error');
    let err = wrap.querySelector('.tc-file-error');
    if (!err) {
      err = document.createElement('p');
      err.className = 'tc-file-error';
      err.style.cssText = 'font-size:12px;color:var(--tc-error);margin-top:8px;';
      wrap.parentElement.appendChild(err);
    }
    err.textContent = msg;
    setTimeout(function () { if (err) err.remove(); }, 4000);
  }

  // ─── Contador de caracteres ───────────────────────────────────────────────
  function initCharCounter(inputId, counterId, max) {
    const input   = document.getElementById(inputId);
    const counter = document.getElementById(counterId);
    if (!input || !counter) return;

    function update() {
      const len = input.value.length;
      counter.textContent = len + '/' + max;
      counter.classList.remove('danger', 'warn');
      if (len > max * 0.9) counter.classList.add(len > max ? 'danger' : 'warn');
    }

    input.addEventListener('input', update);
    update();
  }

  // ─── Máscara de telefone ──────────────────────────────────────────────────
  function initPhoneMask(inputId) {
    const input = document.getElementById(inputId);
    if (!input) return;

    input.addEventListener('input', function () {
      let v = input.value.replace(/\D/g, '').slice(0, 11);
      if (v.length > 10) {
        v = v.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
      } else if (v.length > 6) {
        v = v.replace(/^(\d{2})(\d{4})(\d*)$/, '($1) $2-$3');
      } else if (v.length > 2) {
        v = v.replace(/^(\d{2})(\d*)$/, '($1) $2');
      }
      input.value = v;
    });
  }

  // ─── Termos ───────────────────────────────────────────────────────────────
  function initTermos() {
    const termos = document.getElementById('aceitar-termos');
    if (!termos) return;

    termos.addEventListener('change', function () {
      const wrap = termos.closest('.tc-terms');
      if (wrap) wrap.style.borderColor = termos.checked ? 'var(--tc-gold)' : 'var(--tc-border)';
    });
  }

  // ─── Submit ───────────────────────────────────────────────────────────────
  function initSubmit() {
    if (!form) return;

    form.addEventListener('submit', function (e) {
      if (!validateStep(3)) {
        e.preventDefault();
        return;
      }

      btnSubmit.classList.add('loading');
      btnSubmit.disabled = true;
    });
  }

  // ─── Inicialização ────────────────────────────────────────────────────────
  function init() {
    if (!form) return;

    goTo(1);

    if (btnNext) {
      btnNext.addEventListener('click', function () {
        if (validateStep(currentStep)) goTo(currentStep + 1);
      });
    }

    if (btnPrev) {
      btnPrev.addEventListener('click', function () {
        goTo(currentStep - 1);
      });
    }

    stepsNav.forEach(function (el, i) {
      el.addEventListener('click', function () {
        const target = i + 1;
        if (target < currentStep) goTo(target);
      });
    });

    initUpload('curriculo',    'curriculo-upload',    'curriculo-wrap');
    initUpload('certificado',  'certificado-upload',  'certificado-wrap');
    initCharCounter('campo-descricao', 'desc-counter', 600);
    initPhoneMask('campo-telefone');
    initTermos();
    initSubmit();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
