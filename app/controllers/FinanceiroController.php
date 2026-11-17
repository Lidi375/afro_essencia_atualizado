<?php

/**
 * FinanceiroController — módulo financeiro / comissões do Afro Essência.
 *
 * Rotas admin:
 *   GET  /financeiro              → visão geral (dashboard)
 *   GET  /financeiro/comissoes    → lista de comissões
 *   GET  /financeiro/config       → configurar comissões
 *   POST /financeiro/salvarConfig → salvar configuração
 *   POST /financeiro/pagar        → marcar comissão como paga
 *   POST /financeiro/cancelar     → cancelar comissão
 *   GET  /financeiro/relatorio    → relatório admin
 *   GET  /financeiro/pdf          → exportar PDF admin
 *
 * Rotas profissional:
 *   GET  /financeiro/minhas       → minhas comissões
 *   GET  /financeiro/meusRelatorios → relatório profissional
 *   GET  /financeiro/meuPdf       → PDF profissional
 */
class FinanceiroController extends BaseController
{
    private ComissaoService $service;

    public function __construct()
    {
        $this->service = new ComissaoService();
    }

    // ═══════════════════════════════════════════════════════
    // ADMIN — Visão Geral
    // ═══════════════════════════════════════════════════════

    public function index()
    {
        $this->middleware('admin');

        $filtros = $this->filtrosFromRequest();

        $totais              = $this->service->totaisAdmin($filtros);
        $totaisProfissionais = $this->service->totaisPorProfissional($filtros);
        $evolucao            = $this->service->evolucaoMensal();
        $profissionaisDAO    = new ProfissionalDAO();
        $profissionais       = $profissionaisDAO->findAll();

        $this->view('financeiro/admin/index', [
            'totais'              => $totais,
            'totaisProfissionais' => $totaisProfissionais,
            'evolucao'            => $evolucao,
            'profissionais'       => $profissionais,
            'filtros'             => $filtros,
            'activePage'          => 'financeiro',
        ]);
    }

    // ═══════════════════════════════════════════════════════
    // ADMIN — Lista de comissões
    // ═══════════════════════════════════════════════════════

    public function comissoes()
    {
        $this->middleware('admin');

        $filtros       = $this->filtrosFromRequest();
        $comissoes     = $this->service->findAll($filtros);
        $profissionalDAO = new ProfissionalDAO();
        $profissionais = $profissionalDAO->findAll();

        $mensagem = $_SESSION['success'] ?? null;
        $erro     = $_SESSION['erro']    ?? null;
        unset($_SESSION['success'], $_SESSION['erro']);

        $this->view('financeiro/admin/comissoes', [
            'comissoes'     => $comissoes,
            'profissionais' => $profissionais,
            'filtros'       => $filtros,
            'mensagem'      => $mensagem,
            'erro'          => $erro,
            'activePage'    => 'financeiro',
        ]);
    }

    // ═══════════════════════════════════════════════════════
    // ADMIN — Configuração de comissões
    // ═══════════════════════════════════════════════════════

    public function config()
    {
        $this->middleware('admin');

        $configs       = $this->service->findAllConfigs();
        $profissionalDAO = new ProfissionalDAO();
        $profissionais = $profissionalDAO->findAll();

        $mensagem = $_SESSION['success'] ?? null;
        $erro     = $_SESSION['erro']    ?? null;
        unset($_SESSION['success'], $_SESSION['erro']);

        $this->view('financeiro/admin/config', [
            'configs'       => $configs,
            'profissionais' => $profissionais,
            'mensagem'      => $mensagem,
            'erro'          => $erro,
            'activePage'    => 'financeiro',
        ]);
    }

    /**
     * POST /financeiro/salvarConfig
     */
    public function salvarConfig()
    {
        $this->middleware('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('financeiro/config');
            return;
        }

        $this->verifyCsrf();

        $dados = [
            'profissional_id' => (int)($_POST['profissional_id'] ?? 0),
            'tipo'            => trim($_POST['tipo'] ?? ''),
            'percentual'      => !empty($_POST['percentual']) ? (float)$_POST['percentual'] : null,
            'valor_fixo'      => !empty($_POST['valor_fixo'])  ? (float)$_POST['valor_fixo']  : null,
            'observacoes'     => trim($_POST['observacoes'] ?? ''),
        ];

        try {
            $this->service->salvarConfig($dados, (int)$_SESSION['user']['id']);
            $_SESSION['success'] = "Configuração de comissão salva com sucesso!";
        } catch (Exception $e) {
            $_SESSION['erro'] = $e->getMessage();
        }

        $this->redirect('financeiro/config');
    }

    // ═══════════════════════════════════════════════════════
    // ADMIN — Pagar comissão
    // ═══════════════════════════════════════════════════════

    /**
     * POST /financeiro/pagar
     */
    public function pagar()
    {
        $this->middleware('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('financeiro/comissoes');
            return;
        }

        $this->verifyCsrf();

        $comissaoId      = (int)($_POST['comissao_id']    ?? 0);
        $formaPagamento  = trim($_POST['forma_pagamento'] ?? '');

        try {
            $this->service->marcarComoPago($comissaoId, $formaPagamento, (int)$_SESSION['user']['id']);
            $_SESSION['success'] = "Comissão marcada como paga com sucesso!";
        } catch (Exception $e) {
            $_SESSION['erro'] = $e->getMessage();
        }

        $this->redirect('financeiro/comissoes');
    }

    /**
     * POST /financeiro/cancelarComissao
     */
    public function cancelarComissao()
    {
        $this->middleware('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('financeiro/comissoes');
            return;
        }

        $this->verifyCsrf();

        $comissaoId = (int)($_POST['comissao_id'] ?? 0);

        try {
            $this->service->cancelar($comissaoId, (int)$_SESSION['user']['id']);
            $_SESSION['success'] = "Comissão cancelada.";
        } catch (Exception $e) {
            $_SESSION['erro'] = $e->getMessage();
        }

        $this->redirect('financeiro/comissoes');
    }

    // ═══════════════════════════════════════════════════════
    // ADMIN — Relatório
    // ═══════════════════════════════════════════════════════

    public function relatorio()
    {
        $this->middleware('admin');

        $filtros             = $this->filtrosFromRequest();
        $totais              = $this->service->totaisAdmin($filtros);
        $totaisProfissionais = $this->service->totaisPorProfissional($filtros);
        $comissoes           = $this->service->findAll($filtros);
        $profissionalDAO     = new ProfissionalDAO();
        $profissionais       = $profissionalDAO->findAll();

        $this->view('financeiro/admin/relatorio', [
            'totais'              => $totais,
            'totaisProfissionais' => $totaisProfissionais,
            'comissoes'           => $comissoes,
            'profissionais'       => $profissionais,
            'filtros'             => $filtros,
            'activePage'          => 'financeiro',
        ]);
    }

    // ═══════════════════════════════════════════════════════
    // ADMIN — PDF
    // ═══════════════════════════════════════════════════════

    public function pdf()
    {
        $this->middleware('admin');

        $filtros             = $this->filtrosFromRequest();
        $totais              = $this->service->totaisAdmin($filtros);
        $totaisProfissionais = $this->service->totaisPorProfissional($filtros);
        $comissoes           = $this->service->findAll($filtros);

        // Log de exportação de PDF
        $dao = new ComissaoDAO();
        $dao->log(
            'pdf_exportado',
            'relatorio_admin',
            null,
            (int)$_SESSION['user']['id'],
            $filtros
        );

        // Renderiza a view de impressão direta (sem sidebar)
        $this->view('financeiro/admin/pdf', [
            'totais'              => $totais,
            'totaisProfissionais' => $totaisProfissionais,
            'comissoes'           => $comissoes,
            'filtros'             => $filtros,
            'dataEmissao'         => date('d/m/Y H:i'),
        ]);
    }

    // ═══════════════════════════════════════════════════════
    // PROFISSIONAL — Minhas comissões
    // ═══════════════════════════════════════════════════════

    public function minhas()
    {
        $this->middleware('profissional');

        $profissionalId = $this->getProfissionalIdDaSessao();

        $filtros   = $this->filtrosFromRequest();
        $comissoes = $this->service->findByProfissional($profissionalId, $filtros);
        $totais    = $this->service->totaisProfissional($profissionalId);
        $evolucao  = $this->service->evolucaoMensal($profissionalId);
        $config    = $this->service->findConfigAtiva($profissionalId);

        $mensagem = $_SESSION['success'] ?? null;
        $erro     = $_SESSION['erro']    ?? null;
        unset($_SESSION['success'], $_SESSION['erro']);

        $this->view('financeiro/profissional/minhas', [
            'comissoes'  => $comissoes,
            'totais'     => $totais,
            'evolucao'   => $evolucao,
            'config'     => $config,
            'filtros'    => $filtros,
            'mensagem'   => $mensagem,
            'erro'       => $erro,
            'activePage' => 'financeiro',
        ]);
    }

    // ═══════════════════════════════════════════════════════
    // PROFISSIONAL — Relatório
    // ═══════════════════════════════════════════════════════

    public function meusRelatorios()
    {
        $this->middleware('profissional');

        $profissionalId = $this->getProfissionalIdDaSessao();

        $filtros   = $this->filtrosFromRequest();
        $comissoes = $this->service->findByProfissional($profissionalId, $filtros);
        $totais    = $this->service->totaisProfissional($profissionalId);

        $this->view('financeiro/profissional/relatorio', [
            'comissoes'  => $comissoes,
            'totais'     => $totais,
            'filtros'    => $filtros,
            'activePage' => 'financeiro',
        ]);
    }

    // ═══════════════════════════════════════════════════════
    // PROFISSIONAL — PDF
    // ═══════════════════════════════════════════════════════

    public function meuPdf()
    {
        $this->middleware('profissional');

        $profissionalId = $this->getProfissionalIdDaSessao();

        $filtros   = $this->filtrosFromRequest();
        $comissoes = $this->service->findByProfissional($profissionalId, $filtros);
        $totais    = $this->service->totaisProfissional($profissionalId);

        // Log de exportação
        $dao = new ComissaoDAO();
        $dao->log(
            'pdf_exportado',
            'relatorio_profissional',
            $profissionalId,
            (int)$_SESSION['user']['id'],
            $filtros
        );

        $this->view('financeiro/profissional/pdf', [
            'comissoes'  => $comissoes,
            'totais'     => $totais,
            'filtros'    => $filtros,
            'dataEmissao' => date('d/m/Y H:i'),
            'nomeProfissional' => $_SESSION['user']['nome'] ?? '',
        ]);
    }

    // ═══════════════════════════════════════════════════════
    // HELPERS PRIVADOS
    // ═══════════════════════════════════════════════════════

    /**
     * Extrai e sanitiza filtros da query string.
     */
    private function filtrosFromRequest(): array
    {
        return [
            'data_inicio'      => $this->sanitizeDate($_GET['data_inicio']      ?? ''),
            'data_fim'         => $this->sanitizeDate($_GET['data_fim']          ?? ''),
            'profissional_id'  => !empty($_GET['profissional_id']) ? (int)$_GET['profissional_id'] : null,
            'status'           => $this->sanitizeEnum($_GET['status'] ?? '', ['pendente','pago','cancelado']),
            'forma_pagamento'  => $this->sanitizeEnum($_GET['forma_pagamento'] ?? '', ['pix','transferencia','dinheiro','outro']),
        ];
    }

    private function sanitizeDate(string $val): string
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) {
            return $val;
        }
        return '';
    }

    private function sanitizeEnum(string $val, array $allowed): string
    {
        return in_array($val, $allowed, true) ? $val : '';
    }

    /**
     * Obtém o profissional_id da sessão atual com segurança.
     * Protege contra IDOR — nunca aceita ID da URL.
     */
    private function getProfissionalIdDaSessao(): int
    {
        $usuarioId = (int)$_SESSION['user']['id'];
        $dao       = new ProfissionalDAO();
        $prof      = $dao->findByUsuarioId($usuarioId);

        if (!$prof) {
            $_SESSION['erro'] = "Perfil profissional não encontrado.";
            $this->redirect('dashboard');
        }

        return (int)$prof['id'];
    }
}
