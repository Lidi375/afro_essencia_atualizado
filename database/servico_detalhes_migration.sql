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
