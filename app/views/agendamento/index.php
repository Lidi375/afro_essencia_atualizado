<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="/afro_essencia/public/favicon.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AFRO ESSÊNCIA | Meus Agendamentos</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Dancing+Script:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/afro_essencia/public/css/page-layout.css">
    <link rel="stylesheet" href="/afro_essencia/public/css/agendamento.css">
</head>

<body>

    <div class="page-container">

        <header class="page-header" style="border-bottom: none; padding-bottom: 16px;">
            <a href="/afro_essencia/dashboard" class="page-brand">
                <img src="/afro_essencia/public/imagens/logo.png" alt="AFRO ESSÊNCIA">
                <span class="page-brand-name">AFRO ESSÊNCIA</span>
            </a>
            <div style="display:flex; align-items:center; gap:10px;">
                <a href="/afro_essencia/dashboard" class="page-back" style="margin:0;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                    Painel
                </a>
                <a href="/afro_essencia/auth/logout" class="page-logout">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Sair
                </a>
            </div>
        </header>

        <!-- TÍTULO -->
        <h1 class="page-title">MEUS AGENDAMENTOS</h1>

        <!-- MENSAGENS -->
        <?php if (!empty($mensagem)): ?>
        <div class="alert-success" role="alert"><?= htmlspecialchars($mensagem, ENT_QUOTES) ?></div>
        <?php endif; ?>

        <?php if (!empty($erro)): ?>
        <div class="alert-danger" role="alert"><?= htmlspecialchars($erro, ENT_QUOTES) ?></div>
        <?php endif; ?>


        <!-- =========================
         PRÓXIMOS
    ========================= -->
        <section class="section">

            <div class="section-card">

                <h2 class="section-title">Próximos Agendamentos</h2>

                <?php if (empty($proximos)): ?>
                    <div class="agend-empty-state">
                        <div class="agend-empty-icon">📅</div>
                        <p class="agend-empty-msg">Nenhum agendamento futuro</p>
                        <p class="agend-empty-hint">Que tal marcar seu próximo atendimento?</p>
                        <a href="/afro_essencia/agendamento/create" class="agend-empty-cta">Agendar agora</a>
                    </div>
                <?php else: ?>

                    <div class="table-wrapper">
                        <table class="agendamento-table">

                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Hora</th>
                                    <th>Profissional</th>
                                    <th>Serviço</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach ($proximos as $a): ?>

                                    <?php
                                    $dataHora = strtotime($a['data_agendamento'] . ' ' . $a['hora_inicio']);
                                    $agora = time();

                                    $podeCancelar =
                                        ($a['status'] === 'pendente' || $a['status'] === 'confirmado') &&
                                        ($dataHora > $agora) &&
                                        (($dataHora - $agora) > 7200);

                                    $statusFormatado = ucfirst(str_replace('_', ' ', $a['status']));
                                    ?>

                                    <tr>
                                        <td data-label="Data"><?= date('d/m/Y', strtotime($a['data_agendamento'])) ?></td>
                                        <td data-label="Hora"><?= $a['hora_inicio'] ?></td>
                                        <td data-label="Profissional"><?= $a['profissional'] ?></td>
                                        <td data-label="Serviço"><?= $a['servico'] ?></td>

                                        <td data-label="Status" class="status <?= $a['status'] ?>">
                                            <?= htmlspecialchars($statusFormatado ?? '', ENT_QUOTES) ?>
                                        </td>

                                        <td data-label="Ações" class="td-acoes">
                                            <?php if ($podeCancelar): ?>
                                                <?php
                                                // Mostra botão Pagar se o agendamento ainda não foi pago
                                                $pagDAO    = new PagamentoDAO();
                                                $pagExiste = $pagDAO->findByAgendamento($a['id']);
                                                $jaPago    = $pagExiste && $pagExiste['status_pagamento'] === 'pago';
                                                ?>
                                                <?php if (!$jaPago): ?>
                                                <a href="/afro_essencia/pagamento/iniciar/<?= $a['id'] ?>"
                                                   class="btn-pagar">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" xmlns="http://www.w3.org/2000/svg"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20M7 15h2m4 0h4"/></svg> Pagar
                                                </a>
                                                <?php else: ?>
                                                <span class="span-pago"><svg width="13" height="13" viewBox="0 0 24 24" fill="#4caf50" opacity="0.9"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/></svg> Pago</span>
                                                <?php endif; ?>
                                                <button
                                                    class="btn-reagendar"
                                                    onclick="abrirReagendar(<?= $a['id'] ?>, '<?= $a['profissional'] ?>', '<?= $a['servico'] ?>')">
                                                    Reagendar
                                                </button>
                                                <form method="POST" action="/afro_essencia/agendamento/cancelar/<?= $a['id'] ?>"
                                                      onsubmit="return confirm('Tem certeza que deseja cancelar este agendamento?');"
                                                      style="display:inline;">
                                                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                                    <button type="submit" class="btn-cancelar">Cancelar</button>
                                                </form>
                                            <?php else: ?>
                                                <span style="opacity:0.5;">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>

                                <?php endforeach; ?>
                            </tbody>

                        </table>
                    </div>

                <?php endif; ?>

            </div>
        </section>


        <!-- =========================
         HISTÓRICO
    ========================= -->
        <section class="section">

            <div class="section-card">

                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <h2 class="section-title">Histórico</h2>

                    <!-- ✅ NOVO BOTÃO -->
                    <form method="POST" action="/afro_essencia/agendamento/limparHistorico"
                         onsubmit="return confirm('Limpar o histórico? Esta ação não pode ser desfeita.');">
                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                        <button type="submit" class="btn-limpar-historico">
                            Limpar Histórico
                        </button>
                    </form>
                </div>

                <?php if (empty($historico)): ?>
                    <div class="agend-empty-state">
                        <div class="agend-empty-icon">🗂️</div>
                        <p class="agend-empty-msg">Nenhum histórico ainda</p>
                        <p class="agend-empty-hint">Seus atendimentos passados aparecerão aqui.</p>
                    </div>
                <?php else: ?>

                    <div class="table-wrapper">
                        <table class="agendamento-table">

                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Hora</th>
                                    <th>Profissional</th>
                                    <th>Serviço</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach ($historico as $a): ?>

                                    <?php
                                    $statusFormatado = ucfirst(str_replace('_', ' ', $a['status']));
                                    ?>

                                    <tr>
                                        <td data-label="Data"><?= date('d/m/Y', strtotime($a['data_agendamento'])) ?></td>
                                        <td data-label="Hora"><?= $a['hora_inicio'] ?></td>
                                        <td data-label="Profissional"><?= $a['profissional'] ?></td>
                                        <td data-label="Serviço"><?= $a['servico'] ?></td>

                                        <td data-label="Status" class="status <?= $a['status'] ?>">
                                            <?= htmlspecialchars($statusFormatado ?? '', ENT_QUOTES) ?>
                                        </td>

                                        <td data-label="Ações" class="td-acoes">
                                            <?php if ($a['status'] === 'concluido'): ?>
                                                <?php $jaAvalou = !empty($jaAvaliados[$a['id']]); ?>
                                                <?php if (!$jaAvalou): ?>
                                                <a href="/afro_essencia/avaliacao/create?agendamento_id=<?= $a['id'] ?>"
                                                    class="btn-avaliar">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                                    Avaliar
                                                </a>
                                                <?php else: ?>
                                                <span class="badge-avaliado">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                                    Avaliado
                                                </span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                —
                                            <?php endif; ?>
                                        </td>
                                    </tr>

                                <?php endforeach; ?>
                            </tbody>

                        </table>
                    </div>

                <?php endif; ?>

            </div>
        </section>

    </div>

    <!-- ========================= MODAL REAGENDAR ========================= -->
    <div id="modal-reagendar" class="modal-reagendar">
        <div class="modal-content">

            <!-- TÍTULO -->
            <h2 style="font-family:'Playfair Display',serif; color:#f1c27d; margin:0 0 6px; text-align:center; font-size:1.4rem;">
                REAGENDAR
            </h2>

            <p id="modal-info" style="text-align:center; color:#ccc; font-size: 14px; margin:0 0 22px;"></p>

            <!-- FORM -->
            <form action="/afro_essencia/index.php?url=agendamento/reagendar" method="POST" id="form-reagendar">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

                <input type="hidden" name="agendamento_id" id="reagendar-id">

                <!-- DATA -->
                <div class="input-group date-group" style="margin-bottom:14px; position:relative;">

                    <input
                        type="text"
                        name="data"
                        id="reagendar-data"
                        class="input-field input-date"
                        placeholder="Selecione a nova data"
                        autocomplete="off"
                        required
                        style="cursor:pointer;"> <!-- ✅ mãozinha igual ao create -->

                    <!-- ÍCONE (AGORA NÃO BLOQUEIA O INPUT) -->
                    <div class="date-icon" style="
                    position:absolute;
                    right:12px;
                    top:50%;
                    transform:translateY(-50%);
                    pointer-events:none; /* ESSENCIAL */
                ">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M7 10h5v5H7z" />
                            <path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11z" />
                        </svg>
                    </div>
                </div>

                <!-- HORA -->
                <div class="input-group" style="margin-bottom:20px;">
                    <select name="hora" id="reagendar-hora" class="input-field select-field" required>
                        <option value="">Selecione a data primeiro</option>
                    </select>
                </div>

                <!-- BOTÕES -->
                <div style="display:flex; gap:10px; justify-content:center;">
                    <button type="button" onclick="fecharReagendar()"
                        class="modal-btn-cancel">
                        Cancelar
                    </button>

                    <button type="submit"
                        class="modal-btn-confirm">
                        Confirmar
                    </button>
                </div>

            </form>

        </div>
    </div>

    <!-- FLATPICKR -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>

    <script src="/afro_essencia/public/js/pages/agendamento-index.js"></script>

<script src="/afro_essencia/public/js/global/alerts.js" defer></script>
<script src="/afro_essencia/public/js/global/app.js"></script>
</body>

</html>
