-- ═══════════════════════════════════════════════════════════════════════
-- comissoes_migration.sql — AFRO ESSÊNCIA
--
-- Cria as tabelas usadas pelo módulo financeiro (ComissaoDAO,
-- FinanceiroController e ComissaoService), que existiam no código PHP
-- mas não estavam presentes em nenhum arquivo .sql do projeto.
-- Sem essas tabelas, TODAS as telas financeiras (admin e profissional)
-- retornam erro 500 ("Base table or view not found").
--
-- Rode este arquivo após afro_essencia.sql e as demais migrações:
--   mysql -u root -p afro_essencia < comissoes_migration.sql
-- ═══════════════════════════════════════════════════════════════════════

-- ─── config_comissoes ─────────────────────────────────────────────────
-- Configuração de comissão vigente por profissional (percentual ou fixo).
-- Mantém histórico: uma nova config desativa a anterior (ativo=0).
CREATE TABLE IF NOT EXISTS config_comissoes (
    id               INT(11)       NOT NULL AUTO_INCREMENT,
    profissional_id  INT(11)       NOT NULL,
    tipo             ENUM('percentual','fixo') NOT NULL,
    percentual       DECIMAL(5,2)  NULL,
    valor_fixo       DECIMAL(10,2) NULL,
    ativo            TINYINT(1)    NOT NULL DEFAULT 1,
    observacoes      TEXT          NULL,
    created_by       INT(11)       NULL,
    updated_by       INT(11)       NULL,
    created_at       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_cc_profissional (profissional_id),
    KEY idx_cc_ativo (ativo),
    CONSTRAINT fk_cc_profissional FOREIGN KEY (profissional_id) REFERENCES profissionais(id) ON DELETE CASCADE,
    CONSTRAINT fk_cc_created_by FOREIGN KEY (created_by) REFERENCES usuarios(id) ON DELETE SET NULL,
    CONSTRAINT fk_cc_updated_by FOREIGN KEY (updated_by) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── comissoes ────────────────────────────────────────────────────────
-- Uma comissão por agendamento concluído/pago (UNIQUE em agendamento_id
-- garante a idempotência mencionada no docblock de ComissaoService).
CREATE TABLE IF NOT EXISTS comissoes (
    id                INT(11)       NOT NULL AUTO_INCREMENT,
    profissional_id   INT(11)       NOT NULL,
    agendamento_id    INT(11)       NOT NULL,
    config_id         INT(11)       NULL,
    valor_servico     DECIMAL(10,2) NOT NULL,
    tipo_comissao     ENUM('percentual','fixo') NOT NULL,
    percentual        DECIMAL(5,2)  NULL,
    valor_comissao    DECIMAL(10,2) NOT NULL,
    status_pagamento  ENUM('pendente','pago','cancelado') NOT NULL DEFAULT 'pendente',
    forma_pagamento   ENUM('pix','transferencia','dinheiro','outro') NULL,
    data_geracao      DATE          NOT NULL,
    data_pagamento    DATE          NULL,
    pago_por          INT(11)       NULL,
    observacoes       TEXT          NULL,
    created_at        TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_com_agendamento (agendamento_id),
    KEY idx_com_profissional (profissional_id),
    KEY idx_com_status (status_pagamento),
    KEY idx_com_data_geracao (data_geracao),
    CONSTRAINT fk_com_profissional FOREIGN KEY (profissional_id) REFERENCES profissionais(id) ON DELETE CASCADE,
    CONSTRAINT fk_com_agendamento  FOREIGN KEY (agendamento_id)  REFERENCES agendamentos(id)  ON DELETE CASCADE,
    CONSTRAINT fk_com_config       FOREIGN KEY (config_id)       REFERENCES config_comissoes(id) ON DELETE SET NULL,
    CONSTRAINT fk_com_pago_por     FOREIGN KEY (pago_por)        REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── log_financeiro ───────────────────────────────────────────────────
-- Trilha de auditoria de ações do módulo financeiro (ComissaoDAO::log).
CREATE TABLE IF NOT EXISTS log_financeiro (
    id           INT(11)      NOT NULL AUTO_INCREMENT,
    acao         VARCHAR(60)  NOT NULL,
    entidade     VARCHAR(60)  NOT NULL,
    entidade_id  INT(11)      NULL,
    usuario_id   INT(11)      NULL,
    detalhes     TEXT         NULL,
    ip           VARCHAR(45)  NULL,
    created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_log_entidade (entidade, entidade_id),
    KEY idx_log_usuario (usuario_id),
    CONSTRAINT fk_log_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── login_attempts ───────────────────────────────────────────────────
-- Controle de tentativas de login mal-sucedidas, persistido no banco
-- (e não na sessão PHP, que qualquer atacante pode descartar a cada
-- requisição para burlar o bloqueio). Uma linha por e-mail.
CREATE TABLE IF NOT EXISTS login_attempts (
    id             INT(11)      NOT NULL AUTO_INCREMENT,
    email          VARCHAR(180) NOT NULL,
    tentativas     INT(11)      NOT NULL DEFAULT 1,
    bloqueado_ate  DATETIME     NULL,
    updated_at     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_login_attempts_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
