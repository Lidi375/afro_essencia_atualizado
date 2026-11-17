-- ═══════════════════════════════════════════════════════════════════════
-- banco_completo.sql — AFRO ESSÊNCIA
-- Arquivo único com TODO o banco de dados: schema base + todas as
-- migrações, na ordem correta. Rode só este arquivo, uma vez.
--
-- O arquivo já cria o banco "afro_essencia" sozinho (CREATE DATABASE IF
-- NOT EXISTS + USE), então basta rodar direto, sem precisar criar nada
-- antes:
--
--   mysql -u seu_usuario -p < banco_completo.sql
--
-- ATENÇÃO: as tabelas são recriadas (DROP + CREATE) durante a importação.
-- Se você já tem um banco "afro_essencia" em produção com dados reais,
-- faça um backup antes de rodar este arquivo.
-- ═══════════════════════════════════════════════════════════════════════

-- ───────── 1. SCHEMA BASE (afro_essencia.sql) ─────────
-- ============================================================
-- Afro Essência — SQL completo e corrigido para XAMPP/MariaDB
-- Versão: produção 2026
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;

-- ─────────────────────────────────────────────────────────────
-- Cria (ou usa) o banco
-- ─────────────────────────────────────────────────────────────
CREATE DATABASE IF NOT EXISTS `afro_essencia`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_general_ci;

USE `afro_essencia`;

-- ─────────────────────────────────────────────────────────────
-- Desativa FKs para importação limpa
-- ─────────────────────────────────────────────────────────────
SET FOREIGN_KEY_CHECKS = 0;

-- ─────────────────────────────────────────────────────────────
-- TABELAS (ordem segura: sem FKs primeiro)
-- ─────────────────────────────────────────────────────────────

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
  `id`        int(11)      NOT NULL AUTO_INCREMENT,
  `email`     varchar(180) NOT NULL,
  `token`     varchar(64)  NOT NULL,
  `expira_em` datetime     NOT NULL,
  `usado`     tinyint(1)   NOT NULL DEFAULT 0,
  `created_at` timestamp   NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `idx_token` (`token`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `categorias`;
CREATE TABLE `categorias` (
  `id`         int(11)     NOT NULL AUTO_INCREMENT,
  `nome`       varchar(80) NOT NULL,
  `descricao`  text        DEFAULT NULL,
  `created_at` timestamp   NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `categorias` (`id`, `nome`) VALUES
(1, 'Tranças'),
(2, 'Dreads'),
(3, 'Tratamentos Capilares'),
(4, 'Cortes Afro'),
(5, 'Coloração'),
(6, 'Penteados');

DROP TABLE IF EXISTS `especialidades`;
CREATE TABLE `especialidades` (
  `id`   int(11)     NOT NULL AUTO_INCREMENT,
  `nome` varchar(80) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id`           int(11)      NOT NULL AUTO_INCREMENT,
  `nome`         varchar(120) NOT NULL,
  `email`        varchar(180) NOT NULL,
  `senha`        varchar(255) NOT NULL,
  `tipo_usuario` enum('admin','profissional','cliente') NOT NULL DEFAULT 'cliente',
  `telefone`     varchar(20)  DEFAULT NULL,
  `foto`         varchar(255) DEFAULT NULL,
  `status`       enum('ativo','inativo') NOT NULL DEFAULT 'ativo',
  `created_at`   timestamp    NOT NULL DEFAULT current_timestamp(),
  `updated_at`   timestamp    NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Senhas de teste = "password" (hash bcrypt)
INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `tipo_usuario`, `status`) VALUES
(1, 'Administradora',  'admin@teste.com',         '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin',         'ativo'),
(2, 'Lara Oliveira',   'profissional@teste.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'profissional',  'ativo'),
(3, 'Maria Silva',     'cliente@teste.com',        '$2y$10$wrb8sNd0QB0zvSZwRPuou.jF5WKCHNp12CLeASV9gavVDxmTKL7mK', 'cliente',       'ativo'),
(4, 'João Santos',     'joao@teste.com',           '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'profissional',  'ativo'),
(5, 'Ana Ferreira',    'ana@teste.com',            '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente',       'ativo'),
(6, 'Pedro Alves',     'pedro@teste.com',          '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente',       'ativo');

DROP TABLE IF EXISTS `clientes`;
CREATE TABLE `clientes` (
  `id`              int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id`      int(11) NOT NULL,
  `data_nascimento` date    DEFAULT NULL,
  `instagram`       varchar(80) DEFAULT NULL,
  `observacoes`     text    DEFAULT NULL,
  `created_at`      timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_cliente_usuario` (`usuario_id`),
  CONSTRAINT `fk_cliente_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `clientes` (`id`, `usuario_id`) VALUES
(1, 3),
(2, 5),
(3, 6);

DROP TABLE IF EXISTS `profissionais`;
CREATE TABLE `profissionais` (
  `id`                int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id`        int(11) NOT NULL,
  `biografia`         text    DEFAULT NULL,
  `experiencia_anos`  int(11) NOT NULL DEFAULT 0,
  `atende_domicilio`  tinyint(1) NOT NULL DEFAULT 0,
  `avaliacao_media`   decimal(3,1) NOT NULL DEFAULT 0.0,
  `status`            enum('ativo','inativo') NOT NULL DEFAULT 'ativo',
  `created_at`        timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_profissional_usuario` (`usuario_id`),
  CONSTRAINT `fk_profissional_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `profissionais` (`id`, `usuario_id`, `biografia`, `experiencia_anos`, `status`) VALUES
(1, 2, 'Especialista em tranças afro com mais de 5 anos de experiência. Apaixonada por realçar a beleza natural de cada cliente.', 5, 'ativo'),
(2, 4, 'Especialista em tranças modernas e rápidas.', 3, 'ativo');

DROP TABLE IF EXISTS `profissional_especialidade`;
CREATE TABLE `profissional_especialidade` (
  `profissional_id`  int(11) NOT NULL,
  `especialidade_id` int(11) NOT NULL,
  PRIMARY KEY (`profissional_id`, `especialidade_id`),
  KEY `fk_pe_especialidade` (`especialidade_id`),
  CONSTRAINT `fk_pe_especialidade` FOREIGN KEY (`especialidade_id`) REFERENCES `especialidades` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pe_profissional`  FOREIGN KEY (`profissional_id`)  REFERENCES `profissionais` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `servicos`;
CREATE TABLE `servicos` (
  `id`                   int(11)        NOT NULL AUTO_INCREMENT,
  `categoria_id`         int(11)        DEFAULT NULL,
  `nome`                 varchar(120)   NOT NULL,
  `slug`                 varchar(160)   DEFAULT NULL,
  `descricao`            text           DEFAULT NULL,
  `imagem`               varchar(255)   DEFAULT NULL,
  `beneficios`           text           DEFAULT NULL,
  `tempo_duracao_texto`  varchar(120)   DEFAULT NULL,
  `manutencao`           varchar(200)   DEFAULT NULL,
  `cuidados_pos_servico` text           DEFAULT NULL,
  `valor`                decimal(10,2)  NOT NULL DEFAULT 0.00,
  `duracao_minutos`      int(11)        NOT NULL DEFAULT 60,
  `status`               enum('ativo','inativo') NOT NULL DEFAULT 'ativo',
  `created_at`           timestamp      NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_servicos_slug` (`slug`),
  KEY `fk_servico_categoria` (`categoria_id`),
  CONSTRAINT `fk_servico_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `servicos` (`id`, `categoria_id`, `nome`, `slug`, `descricao`, `beneficios`, `tempo_duracao_texto`, `manutencao`, `cuidados_pos_servico`, `valor`, `duracao_minutos`, `status`) VALUES
(1,  1, 'Box Braids',         'box-braids',         'Técnica africana que divide o cabelo em seções e trança os fios do couro cabeludo às pontas, criando tranças independentes e versáteis.', 'Protege os fios do manuseio diário; estimula o crescimento capilar; dura de 4 a 8 semanas; variedade de espessuras e comprimentos.', '3 a 6 horas (varia com comprimento e espessura)', 'Retoque a cada 6–8 semanas', 'Hidrate o couro cabeludo com óleo leve a cada 2–3 dias. Durma com touca de cetim. Evite molhar nas primeiras 24 horas.', 180.00, 180, 'ativo'),
(2,  1, 'Tranças Nagô',       'trancas-nago',        'Tranças rasas feitas diretamente no couro cabeludo, percorrendo o crânio em fileiras retas ou curvas, criando um visual elegante e estruturado.', 'Visual limpo e sofisticado; proteção total dos fios; dura até 6 semanas; adequada para todas as idades.', '2 a 4 horas', 'Retoque a cada 4–6 semanas', 'Hidrate com spray de água e glicerina 3 vezes por semana. Use touca de cetim ao dormir. Não lave nas primeiras 48 horas.', 150.00, 150, 'ativo'),
(3,  1, 'Twist',              'twist',               'Técnica que divide o cabelo em seções e enrola dois mechões em direções opostas, criando tranças soltas que realçam a textura natural dos cachos.', 'Define os cachos naturais; baixo manuseio dos fios; pode ser desfeito sem danos; resultado volumoso e natural.', '1,5 a 3 horas', 'Refaça a cada 2–4 semanas', 'Não desfaça o twist antes de secar completamente. Hidrate com creme leave-in leve. Durma com touca de cetim.', 120.00, 120, 'ativo'),
(4,  2, 'Dreadlocks',         'dreadlocks',          'Técnica que permite que os fios se entrelacem naturalmente, formando mechas compactas e duradouras de comprimentos variados.', 'Permanente e de longa duração; expressão cultural profunda; baixíssimo manuseio no dia a dia; fortalece a identidade afro.', '4 a 8 horas (criação) · 1 a 2 horas (manutenção)', 'Manutenção das raízes a cada 2–3 meses', 'Aguarde 72 horas antes da primeira lavagem. Seque completamente com secador. Hidrate as pontas com manteiga de karité.', 200.00, 240, 'ativo'),
(5,  3, 'Hidratação Capilar', 'hidratacao-capilar',  'Tratamento intensivo que repõe a água e nutrientes dos fios, restaurando a elasticidade, o brilho e a maciez dos cabelos crespos e cacheados.', 'Reduz quebra e ressecamento; devolve elasticidade; intensifica a definição dos cachos.', '1 hora', 'A cada 15 dias (cabelos ressecados) ou mensal (manutenção)', 'Evite lavar o cabelo nas 48 horas seguintes. Não use secador em alta temperatura. Mantenha a umidade com leave-in leve.', 80.00, 60, 'ativo'),
(6,  4, 'Corte Afro',         'corte-afro',         'Modelagem especializada do cabelo afro natural, respeitando a estrutura e a textura única de cada tipo de fio, do 3C ao 4C.', 'Define e uniformiza o volume; exalta a beleza natural; remove pontas danificadas.', '45 minutos a 1 hora', 'A cada 2–3 meses conforme crescimento', 'Hidrate o cabelo no dia seguinte ao corte. Use finalizador para definir os cachos. Evite usar pente fino nos primeiros dias.', 70.00, 45, 'ativo'),
(7,  5, 'Coloração Natural',   'coloracao-natural',   'Aplicação de cor com produtos premium formulados para cabelos afro, preservando a saúde do fio enquanto entrega resultado vibrante e duradouro.', 'Produtos sem amônia disponíveis; longa duração da cor; feito por especialista em fios afro.', '1,5 a 2,5 horas (varia com técnica)', 'Retoque a cada 6–8 semanas', 'Não lave o cabelo nas primeiras 72 horas. Use shampoo para cabelo colorido. Evite exposição prolongada ao sol sem proteção UV.', 130.00, 90, 'ativo'),
(8,  6, 'Penteado para Festa', 'penteado-para-festa', 'Criação de penteados elaborados para casamentos, formaturas, festas e eventos especiais, valorizando a identidade e o estilo único de cada cliente.', 'Penteado exclusivo e personalizado; dura todo o evento; possibilidade de acessórios e adornos.', '2 a 3 horas', 'Sob demanda (evento específico)', 'Remova os acessórios com cuidado. Desfaça o penteado com os fios levemente úmidos. Hidrate após a remoção.', 160.00, 120, 'ativo');

DROP TABLE IF EXISTS `servico_profissional`;
CREATE TABLE `servico_profissional` (
  `id`               int(11) NOT NULL AUTO_INCREMENT,
  `profissional_id`  int(11) NOT NULL,
  `servico_id`       int(11) NOT NULL,
  `duracao_minutos`  int(11) DEFAULT NULL,
  `valor_customizado` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `profissional_id` (`profissional_id`),
  KEY `servico_id` (`servico_id`),
  CONSTRAINT `sp_profissional` FOREIGN KEY (`profissional_id`) REFERENCES `profissionais` (`id`),
  CONSTRAINT `sp_servico`      FOREIGN KEY (`servico_id`)      REFERENCES `servicos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `agendamentos`;
CREATE TABLE `agendamentos` (
  `id`               int(11)  NOT NULL AUTO_INCREMENT,
  `cliente_id`       int(11)  NOT NULL,
  `profissional_id`  int(11)  NOT NULL,
  `servico_id`       int(11)  NOT NULL,
  `data_agendamento` date     NOT NULL,
  `hora_inicio`      time     NOT NULL,
  `hora_fim`         time     NOT NULL,
  `status`           enum('pendente','confirmado','em_andamento','concluido','cancelado') NOT NULL DEFAULT 'pendente',
  `observacoes`      text     DEFAULT NULL,
  `valor_final`      decimal(10,2) DEFAULT NULL,
  `oculto_cliente`   tinyint(1)    NOT NULL DEFAULT 0,
  `created_at`       timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_ag_cliente`      (`cliente_id`),
  KEY `fk_ag_profissional` (`profissional_id`),
  KEY `fk_ag_servico`      (`servico_id`),
  CONSTRAINT `fk_ag_cliente`      FOREIGN KEY (`cliente_id`)      REFERENCES `clientes` (`id`),
  CONSTRAINT `fk_ag_profissional` FOREIGN KEY (`profissional_id`) REFERENCES `profissionais` (`id`),
  CONSTRAINT `fk_ag_servico`      FOREIGN KEY (`servico_id`)      REFERENCES `servicos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Agendamentos de exemplo (datas futuras para aparecerem como próximos)
INSERT INTO `agendamentos` (`cliente_id`, `profissional_id`, `servico_id`, `data_agendamento`, `hora_inicio`, `hora_fim`, `status`) VALUES
(1, 1, 1, DATE_ADD(CURDATE(), INTERVAL 3 DAY),  '10:00:00', '13:00:00', 'pendente'),
(1, 2, 5, DATE_ADD(CURDATE(), INTERVAL 5 DAY),  '14:00:00', '15:00:00', 'pendente'),
(2, 1, 2, DATE_ADD(CURDATE(), INTERVAL 7 DAY),  '09:00:00', '11:30:00', 'pendente'),
(1, 1, 3, DATE_SUB(CURDATE(), INTERVAL 2 DAY),  '11:00:00', '13:00:00', 'concluido'),
(2, 2, 6, DATE_SUB(CURDATE(), INTERVAL 5 DAY),  '14:00:00', '14:45:00', 'concluido');

DROP TABLE IF EXISTS `pagamentos`;
CREATE TABLE `pagamentos` (
  `id`                int(11)       NOT NULL AUTO_INCREMENT,
  `agendamento_id`    int(11)       NOT NULL,
  `metodo_pagamento`  enum('pix','cartao','dinheiro','transferencia') NOT NULL,
  `valor`             decimal(10,2) NOT NULL,
  `status_pagamento`  enum('pendente','pago','estornado') NOT NULL DEFAULT 'pago',
  `data_pagamento`    datetime      DEFAULT NULL,
  `created_at`        timestamp     NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_pag_agendamento` (`agendamento_id`),
  CONSTRAINT `fk_pag_agendamento` FOREIGN KEY (`agendamento_id`) REFERENCES `agendamentos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `anamneses`;
CREATE TABLE `anamneses` (
  `id`                  int(11) NOT NULL AUTO_INCREMENT,
  `cliente_id`          int(11) NOT NULL,
  `tipo_cabelo`         varchar(60)  DEFAULT NULL,
  `subtipo_cabelo`      varchar(10)  DEFAULT NULL,
  `nivel_hidratacao`    varchar(40)  DEFAULT NULL,
  `comprimento`         varchar(30)  DEFAULT NULL,
  `densidade`           varchar(30)  DEFAULT NULL,
  `usa_quimica`         tinyint(1)   DEFAULT 0,
  `tipo_quimica`        varchar(100) DEFAULT NULL,
  `ultima_quimica`      varchar(60)  DEFAULT NULL,
  `frequencia_lavagem`  varchar(40)  DEFAULT NULL,
  `usa_calor`           tinyint(1)   DEFAULT 0,
  `frequencia_calor`    varchar(40)  DEFAULT NULL,
  `problemas_couro`     varchar(255) DEFAULT NULL,
  `alergias`            text         DEFAULT NULL,
  `objetivo`            varchar(255) DEFAULT NULL,
  `servicos_interesse`  text         DEFAULT NULL,
  `observacoes`         text         DEFAULT NULL,
  `created_at`          timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at`          timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_anamnese_cliente` (`cliente_id`),
  CONSTRAINT `fk_anamnese_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `avaliacoes_capilares`;
CREATE TABLE `avaliacoes_capilares` (
  `id`               int(11) NOT NULL AUTO_INCREMENT,
  `cliente_id`       int(11) NOT NULL,
  `tipo_cabelo`      varchar(80) DEFAULT NULL,
  `nivel_hidratacao` varchar(80) DEFAULT NULL,
  `observacoes`      text        DEFAULT NULL,
  `created_at`       timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_ac_cliente` (`cliente_id`),
  CONSTRAINT `fk_ac_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `avaliacoes_profissionais`;
CREATE TABLE `avaliacoes_profissionais` (
  `id`              int(11)    NOT NULL AUTO_INCREMENT,
  `agendamento_id`  int(11)    NOT NULL,
  `profissional_id` int(11)    NOT NULL,
  `cliente_id`      int(11)    NOT NULL,
  `nota`            tinyint(4) NOT NULL CHECK (`nota` BETWEEN 1 AND 5),
  `comentario`      text       DEFAULT NULL,
  `created_at`      timestamp  NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_av_agendamento`  (`agendamento_id`),
  KEY `fk_av_profissional` (`profissional_id`),
  KEY `fk_av_cliente`      (`cliente_id`),
  CONSTRAINT `fk_av_agendamento`  FOREIGN KEY (`agendamento_id`)  REFERENCES `agendamentos` (`id`),
  CONSTRAINT `fk_av_profissional` FOREIGN KEY (`profissional_id`) REFERENCES `profissionais` (`id`),
  CONSTRAINT `fk_av_cliente`      FOREIGN KEY (`cliente_id`)      REFERENCES `clientes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `notificacoes`;
CREATE TABLE `notificacoes` (
  `id`         int(11)    NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11)    NOT NULL,
  `titulo`     varchar(120) NOT NULL,
  `mensagem`   text       DEFAULT NULL,
  `lida`       tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp  NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_notif_usuario` (`usuario_id`),
  CONSTRAINT `fk_notif_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `horarios`;
CREATE TABLE `horarios` (
  `id`              int(11)     NOT NULL AUTO_INCREMENT,
  `profissional_id` int(11)     NOT NULL,
  `dia_semana`      tinyint(1)  NOT NULL COMMENT '0=Dom, 1=Seg ... 6=Sab',
  `hora_inicio`     time        NOT NULL,
  `hora_fim`        time        NOT NULL,
  `created_at`      timestamp   NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_horario_profissional` (`profissional_id`),
  CONSTRAINT `fk_horario_profissional` FOREIGN KEY (`profissional_id`) REFERENCES `profissionais` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `galerias`;
CREATE TABLE `galerias` (
  `id`         int(11)      NOT NULL AUTO_INCREMENT,
  `titulo`     varchar(120) DEFAULT NULL,
  `imagem`     varchar(255) NOT NULL,
  `created_at` timestamp    NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `historico_agendamentos`;
CREATE TABLE `historico_agendamentos` (
  `id`             int(11)     NOT NULL AUTO_INCREMENT,
  `agendamento_id` int(11)     NOT NULL,
  `status_anterior` varchar(30) DEFAULT NULL,
  `status_novo`    varchar(30) DEFAULT NULL,
  `observacao`     text        DEFAULT NULL,
  `created_at`     timestamp   NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_hist_agendamento` (`agendamento_id`),
  CONSTRAINT `fk_hist_agendamento` FOREIGN KEY (`agendamento_id`) REFERENCES `agendamentos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ─────────────────────────────────────────────────────────────
-- Reativa FKs
-- ─────────────────────────────────────────────────────────────
SET FOREIGN_KEY_CHECKS = 1;

-- ─────────────────────────────────────────────────────────────
-- SCRIPT DE MIGRAÇÃO — execute se já tiver o banco antigo
-- ─────────────────────────────────────────────────────────────
-- ALTER TABLE `profissionais` ADD COLUMN IF NOT EXISTS `status` enum('ativo','inativo') NOT NULL DEFAULT 'ativo';
-- UPDATE `profissionais` SET `status` = 'ativo' WHERE `status` IS NULL OR `status` = '';

-- ───────── 2. servico_detalhes_migration.sql ─────────
-- ============================================================
-- Migration: servico_detalhes_migration.sql
-- Adiciona campos de conteúdo à tabela servicos
-- ✅ Compatível com MySQL 5.5+ e MariaDB 10+
-- Seguro para rodar múltiplas vezes (verifica existência antes)
-- ============================================================

USE `afro_essencia`;

-- Adicionar coluna 'slug' se não existir
SET @col_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'servicos' AND COLUMN_NAME = 'slug'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `servicos` ADD COLUMN `slug` varchar(160) DEFAULT NULL AFTER `nome`',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'servicos' AND COLUMN_NAME = 'imagem'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `servicos` ADD COLUMN `imagem` varchar(255) DEFAULT NULL AFTER `descricao`',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'servicos' AND COLUMN_NAME = 'beneficios'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `servicos` ADD COLUMN `beneficios` text DEFAULT NULL AFTER `imagem`',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'servicos' AND COLUMN_NAME = 'tempo_duracao_texto'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `servicos` ADD COLUMN `tempo_duracao_texto` varchar(120) DEFAULT NULL AFTER `beneficios`',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'servicos' AND COLUMN_NAME = 'manutencao'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `servicos` ADD COLUMN `manutencao` varchar(200) DEFAULT NULL AFTER `tempo_duracao_texto`',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'servicos' AND COLUMN_NAME = 'cuidados_pos_servico'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `servicos` ADD COLUMN `cuidados_pos_servico` text DEFAULT NULL AFTER `manutencao`',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Índice único no slug (seguro: só cria se não existir)
SET @idx_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'servicos' AND INDEX_NAME = 'idx_servicos_slug'
);
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `servicos` ADD UNIQUE INDEX `idx_servicos_slug` (`slug`)',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ── Slugs para os 8 serviços base ────────────────────────────
UPDATE `servicos` SET `slug` = 'box-braids'         WHERE `id` = 1 AND (`slug` IS NULL OR `slug` = '');
UPDATE `servicos` SET `slug` = 'trancas-nago'        WHERE `id` = 2 AND (`slug` IS NULL OR `slug` = '');
UPDATE `servicos` SET `slug` = 'twist'               WHERE `id` = 3 AND (`slug` IS NULL OR `slug` = '');
UPDATE `servicos` SET `slug` = 'dreadlocks'          WHERE `id` = 4 AND (`slug` IS NULL OR `slug` = '');
UPDATE `servicos` SET `slug` = 'hidratacao-capilar'  WHERE `id` = 5 AND (`slug` IS NULL OR `slug` = '');
UPDATE `servicos` SET `slug` = 'corte-afro'          WHERE `id` = 6 AND (`slug` IS NULL OR `slug` = '');
UPDATE `servicos` SET `slug` = 'coloracao-natural'   WHERE `id` = 7 AND (`slug` IS NULL OR `slug` = '');
UPDATE `servicos` SET `slug` = 'penteado-para-festa' WHERE `id` = 8 AND (`slug` IS NULL OR `slug` = '');

-- ── Conteúdo editorial para os 8 serviços ────────────────────
UPDATE `servicos` SET
    `descricao`            = 'Técnica africana que divide o cabelo em seções e trança os fios do couro cabeludo às pontas, criando tranças independentes e versáteis.',
    `beneficios`           = 'Protege os fios do manuseio diário; estimula o crescimento capilar; dura de 4 a 8 semanas; variedade de espessuras e comprimentos.',
    `tempo_duracao_texto`  = '3 a 6 horas (varia com comprimento e espessura)',
    `manutencao`           = 'Retoque a cada 6–8 semanas',
    `cuidados_pos_servico` = 'Hidrate o couro cabeludo com óleo leve (jojoba ou argan) a cada 2–3 dias. Durma com touca de cetim para evitar frizz e ressecamento. Evite molhar nas primeiras 24 horas. Não puxe nem force as raízes. Lave com shampoo diluído a cada 10–14 dias.'
WHERE `id` = 1 AND (`descricao` IS NULL OR `descricao` = '');

UPDATE `servicos` SET
    `descricao`            = 'Tranças rasas feitas diretamente no couro cabeludo, percorrendo o crânio em fileiras retas ou curvas, criando um visual elegante e estruturado.',
    `beneficios`           = 'Visual limpo e sofisticado; proteção total dos fios; dura até 6 semanas; adequada para todas as idades.',
    `tempo_duracao_texto`  = '2 a 4 horas',
    `manutencao`           = 'Retoque a cada 4–6 semanas',
    `cuidados_pos_servico` = 'Hidrate o couro cabeludo com spray de água e glicerina 3 vezes por semana. Use touca de cetim ao dormir. Evite tracionar os fios na raiz. Não lave nas primeiras 48 horas.'
WHERE `id` = 2 AND (`descricao` IS NULL OR `descricao` = '');

UPDATE `servicos` SET
    `descricao`            = 'Técnica que divide o cabelo em seções e enrola dois mechões em direções opostas, criando tranças soltas que realçam a textura natural dos cachos.',
    `beneficios`           = 'Define os cachos naturais; baixo manuseio dos fios; pode ser desfeito sem danos; resultado volumoso e natural.',
    `tempo_duracao_texto`  = '1,5 a 3 horas',
    `manutencao`           = 'Refaça a cada 2–4 semanas',
    `cuidados_pos_servico` = 'Não desfaça o twist antes de secar completamente. Hidrate com creme leave-in leve. Durma com touca de cetim. Evite tocar excessivamente para não criar frizz.'
WHERE `id` = 3 AND (`descricao` IS NULL OR `descricao` = '');

UPDATE `servicos` SET
    `descricao`            = 'Técnica que permite que os fios se entrelacem naturalmente ou com auxílio profissional, formando mechas compactas e duradouras de comprimentos variados.',
    `beneficios`           = 'Permanente e de longa duração; expressão cultural profunda; baixíssimo manuseio no dia a dia; fortalece a identidade afro.',
    `tempo_duracao_texto`  = '4 a 8 horas (criação) · 1 a 2 horas (manutenção)',
    `manutencao`           = 'Manutenção das raízes a cada 2–3 meses',
    `cuidados_pos_servico` = 'Aguarde 72 horas antes da primeira lavagem. Use shampoo específico para dreadlocks, diluído. Seque completamente com secador após cada lavagem. Hidrate as pontas com manteiga de karité.'
WHERE `id` = 4 AND (`descricao` IS NULL OR `descricao` = '');

UPDATE `servicos` SET
    `descricao`            = 'Tratamento intensivo que repõe a água e nutrientes dos fios, restaurando a elasticidade, o brilho e a maciez dos cabelos crespos e cacheados.',
    `beneficios`           = 'Reduz quebra e ressecamento; devolve elasticidade; intensifica a definição dos cachos; pode ser combinado com outros tratamentos.',
    `tempo_duracao_texto`  = '1 hora',
    `manutencao`           = 'A cada 15 dias (cabelos ressecados) ou mensal (manutenção)',
    `cuidados_pos_servico` = 'Evite lavar o cabelo nas 48 horas seguintes ao tratamento. Não use secador em alta temperatura. Mantenha a umidade com leave-in leve. Durma com touca de cetim.'
WHERE `id` = 5 AND (`descricao` IS NULL OR `descricao` = '');

UPDATE `servicos` SET
    `descricao`            = 'Modelagem especializada do cabelo afro natural, respeitando a estrutura e a textura única de cada tipo de fio, do 3C ao 4C.',
    `beneficios`           = 'Define e uniformiza o volume; exalta a beleza natural; remove pontas danificadas; feito por profissional especializado em texturas afro.',
    `tempo_duracao_texto`  = '45 minutos a 1 hora',
    `manutencao`           = 'A cada 2–3 meses conforme crescimento',
    `cuidados_pos_servico` = 'Hidrate o cabelo no dia seguinte ao corte. Use finalizador para definir os cachos. Evite usar pente fino ou escova nos primeiros dias. Durma com touca de cetim.'
WHERE `id` = 6 AND (`descricao` IS NULL OR `descricao` = '');

UPDATE `servicos` SET
    `descricao`            = 'Aplicação de cor com produtos premium formulados para cabelos afro, preservando a saúde do fio enquanto entrega resultado vibrante e duradouro.',
    `beneficios`           = 'Produtos sem amônia disponíveis; longa duração da cor; feito por especialista em fios afro; possibilidade de mechas, luzes e colorações completas.',
    `tempo_duracao_texto`  = '1,5 a 2,5 horas (varia com técnica)',
    `manutencao`           = 'Retoque a cada 6–8 semanas',
    `cuidados_pos_servico` = 'Não lave o cabelo nas primeiras 72 horas. Use shampoo e condicionador para cabelo colorido. Evite exposição prolongada ao sol sem proteção UV. Faça hidratação semanal.'
WHERE `id` = 7 AND (`descricao` IS NULL OR `descricao` = '');

UPDATE `servicos` SET
    `descricao`            = 'Criação de penteados elaborados para casamentos, formaturas, festas e eventos especiais, valorizando a identidade e o estilo único de cada cliente.',
    `beneficios`           = 'Penteado exclusivo e personalizado; dura todo o evento; atendimento especial com duração estendida; possibilidade de acessórios e adornos.',
    `tempo_duracao_texto`  = '2 a 3 horas',
    `manutencao`           = 'Sob demanda (evento específico)',
    `cuidados_pos_servico` = 'Remova os acessórios com cuidado para não puxar os fios. Desfaça o penteado com os fios levemente úmidos para facilitar. Hidrate após a remoção. Aplique óleo nas pontas para nutrir após uso de grampos.'
WHERE `id` = 8 AND (`descricao` IS NULL OR `descricao` = '');

SELECT 'Migration concluída com sucesso.' AS resultado;

-- ───────── 3. trabalhe_conosco_migration.sql ─────────
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

-- ───────── 4. pagamento_migracao.sql ─────────
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

-- ───────── 5. pagamento_gateway_migration.sql ─────────
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
-- (já criados de forma segura pela seção 4 — pagamento_migracao.sql —
--  removido aqui para não duplicar e quebrar a execução)
-- ─────────────────────────────────────────────────────────────────────────────

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

-- ───────── 6. vincular_imagens_servicos.sql ─────────
-- ============================================================
-- Vincula as imagens aos serviços no banco
-- Execute após subir os arquivos para public/imagens/
-- ============================================================
USE `afro_essencia`;

UPDATE servicos SET imagem = 'box-braids.jpg'          WHERE slug = 'box-braids';
UPDATE servicos SET imagem = 'trancas-nago.jpg'         WHERE slug = 'trancas-nago';
UPDATE servicos SET imagem = 'twist.jpg'                WHERE slug = 'twist';
UPDATE servicos SET imagem = 'dreadlocks.jpg'           WHERE slug = 'dreadlocks';
UPDATE servicos SET imagem = 'hidratacao-capilar.jpg'   WHERE slug = 'hidratacao-capilar';
UPDATE servicos SET imagem = 'corte-afro.jpg'           WHERE slug = 'corte-afro';
UPDATE servicos SET imagem = 'coloracao-natural.jpg'    WHERE slug = 'coloracao-natural';
UPDATE servicos SET imagem = 'penteado-para-festa.jpg'  WHERE slug = 'penteado-para-festa';

SELECT id, nome, slug, imagem FROM servicos ORDER BY id;

-- ───────── 7. comissoes_migration.sql (financeiro + login_attempts) ─────────
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
