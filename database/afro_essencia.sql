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
