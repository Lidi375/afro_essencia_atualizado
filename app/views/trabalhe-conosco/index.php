<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Trabalhe Conosco — AFRO ESSÊNCIA</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Dancing+Script:wght@600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="/afro_essencia/public/css/trabalhe-conosco.css">

</head>
<body class="tc-page">

<!-- ═══════════════ NAVBAR ═══════════════ -->
<nav class="tc-nav">
  <a href="/afro_essencia/home" class="tc-nav-brand">
    <img src="/afro_essencia/public/imagens/logo.png" alt="AFRO ESSÊNCIA">
    <span class="tc-nav-brand-text">AFRO ESSÊNCIA</span>
  </a>
  <a href="/afro_essencia/home" class="tc-nav-back">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
    Voltar ao site
  </a>
</nav>

<!-- ═══════════════ HERO ═══════════════ -->
<section class="tc-hero">
  <div class="tc-hero-icon">
    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 7H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2Z"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
  </div>
  <h1>Trabalhe <em>Conosco</em></h1>
  <p>Faça parte da equipe de profissionais da Afro Essência. Preencha o formulário abaixo e aguarde nossa análise.</p>
</section>

<!-- ═══════════════ CONTAINER ═══════════════ -->
<div class="tc-container">

  <?php if (!empty($sucesso)): ?>
    <div class="tc-alert tc-alert--success">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
      <div><?= nl2br(htmlspecialchars($sucesso)) ?></div>
    </div>
  <?php endif; ?>

  <?php if (!empty($erro)): ?>
    <div class="tc-alert tc-alert--error">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <div><?= $erro ?></div>
    </div>
  <?php endif; ?>

  <?php if (empty($sucesso)): ?>

  <!-- Steps de progresso -->
  <ul class="tc-steps" role="list">
    <li class="tc-step active" data-step="1">
      <div class="tc-step-num">1</div>
      <span class="tc-step-label">Pessoal</span>
    </li>
    <li class="tc-step" data-step="2">
      <div class="tc-step-num">2</div>
      <span class="tc-step-label">Profissional</span>
    </li>
    <li class="tc-step" data-step="3">
      <div class="tc-step-num">3</div>
      <span class="tc-step-label">Documentos</span>
    </li>
  </ul>

  <!-- Info banner -->
  <div class="tc-info-banner">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <span>Sua candidatura será analisada pela nossa equipe. Você receberá um e-mail com nossa resposta em até 7 dias úteis.</span>
  </div>

  <!-- Card do formulário -->
  <div class="tc-card">
    <h2 class="tc-card-title" id="card-title">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      Dados Pessoais
    </h2>
    <p class="tc-card-subtitle" id="card-subtitle">Passo 1 de 3 — Informações de contato</p>

    <form id="tc-form" method="POST" action="/afro_essencia/trabalheconosco/submeter"
          enctype="multipart/form-data" novalidate>
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

      <!-- ─── STEP 1: Dados pessoais ─────────────────────── -->
      <div class="tc-form-section active" id="step-1">
        <div class="tc-grid">
          <div class="tc-field">
            <label class="tc-label" for="campo-nome">
              Nome completo <span class="tc-required">*</span>
            </label>
            <input type="text" id="campo-nome" name="nome" class="tc-input"
                   placeholder="Seu nome completo"
                   maxlength="120" required autocomplete="name">
          </div>

          <div class="tc-grid tc-grid-2">
            <div class="tc-field">
              <label class="tc-label" for="campo-email">
                E-mail <span class="tc-required">*</span>
              </label>
              <input type="email" id="campo-email" name="email" class="tc-input"
                     placeholder="seu@email.com"
                     required autocomplete="email">
            </div>

            <div class="tc-field">
              <label class="tc-label" for="campo-telefone">
                Telefone / WhatsApp <span class="tc-required">*</span>
              </label>
              <input type="tel" id="campo-telefone" name="telefone" class="tc-input"
                     placeholder="(11) 99999-9999"
                     maxlength="16" required autocomplete="tel">
            </div>
          </div>

          <div class="tc-grid tc-grid-2">
            <div class="tc-field">
              <label class="tc-label" for="campo-cidade">
                Cidade <span class="tc-required">*</span>
              </label>
              <input type="text" id="campo-cidade" name="cidade" class="tc-input"
                     placeholder="Sua cidade"
                     maxlength="80" required autocomplete="address-level2">
            </div>

            <div class="tc-field">
              <label class="tc-label" for="campo-estado">
                Estado (UF) <span class="tc-required">*</span>
              </label>
              <select id="campo-estado" name="estado" class="tc-select" required>
                <option value="">Selecione...</option>
                <?php
                $ufs = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG',
                        'PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
                foreach ($ufs as $uf): ?>
                  <option value="<?= $uf ?>"><?= $uf ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="tc-field">
            <label class="tc-label" for="campo-instagram">
              Instagram <span style="font-weight:400;opacity:.6;">(opcional)</span>
            </label>
            <input type="text" id="campo-instagram" name="instagram" class="tc-input"
                   placeholder="@seu_perfil"
                   maxlength="80">
          </div>
        </div>
      </div>

      <!-- ─── STEP 2: Dados profissionais ───────────────────── -->
      <div class="tc-form-section" id="step-2">
        <div class="tc-grid">
          <div class="tc-grid tc-grid-2">
            <div class="tc-field">
              <label class="tc-label" for="campo-especialidade">
                Especialidade <span class="tc-required">*</span>
              </label>
              <select id="campo-especialidade" name="especialidade" class="tc-select" required>
                <option value="">Selecione...</option>
                <option value="Tranças Afro">Tranças Afro</option>
                <option value="Dreads">Dreads</option>
                <option value="Tratamentos Capilares">Tratamentos Capilares</option>
                <option value="Cortes Afro">Cortes Afro</option>
                <option value="Coloração Natural">Coloração Natural</option>
                <option value="Penteados">Penteados</option>
                <option value="Box Braids">Box Braids</option>
                <option value="Twist">Twist</option>
                <option value="Nagô">Nagô</option>
                <option value="Outra">Outra</option>
              </select>
            </div>

            <div class="tc-field">
              <label class="tc-label" for="campo-experiencia">
                Anos de experiência <span class="tc-required">*</span>
              </label>
              <input type="number" id="campo-experiencia" name="experiencia_anos" class="tc-input"
                     min="0" max="60" value="0" required>
            </div>
          </div>

          <div class="tc-field">
            <label class="tc-label" for="campo-descricao">
              Descrição profissional <span class="tc-required">*</span>
            </label>
            <textarea id="campo-descricao" name="descricao" class="tc-textarea"
                      placeholder="Descreva sua experiência, técnicas que domina e por que deseja fazer parte da equipe. Mínimo 30 caracteres."
                      maxlength="600" required></textarea>
            <div style="display:flex;justify-content:space-between;align-items:center;">
              <span class="tc-hint">Mínimo 30 · máximo 600 caracteres.</span>
              <span class="tc-char-counter" id="desc-counter">0/600</span>
            </div>
          </div>

          <div class="tc-field">
            <label class="tc-label" for="campo-portfolio">
              Portfólio / Site <span style="font-weight:400;opacity:.6;">(opcional)</span>
            </label>
            <input type="url" id="campo-portfolio" name="portfolio_url" class="tc-input"
                   placeholder="https://seuportfolio.com.br"
                   maxlength="255">
          </div>
        </div>
      </div>

      <!-- ─── STEP 3: Documentos & Revisão ──────────────────── -->
      <div class="tc-form-section" id="step-3">

        <!-- Upload currículo -->
        <div class="tc-field" style="margin-bottom:20px;">
          <label class="tc-label">
            Currículo em PDF <span class="tc-required">*</span>
          </label>
          <div class="tc-upload" id="curriculo-wrap" data-campo="curriculo">
            <input type="file" id="curriculo-upload" name="curriculo"
                   accept=".pdf,application/pdf">
            <div class="tc-upload-icon">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            </div>
            <div class="tc-upload-text">Clique ou arraste o PDF aqui</div>
            <div class="tc-upload-hint">Apenas PDF · Máximo 5 MB</div>
            <div class="tc-upload-name"></div>
          </div>
          <div class="tc-progress">
            <div class="tc-progress-bar"><div class="tc-progress-fill"></div></div>
            <div class="tc-progress-label">Preparando arquivo...</div>
          </div>
        </div>

        <!-- Upload certificados -->
        <div class="tc-field" style="margin-bottom:28px;">
          <label class="tc-label">
            Certificados <span style="font-weight:400;opacity:.6;">(opcional)</span>
          </label>
          <div class="tc-upload" id="certificado-wrap" data-campo="certificado">
            <input type="file" id="certificado-upload" name="certificado"
                   accept=".pdf,application/pdf">
            <div class="tc-upload-icon">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89L17 22l-5-3-5 3 1.523-9.11"/></svg>
            </div>
            <div class="tc-upload-text">Clique ou arraste o PDF dos certificados</div>
            <div class="tc-upload-hint">Apenas PDF · Máximo 5 MB</div>
            <div class="tc-upload-name"></div>
          </div>
          <div class="tc-progress">
            <div class="tc-progress-bar"><div class="tc-progress-fill"></div></div>
            <div class="tc-progress-label">Preparando arquivo...</div>
          </div>
        </div>

        <!-- Resumo -->
        <div class="tc-summary">
          <h4 class="tc-summary-title">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            Revisão da Candidatura
          </h4>
          <div class="tc-summary-grid">
            <div class="tc-summary-item">
              <span class="tc-summary-key">Nome</span>
              <span class="tc-summary-val" id="s-nome">—</span>
            </div>
            <div class="tc-summary-item">
              <span class="tc-summary-key">E-mail</span>
              <span class="tc-summary-val" id="s-email">—</span>
            </div>
            <div class="tc-summary-item">
              <span class="tc-summary-key">Telefone</span>
              <span class="tc-summary-val" id="s-telefone">—</span>
            </div>
            <div class="tc-summary-item">
              <span class="tc-summary-key">Cidade / Estado</span>
              <span class="tc-summary-val" id="s-cidade-estado">—</span>
            </div>
            <div class="tc-summary-item">
              <span class="tc-summary-key">Especialidade</span>
              <span class="tc-summary-val" id="s-especialidade">—</span>
            </div>
            <div class="tc-summary-item">
              <span class="tc-summary-key">Experiência</span>
              <span class="tc-summary-val" id="s-experiencia">—</span>
            </div>
            <div class="tc-summary-item">
              <span class="tc-summary-key">Currículo</span>
              <span class="tc-summary-val" id="s-curriculo">—</span>
            </div>
            <div class="tc-summary-item">
              <span class="tc-summary-key">Certificados</span>
              <span class="tc-summary-val" id="s-certificado">—</span>
            </div>
          </div>
        </div>

        <!-- Aceite de termos -->
        <div class="tc-terms">
          <input type="checkbox" id="aceitar-termos">
          <label for="aceitar-termos">
            Confirmo que as informações fornecidas são verdadeiras e autorizo a Afro Essência a analisar minha candidatura e entrar em contato pelo e-mail ou telefone informados.
          </label>
        </div>

      </div>

      <!-- ─── Botões de navegação ─────────────────────────── -->
      <div class="tc-nav-btns">
        <button type="button" id="btn-prev" class="tc-btn tc-btn-outline" style="display:none;">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
          Anterior
        </button>

        <button type="button" id="btn-next" class="tc-btn tc-btn-primary">
          Próximo
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </button>

        <button type="submit" id="btn-submit" class="tc-btn tc-btn-primary" style="display:none;">
          <span class="tc-btn-label">Enviar Candidatura</span>
          <div class="tc-spinner"></div>
        </button>
      </div>

    </form>
  </div>

  <?php endif; /* sem sucesso */ ?>

</div><!-- /tc-container -->

<script src="/afro_essencia/public/js/pages/trabalhe-conosco.js"></script>


<script src="/afro_essencia/public/js/global/app.js"></script>
</body>
</html>
