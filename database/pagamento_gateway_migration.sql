-- ═══════════════════════════════════════════════════════════════════════════════
-- MIGRAÇÃO — Sistema de Pagamento Real com Gateway — Afro Essência v3
-- Execute este script no banco existente.
-- Compatível com o schema afro_essencia.sql original.
-- ═══════════════════════════════════════════════════════════════════════════════

-- ─────────────────────────────────────────────────────────────────────────────
-- 1. Ampliar enum de status do agendamento para suportar estados de pagamento
-- ─────────────────────────────────────────────────────────────────────────────
ALTER TABLE `agendamentos`
    MODIFY COLUMN `status` ENUM(
        'pendente',
        'confirmado',
        'em_andamento',
        'concluido',
        'cancelado',
        'aguardando_pagamento',
        'pagamento_pendente',
        'pagamento_aprovado',
        'pagamento_recusado',
        'pagamento_cancelado',
        'reembolsado'
    ) NOT NULL DEFAULT 'pendente';

-- ─────────────────────────────────────────────────────────────────────────────
-- 2. Tabela principal de transações com gateway
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `payment_transactions` (
    `id`                INT(11)         NOT NULL AUTO_INCREMENT,
    `transaction_id`    VARCHAR(36)     NOT NULL COMMENT 'UUID interno',
    `agendamento_id`    INT(11)         NOT NULL,
    `gateway`           VARCHAR(30)     NOT NULL DEFAULT 'mercadopago',
    `gateway_reference` VARCHAR(255)    DEFAULT NULL COMMENT 'ID/preferência no gateway',
    `amount`            DECIMAL(10,2)   NOT NULL,
    `currency`          VARCHAR(3)      NOT NULL DEFAULT 'BRL',
    `status`            ENUM('pending','approved','rejected','cancelled','refunded')
                        NOT NULL DEFAULT 'pending',
    `paid_at`           DATETIME        DEFAULT NULL,
    `created_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_transaction_id` (`transaction_id`),
    KEY `idx_pt_agendamento` (`agendamento_id`),
    KEY `idx_pt_status` (`status`),
    KEY `idx_pt_gateway_ref` (`gateway_reference`),
    CONSTRAINT `fk_pt_agendamento` FOREIGN KEY (`agendamento_id`)
        REFERENCES `agendamentos` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ─────────────────────────────────────────────────────────────────────────────
-- 3. Logs de eventos de pagamento (auditoria)
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `payment_logs` (
    `id`             INT(11)         NOT NULL AUTO_INCREMENT,
    `transaction_id` VARCHAR(36)     NOT NULL,
    `evento`         VARCHAR(60)     NOT NULL COMMENT 'ex: checkout_criado, status_approved',
    `dados`          JSON            DEFAULT NULL,
    `nivel`          ENUM('info','warning','error') NOT NULL DEFAULT 'info',
    `created_at`     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_pl_transaction` (`transaction_id`),
    KEY `idx_pl_evento` (`evento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ─────────────────────────────────────────────────────────────────────────────
-- 4. Logs de webhooks recebidos (rastreabilidade + reprocessamento)
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `webhook_logs` (
    `id`                INT(11)         NOT NULL AUTO_INCREMENT,
    `gateway`           VARCHAR(30)     NOT NULL,
    `transaction_id`    VARCHAR(36)     DEFAULT NULL,
    `gateway_reference` VARCHAR(255)    DEFAULT NULL,
    `payload`           JSON            DEFAULT NULL,
    `headers`           JSON            DEFAULT NULL,
    `status`            VARCHAR(30)     DEFAULT NULL,
    `processado`        TINYINT(1)      NOT NULL DEFAULT 0,
    `erro`              TEXT            DEFAULT NULL,
    `created_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_wl_gateway_ref` (`gateway_reference`),
    KEY `idx_wl_transaction` (`transaction_id`),
    KEY `idx_wl_processado` (`processado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ─────────────────────────────────────────────────────────────────────────────
-- 5. Proteção: impede pagamento duplicado aprovado na tabela legada
-- ─────────────────────────────────────────────────────────────────────────────
DROP TRIGGER IF EXISTS `trg_pagamento_unico_pago`;
DELIMITER $$
CREATE TRIGGER `trg_pagamento_unico_pago`
BEFORE INSERT ON `pagamentos`
FOR EACH ROW
BEGIN
    IF NEW.status_pagamento = 'pago' THEN
        IF EXISTS (
            SELECT 1 FROM pagamentos
            WHERE agendamento_id   = NEW.agendamento_id
              AND status_pagamento = 'pago'
        ) THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Agendamento já possui pagamento confirmado.';
        END IF;
    END IF;
END$$
DELIMITER ;

-- ─────────────────────────────────────────────────────────────────────────────
-- 6. Proteção: impede aprovação dupla de transação via payment_transactions
-- ─────────────────────────────────────────────────────────────────────────────
DROP TRIGGER IF EXISTS `trg_transaction_unica_aprovada`;
DELIMITER $$
CREATE TRIGGER `trg_transaction_unica_aprovada`
BEFORE UPDATE ON `payment_transactions`
FOR EACH ROW
BEGIN
    IF NEW.status = 'approved' AND OLD.status != 'approved' THEN
        IF EXISTS (
            SELECT 1 FROM payment_transactions
            WHERE agendamento_id = NEW.agendamento_id
              AND status         = 'approved'
              AND id            != NEW.id
        ) THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Agendamento já possui transação aprovada.';
        END IF;
    END IF;
END$$
DELIMITER ;

-- ─────────────────────────────────────────────────────────────────────────────
-- 7. Índices adicionais na tabela legada
-- ─────────────────────────────────────────────────────────────────────────────
ALTER TABLE `pagamentos`
    ADD INDEX `idx_pag_status` (`status_pagamento`),
    ADD INDEX `idx_pag_created` (`created_at`);

-- ─────────────────────────────────────────────────────────────────────────────
-- 8. View consolidada para relatórios financeiros (admin)
-- ─────────────────────────────────────────────────────────────────────────────
CREATE OR REPLACE VIEW `vw_financeiro` AS
SELECT
    pt.transaction_id,
    pt.gateway,
    pt.gateway_reference,
    pt.amount,
    pt.currency,
    pt.status                       AS status_transacao,
    pt.paid_at,
    pt.created_at,
    a.id                            AS agendamento_id,
    a.data_agendamento,
    a.hora_inicio,
    a.status                        AS status_agendamento,
    s.nome                          AS servico,
    s.valor                         AS valor_servico,
    u_cli.nome                      AS cliente,
    u_cli.email                     AS cliente_email,
    u_prof.nome                     AS profissional
FROM payment_transactions pt
JOIN agendamentos   a      ON pt.agendamento_id  = a.id
JOIN servicos       s      ON a.servico_id        = s.id
JOIN clientes       c      ON a.cliente_id        = c.id
JOIN usuarios       u_cli  ON c.usuario_id        = u_cli.id
JOIN profissionais  pr     ON a.profissional_id   = pr.id
JOIN usuarios       u_prof ON pr.usuario_id       = u_prof.id;

-- ─────────────────────────────────────────────────────────────────────────────
-- Verificação rápida após migração:
-- SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()
--   AND TABLE_NAME IN ('payment_transactions','payment_logs','webhook_logs');
-- SELECT COLUMN_NAME, COLUMN_TYPE FROM information_schema.COLUMNS
--   WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'agendamentos' AND COLUMN_NAME = 'status';
-- ─────────────────────────────────────────────────────────────────────────────
