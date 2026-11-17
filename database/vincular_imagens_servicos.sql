-- ============================================================
-- Vincula as imagens aos serviços no banco
-- Execute após subir os arquivos para public/imagens/
-- ============================================================
USE `afro_essencia`;

UPDATE servicos SET imagem = 'boxbraids.png'          WHERE slug = 'box-braids';
UPDATE servicos SET imagem = 'nago.png'         WHERE slug = 'trancas-nago';
UPDATE servicos SET imagem = 'twist.png'                WHERE slug = 'twist';
UPDATE servicos SET imagem = 'dreadlocks.png'           WHERE slug = 'dreadlocks';
UPDATE servicos SET imagem = 'hidratacao.png'   WHERE slug = 'hidratacao-capilar';
UPDATE servicos SET imagem = 'corte-afro.png'           WHERE slug = 'corte-afro';
UPDATE servicos SET imagem = 'coloracao.png'    WHERE slug = 'coloracao-natural';
UPDATE servicos SET imagem = 'penteado.png'  WHERE slug = 'penteado-para-festa';

SELECT id, nome, slug, imagem FROM servicos ORDER BY id;
