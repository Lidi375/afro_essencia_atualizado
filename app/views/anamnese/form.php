<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="/afro_essencia/public/favicon.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AFRO ESSÊNCIA | Minha Anamnese</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Dancing+Script:wght@600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="/afro_essencia/public/css/page-layout.css">
    <link rel="stylesheet" href="/afro_essencia/public/css/anamnese.css">
</head>
<body>

<div class="page-container">

    <header class="page-header">
        <a href="/afro_essencia/dashboard" class="page-brand">
            <img src="/afro_essencia/public/imagens/logo.png" alt="AFRO ESSÊNCIA">
            <span class="page-brand-name">AFRO ESSÊNCIA</span>
        </a>
        <a href="/afro_essencia/auth/logout" class="page-logout">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Sair
        </a>
    </header>

    <a href="/afro_essencia/dashboard" class="link-voltar">
        <i data-lucide="arrow-left"></i>
        Voltar ao painel
    </a>

    <h1 class="page-title">FICHA DE ANAMNESE</h1>
    <p class="page-subtitle">Nos conte sobre o seu cabelo para que possamos oferecer o melhor atendimento</p>

    <?php if (!empty($sucesso)): ?>
        <div class="alert-success" role="alert"><?= htmlspecialchars($sucesso) ?></div>
    <?php endif; ?>
    <?php if (!empty($erro)): ?>
        <div class="alert-danger"  role="alert"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['anamnese_info'])): ?>
        <div class="alert-success" role="alert"><?= htmlspecialchars($_SESSION['anamnese_info']) ?></div>
        <?php unset($_SESSION['anamnese_info']); ?>
    <?php endif; ?>

    <form method="POST" action="/afro_essencia/anamnese/salvar">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

        <!-- ════ SEÇÃO 1: Identificação Capilar ════ -->
        <div class="anamnese-section">
            <h2 class="section-heading">
                <i data-lucide="sparkles"></i>
                Identificação Capilar
            </h2>
            <div class="fields-grid">

                <div class="field-group">
                    <label class="field-label" for="tipo_cabelo">Tipo de Cabelo</label>
                    <select class="field-select" id="tipo_cabelo" name="tipo_cabelo">
                        <option value="">Selecione...</option>
                        <?php
                        $tipos = ['liso' => 'Liso', 'ondulado' => 'Ondulado', 'cacheado' => 'Cacheado', 'crespo' => 'Crespo'];
                        foreach ($tipos as $val => $label):
                            $sel = ($anamnese['tipo_cabelo'] ?? '') === $val ? 'selected' : '';
                        ?>
                            <option value="<?= htmlspecialchars($val, ENT_QUOTES) ?>" <?= htmlspecialchars($sel, ENT_QUOTES) ?>><?= htmlspecialchars($label, ENT_QUOTES) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field-group">
                    <label class="field-label" for="subtipo_cabelo">Subtipo</label>
                    <select class="field-select" id="subtipo_cabelo" name="subtipo_cabelo">
                        <option value="">Selecione...</option>
                        <?php
                        $subtipos = ['1a','1b','1c','2a','2b','2c','3a','3b','3c','4a','4b','4c'];
                        foreach ($subtipos as $s):
                            $sel = ($anamnese['subtipo_cabelo'] ?? '') === $s ? 'selected' : '';
                        ?>
                            <option value="<?= htmlspecialchars($s, ENT_QUOTES) ?>" <?= htmlspecialchars($sel, ENT_QUOTES) ?>><?= htmlspecialchars($s, ENT_QUOTES) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field-group">
                    <label class="field-label" for="nivel_hidratacao">Condição do Fio</label>
                    <select class="field-select" id="nivel_hidratacao" name="nivel_hidratacao">
                        <option value="">Selecione...</option>
                        <?php
                        $hidrat = ['seco' => 'Seco', 'normal' => 'Normal', 'oleoso' => 'Oleoso', 'misto' => 'Misto'];
                        foreach ($hidrat as $val => $label):
                            $sel = ($anamnese['nivel_hidratacao'] ?? '') === $val ? 'selected' : '';
                        ?>
                            <option value="<?= htmlspecialchars($val, ENT_QUOTES) ?>" <?= htmlspecialchars($sel, ENT_QUOTES) ?>><?= htmlspecialchars($label, ENT_QUOTES) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field-group">
                    <label class="field-label" for="comprimento">Comprimento</label>
                    <select class="field-select" id="comprimento" name="comprimento">
                        <option value="">Selecione...</option>
                        <?php
                        $comps = ['curto' => 'Curto', 'medio' => 'Médio', 'longo' => 'Longo', 'muito_longo' => 'Muito Longo'];
                        foreach ($comps as $val => $label):
                            $sel = ($anamnese['comprimento'] ?? '') === $val ? 'selected' : '';
                        ?>
                            <option value="<?= htmlspecialchars($val, ENT_QUOTES) ?>" <?= htmlspecialchars($sel, ENT_QUOTES) ?>><?= htmlspecialchars($label, ENT_QUOTES) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field-group">
                    <label class="field-label" for="densidade">Espessura do Fio</label>
                    <select class="field-select" id="densidade" name="densidade">
                        <option value="">Selecione...</option>
                        <?php
                        $dens = ['fina' => 'Fina', 'media' => 'Média', 'grossa' => 'Grossa'];
                        foreach ($dens as $val => $label):
                            $sel = ($anamnese['densidade'] ?? '') === $val ? 'selected' : '';
                        ?>
                            <option value="<?= htmlspecialchars($val, ENT_QUOTES) ?>" <?= htmlspecialchars($sel, ENT_QUOTES) ?>><?= htmlspecialchars($label, ENT_QUOTES) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

            </div>
        </div>

        <!-- ════ SEÇÃO 2: Histórico Químico ════ -->
        <div class="anamnese-section">
            <h2 class="section-heading">
                <i data-lucide="flask-conical"></i>
                Histórico Químico
            </h2>
            <div class="fields-grid">

                <div class="field-group full">
                    <div class="toggle-group">
                        <input type="checkbox" class="toggle-checkbox" id="usa_quimica" name="usa_quimica"
                            <?= !empty($anamnese['usa_quimica']) ? 'checked' : '' ?>>
                        <label class="toggle-label" for="usa_quimica">
                            <span class="toggle-text">Utilizo ou já utilizei química no cabelo</span>
                            <span class="toggle-switch"></span>
                        </label>
                    </div>
                </div>

                <div id="grupo-quimica" class="field-group" style="display:none">
                    <label class="field-label" for="tipo_quimica">Tipo de Química Utilizada</label>
                    <input type="text" class="field-input" id="tipo_quimica" name="tipo_quimica"
                        placeholder="Ex: coloração, relaxamento, alisamento..."
                        value="<?= htmlspecialchars($anamnese['tipo_quimica'] ?? '') ?>">
                </div>

                <div class="field-group" id="grupo-calor-wrapper-quimica" style="display:none">
                    <label class="field-label" for="ultima_quimica">Última Aplicação</label>
                    <input type="text" class="field-input" id="ultima_quimica" name="ultima_quimica"
                        placeholder="Ex: há 3 meses, em Janeiro/2026..."
                        value="<?= htmlspecialchars($anamnese['ultima_quimica'] ?? '') ?>">
                </div>

            </div>
        </div>

        <!-- ════ SEÇÃO 3: Hábitos de Cuidado ════ -->
        <div class="anamnese-section">
            <h2 class="section-heading">
                <i data-lucide="droplets"></i>
                Hábitos de Cuidado
            </h2>
            <div class="fields-grid">

                <div class="field-group">
                    <label class="field-label" for="frequencia_lavagem">Frequência de Lavagem</label>
                    <select class="field-select" id="frequencia_lavagem" name="frequencia_lavagem">
                        <option value="">Selecione...</option>
                        <?php
                        $lavs = ['diaria' => 'Diária', '2x_semana' => '2x por semana', '3x_semana' => '3x por semana', 'semanal' => '1x por semana', 'quinzenal' => 'Quinzenal'];
                        foreach ($lavs as $val => $label):
                            $sel = ($anamnese['frequencia_lavagem'] ?? '') === $val ? 'selected' : '';
                        ?>
                            <option value="<?= htmlspecialchars($val, ENT_QUOTES) ?>" <?= htmlspecialchars($sel, ENT_QUOTES) ?>><?= htmlspecialchars($label, ENT_QUOTES) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field-group">
                    <label class="field-label">Uso de Calor</label>
                    <div class="toggle-group">
                        <input type="checkbox" class="toggle-checkbox" id="usa_calor" name="usa_calor"
                            <?= !empty($anamnese['usa_calor']) ? 'checked' : '' ?>>
                        <label class="toggle-label" for="usa_calor">
                            <span class="toggle-text">Uso secador, chapinha ou outros</span>
                            <span class="toggle-switch"></span>
                        </label>
                    </div>
                </div>

                <div id="grupo-calor" class="field-group" style="display:none">
                    <label class="field-label" for="frequencia_calor">Frequência do Calor</label>
                    <select class="field-select" id="frequencia_calor" name="frequencia_calor">
                        <option value="">Selecione...</option>
                        <?php
                        $calors = ['diario' => 'Diário', '2x_semana' => '2 a 3x por semana', 'semanal' => '1x por semana', 'raramente' => 'Raramente'];
                        foreach ($calors as $val => $label):
                            $sel = ($anamnese['frequencia_calor'] ?? '') === $val ? 'selected' : '';
                        ?>
                            <option value="<?= htmlspecialchars($val, ENT_QUOTES) ?>" <?= htmlspecialchars($sel, ENT_QUOTES) ?>><?= htmlspecialchars($label, ENT_QUOTES) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field-group full">
                    <label class="field-label" for="problemas_couro">Problemas no Couro Cabeludo</label>
                    <input type="text" class="field-input" id="problemas_couro" name="problemas_couro"
                        placeholder="Ex: caspa, queda, sensibilidade, oleosidade..."
                        value="<?= htmlspecialchars($anamnese['problemas_couro'] ?? '') ?>">
                </div>

                <div class="field-group full">
                    <label class="field-label" for="alergias">Alergias ou Restrições</label>
                    <textarea class="field-textarea" id="alergias" name="alergias"
                        placeholder="Descreva alergias a produtos, substâncias ou procedimentos..."><?= htmlspecialchars($anamnese['alergias'] ?? '') ?></textarea>
                </div>

            </div>
        </div>

        <!-- ════ SEÇÃO 4: Objetivo e Expectativas ════ -->
        <div class="anamnese-section">
            <h2 class="section-heading">
                <i data-lucide="target"></i>
                Objetivo e Expectativas
            </h2>
            <div class="fields-grid single">

                <div class="field-group">
                    <label class="field-label" for="objetivo">Principal Objetivo</label>
                    <select class="field-select" id="objetivo" name="objetivo">
                        <option value="">Selecione...</option>
                        <?php
                        $objs = ['hidratar' => 'Hidratar e nutrir', 'crescimento' => 'Crescimento saudável', 'definir' => 'Definir e realçar os cachos', 'recuperar' => 'Recuperação de danos químicos', 'estilo' => 'Mudar o estilo', 'manutencao' => 'Manutenção geral'];
                        foreach ($objs as $val => $label):
                            $sel = ($anamnese['objetivo'] ?? '') === $val ? 'selected' : '';
                        ?>
                            <option value="<?= htmlspecialchars($val, ENT_QUOTES) ?>" <?= htmlspecialchars($sel, ENT_QUOTES) ?>><?= htmlspecialchars($label, ENT_QUOTES) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field-group">
                    <label class="field-label" for="servicos_interesse">Serviços de Interesse</label>
                    <input type="text" class="field-input" id="servicos_interesse" name="servicos_interesse"
                        placeholder="Ex: tranças, hidratação, corte, coloração..."
                        value="<?= htmlspecialchars($anamnese['servicos_interesse'] ?? '') ?>">
                </div>

                <div class="field-group">
                    <label class="field-label" for="observacoes">Observações Adicionais</label>
                    <textarea class="field-textarea" id="observacoes" name="observacoes"
                        placeholder="Alguma informação importante que queira compartilhar com a profissional..."><?= htmlspecialchars($anamnese['observacoes'] ?? '') ?></textarea>
                </div>

            </div>
        </div>

        <button type="submit" class="btn-salvar">
            <?= $anamnese ? 'Atualizar Ficha' : 'Salvar Ficha' ?>
        </button>

    </form>

</div>

<script src="/afro_essencia/public/js/pages/anamnese.js" defer></script>
<script src="/afro_essencia/public/js/global/alerts.js" defer></script>
<script src="/afro_essencia/public/js/global/app.js"></script>
</body>
</html>
