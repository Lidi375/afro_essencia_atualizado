-- ═══════════════════════════════════════════════════════════════════════════════
-- MIGRAÇÃO — Sistema de Pagamento Automatizado — Afro Essência v2
-- Execute este script NO BANCO EXISTENTE caso já tenha a tabela `pagamentos`.
-- Se estiver usando o afro_essencia.sql do zero, as colunas já existem.
-- ═══════════════════════════════════════════════════════════════════════════════

-- 1. Garante que status_pagamento aceite 'pendente' (já está no schema original)
--    Nenhuma alteração necessária no enum ('pendente','pago','estornado').

-- 2. Índice para acelerar buscas por agendamento_id + status (novo fluxo)
-- Índices de pagamento (compatível MySQL 5.5+)
SET @idx1 = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='pagamentos' AND INDEX_NAME='idx_pag_status');
SET @sql1 = IF(@idx1=0, 'ALTER TABLE `pagamentos` ADD INDEX `idx_pag_status` (`status_pagamento`)', 'SELECT 1');
PREPARE stmt FROM @sql1; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx2 = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='pagamentos' AND INDEX_NAME='idx_pag_created');
SET @sql2 = IF(@idx2=0, 'ALTER TABLE `pagamentos` ADD INDEX `idx_pag_created` (`created_at`)', 'SELECT 1');
PREPARE stmt FROM @sql2; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 3. Previne pagamentos duplicados 'pago' por agendamento
--    (um agendamento pode ter vários 'pendente'/'estornado', mas apenas um 'pago')
-- Cria trigger de proteção (substitui se existir)
DROP TRIGGER IF EXISTS `trg_pagamento_unico_pago`;
DELIMITER $$
CREATE TRIGGER `trg_pagamento_unico_pago`
BEFORE INSERT ON `pagamentos`
FOR EACH ROW
BEGIN
    IF NEW.status_pagamento = 'pago' THEN
        IF EXISTS (
            SELECT 1 FROM pagamentos
            WHERE agendamento_id    = NEW.agendamento_id
              AND status_pagamento  = 'pago'
        ) THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Este agendamento já possui um pagamento confirmado.';
        END IF;
    END IF;
END$$
DELIMITER ;

-- 4. View auxiliar para relatórios financeiros
CREATE OR REPLACE VIEW `vw_pagamentos_detalhado` AS
SELECT
    p.id                AS pagamento_id,
    p.metodo_pagamento,
    p.valor,
    p.status_pagamento,
    p.data_pagamento,
    p.created_at,
    a.id                AS agendamento_id,
    a.data_agendamento,
    a.hora_inicio,
    a.status            AS status_agendamento,
    s.nome              AS servico,
    s.valor             AS valor_servico,
    u_cli.nome          AS cliente,
    u_prof.nome         AS profissional
FROM pagamentos p
JOIN agendamentos  a     ON p.agendamento_id  = a.id
JOIN servicos      s     ON a.servico_id      = s.id
JOIN clientes      c     ON a.cliente_id      = c.id
JOIN usuarios      u_cli ON c.usuario_id      = u_cli.id
JOIN profissionais pr    ON a.profissional_id = pr.id
JOIN usuarios      u_prof ON pr.usuario_id    = u_prof.id;

-- ═══════════════════════════════════════════════════════════════════════════════
-- Verificação rápida após migração:
-- SELECT COUNT(*) FROM pagamentos WHERE status_pagamento = 'pago';
-- SELECT * FROM vw_pagamentos_detalhado LIMIT 5;
-- ═══════════════════════════════════════════════════════════════════════════════
