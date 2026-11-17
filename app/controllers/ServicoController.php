<?php

class ServicoController extends BaseController
{
    // ── Admin: listagem ─────────────────────────────────────────
    public function index()
    {
        $this->middleware('admin');
        $servicos = (new ServicoDAO())->findAll();
        $this->view('dashboard/admin/servicos', ['servicos' => $servicos]);
    }

    public function create()
    {
        $this->middleware('admin');
        $this->redirect('dashboard/servicos');
    }

    // ── Admin: exibir formulário de cadastro ────────────────────
    // Rota GET: /servico/novo
    public function novo(): void
    {
        $this->middleware('admin');

        $categorias = (new ServicoDAO())->findCategorias();

        $erro     = $_SESSION['erro']    ?? null;
        $sucesso  = $_SESSION['success'] ?? null;
        unset($_SESSION['erro'], $_SESSION['success']);

        $this->view('servico/criar', [
            'categorias' => $categorias,
            'erro'       => $erro,
            'sucesso'    => $sucesso,
        ]);
    }

    // ── Admin: processar cadastro ───────────────────────────────
    // Rota POST: /servico/store
    public function store(): void
    {
        $this->middleware('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('servico/novo');
            return;
        }

        $this->verifyCsrf();

        try {
            (new ServicoService())->criar($_POST);
            $_SESSION['success'] = 'Serviço cadastrado com sucesso!';
        } catch (Exception $e) {
            $_SESSION['erro'] = $e->getMessage();
            $this->redirect('servico/novo');
            return;
        }

        $this->redirect('dashboard/servicos');
    }

    // ── Público: página de detalhes de um serviço ───────────────
    // Rota: /servico/show/{slug}
    public function show(string $slug = ''): void
    {
        $slug = preg_replace('/[^a-z0-9\-]/', '', strtolower(trim($slug)));

        if (empty($slug)) {
            http_response_code(404);
            echo '<h2>Serviço não encontrado.</h2>';
            return;
        }

        $dao     = new ServicoDAO();
        $servico = $dao->findBySlug($slug);

        if (!$servico) {
            // Pode ser que a migration ainda não foi executada ou slug não existe.
            // Tenta buscar pelo nome para dar melhor UX.
            http_response_code(404);
            echo '<!DOCTYPE html><html lang="pt-br"><head><meta charset="UTF-8">
                  <title>AFRO ESSÊNCIA | Serviço não encontrado</title>
                  <link rel="stylesheet" href="/afro_essencia/public/css/home.css"></head><body>
                  <div style="padding:120px 40px;text-align:center;">
                  <h2 style="font-family:Playfair Display,serif;color:#f0e8d8;margin-bottom:16px;">Serviço não encontrado</h2>
                  <p style="color:rgba(240,232,216,.6);margin-bottom:24px;">O serviço solicitado não existe ou não está disponível.</p>
                  <a href="/afro_essencia/home#servicos" style="color:#d4a96a;text-decoration:none;">← Ver todos os serviços</a>
                  </div></body></html>';
            return;
        }

        // Conteúdo editorial de fallback quando o campo ainda é NULL no banco
        $servico = $this->_preencherFallback($servico);

        $this->view('servico/show', ['servico' => $servico]);
    }

    // ─────────────────────────────────────────────────────────────
    // Conteúdo editorial padrão por serviço (fallback se NULL no BD)
    // Evita tela vazia enquanto o admin não preencher os campos
    // ─────────────────────────────────────────────────────────────
    private function _preencherFallback(array $s): array
    {
        $editorial = [
            'box-braids' => [
                'descricao'           => 'Técnica africana que divide o cabelo em seções e trança os fios do couro cabeludo às pontas, criando tranças independentes e versáteis.',
                'beneficios'          => 'Protege os fios do manuseio diário; estimula o crescimento capilar; dura de 4 a 8 semanas; variedade de espessuras e comprimentos.',
                'tempo_duracao_texto' => '3 a 6 horas',
                'manutencao'          => 'Retoque a cada 6–8 semanas',
                'cuidados_pos_servico'=> 'Hidrate o couro cabeludo com óleo leve a cada 2–3 dias. Durma com touca de cetim. Evite molhar nas primeiras 24 horas. Lave com shampoo diluído a cada 10–14 dias.',
            ],
            'trancas-nago' => [
                'descricao'           => 'Tranças rasas feitas diretamente no couro cabeludo, percorrendo o crânio em fileiras retas ou curvas, criando visual elegante e estruturado.',
                'beneficios'          => 'Visual limpo e sofisticado; proteção total dos fios; dura até 6 semanas; adequada para todas as idades.',
                'tempo_duracao_texto' => '2 a 4 horas',
                'manutencao'          => 'Retoque a cada 4–6 semanas',
                'cuidados_pos_servico'=> 'Hidrate com spray de água e glicerina 3 vezes por semana. Use touca de cetim ao dormir. Não lave nas primeiras 48 horas.',
            ],
            'twist' => [
                'descricao'           => 'Técnica que enrola dois mechões em direções opostas, criando tranças soltas que realçam a textura natural dos cachos.',
                'beneficios'          => 'Define os cachos naturais; baixo manuseio; pode ser desfeito sem danos; resultado volumoso e natural.',
                'tempo_duracao_texto' => '1,5 a 3 horas',
                'manutencao'          => 'Refaça a cada 2–4 semanas',
                'cuidados_pos_servico'=> 'Não desfaça antes de secar completamente. Hidrate com leave-in leve. Durma com touca de cetim. Evite tocar excessivamente.',
            ],
            'dreadlocks' => [
                'descricao'           => 'Técnica que permite que os fios se entrelacem naturalmente, formando mechas compactas e duradouras de comprimentos variados.',
                'beneficios'          => 'Permanente e duradouro; baixíssimo manuseio; fortalece a identidade afro; expressão cultural profunda.',
                'tempo_duracao_texto' => '4 a 8 horas (criação) · 1 a 2 horas (manutenção)',
                'manutencao'          => 'Manutenção das raízes a cada 2–3 meses',
                'cuidados_pos_servico'=> 'Aguarde 72 horas antes da primeira lavagem. Seque completamente com secador após cada lavagem. Hidrate as pontas com manteiga de karité.',
            ],
            'hidratacao-capilar' => [
                'descricao'           => 'Tratamento intensivo que repõe água e nutrientes dos fios, restaurando elasticidade, brilho e maciez dos cabelos crespos e cacheados.',
                'beneficios'          => 'Reduz quebra e ressecamento; devolve elasticidade; intensifica a definição dos cachos.',
                'tempo_duracao_texto' => '1 hora',
                'manutencao'          => 'A cada 15 dias (cabelos ressecados) ou mensal (manutenção)',
                'cuidados_pos_servico'=> 'Evite lavar nas 48 horas seguintes. Não use secador em alta temperatura. Mantenha a umidade com leave-in leve.',
            ],
            'corte-afro' => [
                'descricao'           => 'Modelagem especializada do cabelo afro natural, respeitando a estrutura e a textura única de cada fio, do 3C ao 4C.',
                'beneficios'          => 'Define e uniformiza o volume; exalta a beleza natural; remove pontas danificadas.',
                'tempo_duracao_texto' => '45 minutos a 1 hora',
                'manutencao'          => 'A cada 2–3 meses',
                'cuidados_pos_servico'=> 'Hidrate no dia seguinte. Use finalizador para definir os cachos. Evite pente fino nos primeiros dias. Durma com touca de cetim.',
            ],
            'coloracao-natural' => [
                'descricao'           => 'Aplicação de cor com produtos premium formulados para cabelos afro, preservando a saúde do fio enquanto entrega resultado vibrante e duradouro.',
                'beneficios'          => 'Produtos sem amônia disponíveis; longa duração da cor; profissional especialista em fios afro.',
                'tempo_duracao_texto' => '1,5 a 2,5 horas',
                'manutencao'          => 'Retoque a cada 6–8 semanas',
                'cuidados_pos_servico'=> 'Não lave nas primeiras 72 horas. Use shampoo para cabelo colorido. Evite exposição prolongada ao sol sem proteção UV.',
            ],
            'penteado-para-festa' => [
                'descricao'           => 'Criação de penteados elaborados para casamentos, formaturas e eventos especiais, valorizando a identidade e o estilo único de cada cliente.',
                'beneficios'          => 'Penteado exclusivo e personalizado; dura todo o evento; possibilidade de acessórios e adornos.',
                'tempo_duracao_texto' => '2 a 3 horas',
                'manutencao'          => 'Sob demanda (evento específico)',
                'cuidados_pos_servico'=> 'Remova acessórios com cuidado. Desfaça com os fios levemente úmidos. Hidrate após a remoção. Aplique óleo nas pontas para nutrir após uso de grampos.',
            ],
        ];

        $slug = $s['slug'] ?? '';

        foreach (['descricao', 'beneficios', 'tempo_duracao_texto', 'manutencao', 'cuidados_pos_servico'] as $campo) {
            if (empty($s[$campo]) && isset($editorial[$slug][$campo])) {
                $s[$campo] = $editorial[$slug][$campo];
            }
        }

        return $s;
    }
}
