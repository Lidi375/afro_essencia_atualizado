-- ============================================================
-- Migração: Trabalhe Conosco — Candidaturas de Profissionais
-- Versão: 1.0  |  Data: 2026-06
-- ============================================================
-- Execute este script após o afro_essencia.sql principal.
-- Seguro para rodar múltiplas vezes (IF NOT EXISTS / IF EXISTS).
-- ============================================================

USE `afro_essencia`;

SET FOREIGN_KEY_CHECKS = 0;

-- ─────────────────────────────────────────────────────────────
-- Tabela principal de candidaturas
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `candidaturas_profissionais` (
  `id`                   int(11)      NOT NULL AUTO_INCREMENT,
  `nome`                 varchar(120) NOT NULL,
  `email`                varchar(180) NOT NULL,
  `telefone`             varchar(20)  NOT NULL,
  `cidade`               varchar(80)  NOT NULL,
  `estado`               char(2)      NOT NULL,
  `especialidade`        varchar(120) NOT NULL,
  `experiencia_anos`     int(3)       NOT NULL DEFAULT 0,
  `descricao`            text         NOT NULL,
  `instagram`            varchar(80)  DEFAULT NULL,
  `portfolio_url`        varchar(255) DEFAULT NULL,
  `arquivo_curriculo`    varchar(500) DEFAULT NULL
                           COMMENT 'Caminho absoluto no servidor',
  `arquivo_certificado`  varchar(500) DEFAULT NULL
                           COMMENT 'Caminho absoluto no servidor',
  `status`               enum('pendente','em_analise','aprovado','reprovado')
                           NOT NULL DEFAULT 'pendente',
  `created_at`           timestamp    NOT NULL DEFAULT current_timestamp(),
  `updated_at`           timestamp    NOT NULL DEFAULT current_timestamp()
                           ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_cand_email`   (`email`),
  KEY `idx_cand_status`  (`status`),
  KEY `idx_cand_created` (`created_at`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_general_ci
  COMMENT='Candidaturas do módulo Trabalhe Conosco';

-- ─────────────────────────────────────────────────────────────
-- Tabela de histórico de análise
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `candidaturas_historico` (
  `id`               int(11)      NOT NULL AUTO_INCREMENT,
  `candidatura_id`   int(11)      NOT NULL,
  `status_anterior`  varchar(20)  NOT NULL,
  `status_novo`      varchar(20)  NOT NULL,
  `observacao`       text         DEFAULT NULL,
  `admin_id`         int(11)      DEFAULT NULL
                       COMMENT 'Usuário admin que realizou a ação',
  `created_at`       timestamp    NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_hist_cand_id`   (`candidatura_id`),
  KEY `fk_hist_admin_id`  (`admin_id`),
  CONSTRAINT `fk_hist_cand_id`
    FOREIGN KEY (`candidatura_id`)
    REFERENCES `candidaturas_profissionais` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_hist_admin_id`
    FOREIGN KEY (`admin_id`)
    REFERENCES `usuarios` (`id`)
    ON DELETE SET NULL
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_general_ci
  COMMENT='Histórico de mudanças de status das candidaturas';

-- ─────────────────────────────────────────────────────────────
-- Diretório de uploads — garantia de existência via SQL
-- (O PHP cria o diretório físico; este comentário documenta o path)
-- Path: /afro_essencia/uploads/candidaturas/
-- Protegido por .htaccess gerado automaticamente pelo CandidaturaService
-- ─────────────────────────────────────────────────────────────

SET FOREIGN_KEY_CHECKS = 1;

-- ─────────────────────────────────────────────────────────────
-- Verificação pós-migração
-- ─────────────────────────────────────────────────────────────
SELECT
  TABLE_NAME,
  TABLE_ROWS,
  CREATE_TIME
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'afro_essencia'
  AND TABLE_NAME IN ('candidaturas_profissionais', 'candidaturas_historico');

-- ─────────────────────────────────────────────────────────────
-- FIM DA MIGRAÇÃO
-- ─────────────────────────────────────────────────────────────
