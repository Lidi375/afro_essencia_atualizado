<?php

/**
 * DashboardController
 * Controla o painel conforme perfil do usuário.
 */
class DashboardController extends BaseController
{
    // ─── Rota principal ───────────────────────────────────────────────────────
    public function index()
    {
        $this->middleware('auth');

        $mensagem = $_SESSION['success'] ?? null;
        $erro     = $_SESSION['erro']    ?? null;
        unset($_SESSION['success'], $_SESSION['erro']);

        $tipo = $_SESSION['user']['tipo_usuario'];

        if ($tipo === 'admin') {
            $this->indexAdmin($mensagem, $erro);
        } elseif ($tipo === 'profissional') {
            $this->indexProfissional($mensagem, $erro);
        } else {
            // Verifica se o cliente já preencheu a anamnese
            $temAnamnese = false;
            try {
                $clienteDAO  = new ClienteDAO();
                $cliente     = $clienteDAO->findByUsuarioId($_SESSION['user']['id']);
                if ($cliente) {
                    $anamneseDAO = new AnamneseDAO();
                    $temAnamnese = $anamneseDAO->findByClienteId((int)$cliente['id']) !== null;
                }
            } catch (\Throwable $e) {
                // Silencioso: não quebra o dashboard se houver falha
            }

            $this->view('dashboard/cliente/index', [
                'mensagem'    => $mensagem,
                'erro'        => $erro,
                'temAnamnese' => $temAnamnese,
            ]);
        }
    }

    // ─── Dashboard Profissional ──────────────────────────────────────────────
    private function indexProfissional(?string $mensagem, ?string $erro): void
    {
        $usuarioId = (int) $_SESSION['user']['id'];

        $profissionalDAO = new ProfissionalDAO();
        $profissional    = $profissionalDAO->findByUsuarioId($usuarioId);

        if (!$profissional) {
            $_SESSION['erro'] = "Perfil profissional não encontrado. Contate a administração.";
            $base = defined('APP_BASE') ? rtrim(APP_BASE, '/') : '/afro_essencia';
            header("Location: {$base}/auth/logout");
            exit;
        }

        $profissionalId = (int) $profissional['id'];
        $agendamentoDAO = new AgendamentoDAO();
        $avaliacaoDAO   = new AvaliacaoDAO();

        $agendaHoje = $agendamentoDAO->findHojeByProfissionalId($profissionalId);
        $proximos   = $agendamentoDAO->findProximosByProfissionalId($profissionalId);
        $historico  = $agendamentoDAO->findHistoricoByProfissionalId($profissionalId);
        $metricas   = $agendamentoDAO->metricasProfissional($profissionalId);
        $avaliacoes = $avaliacaoDAO->findByProfissional($profissionalId);

        $this->view('dashboard/profissional/index', [
            'profissional' => $profissional,
            'agendaHoje'   => $agendaHoje,
            'proximos'     => $proximos,
            'historico'    => $historico,
            'metricas'     => $metricas,
            'avaliacoes'   => $avaliacoes,
            'mensagem'     => $mensagem,
            'erro'         => $erro,
        ]);
    }

    // ─── Dashboard Admin ─────────────────────────────────────────────────────
    private function indexAdmin(?string $mensagem, ?string $erro): void
    {
        $usuarioDAO      = new UsuarioDAO();
        $clienteDAO      = new ClienteDAO();
        $profissionalDAO = new ProfissionalDAO();
        $servicoDAO      = new ServicoDAO();
        $agendamentoDAO  = new AgendamentoDAO();
        $pagamentoDAO    = new PagamentoDAO();
        $totais = [
            'usuarios'      => $usuarioDAO->countAll(),
            'clientes'      => $clienteDAO->countAll(),
            'profissionais' => $profissionalDAO->countAll(),
            'servicos'      => $servicoDAO->countAll(),
            'agendamentos'  => $agendamentoDAO->countAll(),
            'pendentes'     => $agendamentoDAO->countByStatus('pendente'),
            'cancelados'    => $agendamentoDAO->countByStatus('cancelado'),
            'concluidos'    => $agendamentoDAO->countByStatus('concluido'),
            'candidaturas_pendentes' => $this->contarCandidaturasPendentes(),
        ];

        $hoje = [
            'agendamentos'   => $agendamentoDAO->countHoje(),
            'cancelamentos'  => $agendamentoDAO->countCanceladosHoje(),
            'concluidos'     => $agendamentoDAO->countConcluidosHoje(),
            'novos_clientes' => $clienteDAO->countNovosHoje(),
            'faturamento'    => $agendamentoDAO->faturamentoHoje(),
        ];

        $agendaHoje      = $agendamentoDAO->findAgendamentosHoje();
        $faturamentoDias = $agendamentoDAO->faturamentoPorDia(30);
        $topServicos     = $agendamentoDAO->topServicos(6);

        $financeiro = [
            'receita_mes'        => $agendamentoDAO->receitaMesAtual(),
            'novos_clientes_mes' => $clienteDAO->countNovosNoMes(),
            'por_metodo'         => $pagamentoDAO->receitaPorMetodo(),
        ];

        $profissionaisDados = $agendamentoDAO->faturamentoPorProfissional();

        $this->view('dashboard/admin/index', [
            'mensagem'               => $mensagem,
            'erro'                   => $erro,
            'totais'                 => $totais,
            'hoje'                   => $hoje,
            'agendaHoje'             => $agendaHoje,
            'faturamentoDias'        => $faturamentoDias,
            'topServicos'            => $topServicos,
            'financeiro'             => $financeiro,
            'profissionaisDados'     => $profissionaisDados,
        ]);
    }

    // ─── Usuários ─────────────────────────────────────────────────────────────
    public function usuarios()
    {
        $this->middleware('admin');
        $usuarios = (new UsuarioDAO())->findAll();
        $this->view('dashboard/admin/usuarios', ['usuarios' => $usuarios]);
    }

    // ─── Clientes ─────────────────────────────────────────────────────────────
    public function clientes()
    {
        $this->middleware('admin');

        $mensagem = $_SESSION['success'] ?? null;
        $erro     = $_SESSION['erro']    ?? null;
        unset($_SESSION['success'], $_SESSION['erro']);

        $clientes = (new ClienteDAO())->findAll();
        $this->view('dashboard/admin/clientes', [
            'clientes' => $clientes,
            'mensagem' => $mensagem,
            'erro'     => $erro,
        ]);
    }

    // ─── Profissionais ────────────────────────────────────────────────────────
    public function profissionais()
    {
        $this->middleware('admin');

        $mensagem = $_SESSION['success'] ?? null;
        $erro     = $_SESSION['erro']    ?? null;
        unset($_SESSION['success'], $_SESSION['erro']);

        $profissionais = (new ProfissionalDAO())->findAll();

        $this->view('dashboard/admin/profissionais', [
            'profissionais' => $profissionais,
            'mensagem'      => $mensagem,
            'erro'          => $erro,
        ]);
    }

    // ─── Desligar profissional ────────────────────────────────────────────────
    public function desligarProfissional($profissionalId = null)
    {
        $this->middleware('admin');

        if (!$profissionalId) {
            $_SESSION['erro'] = "Profissional inválido.";
            $this->redirect('dashboard/profissionais');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('dashboard/profissionais');
            return;
        }

        $this->verifyCsrf();

        $dao  = new ProfissionalDAO();
        $prof = $dao->findById((int) $profissionalId);

        if (!$prof) {
            $_SESSION['erro'] = "Profissional não encontrado.";
            $this->redirect('dashboard/profissionais');
            return;
        }

        try {
            $conn = Database::getConnection();
            $conn->beginTransaction();

            // Desativa o profissional (sem apagar dados)
            $dao->desativar((int) $profissionalId);

            // Reverte o tipo do usuário para cliente
            (new UsuarioDAO())->updateTipo((int) $prof['usuario_id'], 'cliente');

            $conn->commit();
            $_SESSION['success'] = "Profissional desligado com sucesso.";

        } catch (Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            error_log('DashboardController::desligarProfissional — ' . $e->getMessage());
            $_SESSION['erro'] = "Erro ao desligar profissional. Tente novamente.";
        }

        $this->redirect('dashboard/profissionais');
    }

    // ─── Serviços ─────────────────────────────────────────────────────────────
    public function servicos()
    {
        $this->middleware('admin');
        $servicos = (new ServicoDAO())->findAll();
        $this->view('dashboard/admin/servicos', ['servicos' => $servicos]);
    }

    // ─── Agendamentos ─────────────────────────────────────────────────────────
    public function agendamentos()
    {
        $this->middleware('admin');

        $mensagem = $_SESSION['success'] ?? null;
        $erro     = $_SESSION['erro']    ?? null;
        unset($_SESSION['success'], $_SESSION['erro']);

        $agendamentos = (new AgendamentoDAO())->findAllAdmin();
        $this->view('dashboard/admin/agendamentos', [
            'agendamentos' => $agendamentos,
            'mensagem'     => $mensagem,
            'erro'         => $erro,
        ]);
    }

    // ─── Cancelar agendamento (admin) ─────────────────────────────────────────
    public function cancelarAgendamento($id = null)
    {
        $this->middleware('admin');

        if (!$id) {
            $_SESSION['erro'] = "Agendamento inválido.";
            $this->redirect('dashboard/agendamentos');
            return;
        }

        $dao = new AgendamentoDAO();
        $ag  = $dao->findById((int) $id);

        if (!$ag) {
            $_SESSION['erro'] = "Agendamento não encontrado.";
            $this->redirect('dashboard/agendamentos');
            return;
        }

        if ($ag['status'] === 'cancelado') {
            $_SESSION['erro'] = "Este agendamento já está cancelado.";
            $this->redirect('dashboard/agendamentos');
            return;
        }

        $dao->cancelar((int) $id);
        $_SESSION['success'] = "Agendamento cancelado com sucesso.";
        $this->redirect('dashboard/agendamentos');
    }

    // ─── Anamnese ─────────────────────────────────────────────────────────────
    public function anamnese()
    {
        $this->middleware('admin');
        $service   = new AnamneseService();
        $anamneses = $service->listarAdmin();
        $this->view('dashboard/admin/anamnese', ['anamneses' => $anamneses]);
    }

    public function anamneseDetalhe($id = null)
    {
        $this->middleware('admin');

        if (!$id) {
            $this->redirect('dashboard/anamnese');
            return;
        }

        $service  = new AnamneseService();
        $anamnese = $service->detalheAdmin((int) $id);

        if (!$anamnese) {
            $_SESSION['erro'] = "Anamnese não encontrada.";
            $this->redirect('dashboard/anamnese');
            return;
        }

        $this->view('dashboard/admin/anamnese-detalhe', ['anamnese' => $anamnese]);
    }

    // ─── Candidaturas — Trabalhe Conosco ─────────────────────────────────────
    public function candidaturas()
    {
        $this->middleware('admin');

        $mensagem = $_SESSION['success'] ?? null;
        $erro     = $_SESSION['erro']    ?? null;
        unset($_SESSION['success'], $_SESSION['erro']);

        $status    = trim($_GET['status'] ?? '');
        $busca     = trim($_GET['busca'] ?? '');
        $dao       = new CandidaturaDAO();

        $candidaturas = $dao->findAllFiltered($status, $busca);
        $totaisPorStatus = $dao->countGroupByStatus();

        $this->view('dashboard/admin/candidaturas', [
            'candidaturas'    => $candidaturas,
            'totaisPorStatus' => $totaisPorStatus,
            'filtroStatus'    => $status,
            'filtroBusca'     => $busca,
            'mensagem'        => $mensagem,
            'erro'            => $erro,
        ]);
    }

    public function candidaturaDetalhe($id = null)
    {
        $this->middleware('admin');

        if (!$id) {
            $this->redirect('dashboard/candidaturas');
            return;
        }

        $dao        = new CandidaturaDAO();
        $candidatura = $dao->findById((int) $id);

        if (!$candidatura) {
            $_SESSION['erro'] = "Candidatura não encontrada.";
            $this->redirect('dashboard/candidaturas');
            return;
        }

        $historico = $dao->findHistoricoByCandidaturaId((int) $id);

        $mensagem = $_SESSION['success'] ?? null;
        $erro     = $_SESSION['erro']    ?? null;
        unset($_SESSION['success'], $_SESSION['erro']);

        $this->view('dashboard/admin/candidatura-detalhe', [
            'candidatura' => $candidatura,
            'historico'   => $historico,
            'mensagem'    => $mensagem,
            'erro'        => $erro,
        ]);
    }

    public function candidaturaAtualizarStatus($id = null)
    {
        $this->middleware('admin');

        if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('dashboard/candidaturas');
            return;
        }

        $this->verifyCsrf();

        $novoStatus    = trim($_POST['status'] ?? '');
        $observacao    = trim(strip_tags($_POST['observacao'] ?? ''));
        $statusValidos = ['pendente', 'em_analise', 'aprovado', 'reprovado'];

        if (!in_array($novoStatus, $statusValidos, true)) {
            $_SESSION['erro'] = "Status inválido.";
            $this->redirect('dashboard/candidaturaDetalhe/' . $id);
            return;
        }

        $dao        = new CandidaturaDAO();
        $candidatura = $dao->findById((int) $id);

        if (!$candidatura) {
            $_SESSION['erro'] = "Candidatura não encontrada.";
            $this->redirect('dashboard/candidaturas');
            return;
        }

        try {
            $service = new CandidaturaService();
            $service->atualizarStatus(
                (int) $id,
                $novoStatus,
                $observacao,
                (int) $_SESSION['user']['id']
            );

            if ($novoStatus === 'aprovado') {
                $service->aprovarCandidatura((int) $id);
                $_SESSION['success'] = "Candidatura aprovada! Perfil profissional criado com sucesso.";
            } else {
                $_SESSION['success'] = "Status atualizado para: " . ucfirst(str_replace('_', ' ', $novoStatus)) . ".";
            }

        } catch (Exception $e) {
            error_log('DashboardController::candidaturaAtualizarStatus — ' . $e->getMessage());
            $_SESSION['erro'] = $e->getMessage();
        }

        $this->redirect('dashboard/candidaturaDetalhe/' . $id);
    }

    public function candidaturaDownload($id = null)
    {
        $this->middleware('admin');

        if (!$id) {
            $this->redirect('dashboard/candidaturas');
            return;
        }

        $tipo = trim($_GET['tipo'] ?? 'curriculo');
        $dao  = new CandidaturaDAO();
        $candidatura = $dao->findById((int) $id);

        if (!$candidatura) {
            $_SESSION['erro'] = "Candidatura não encontrada.";
            $this->redirect('dashboard/candidaturas');
            return;
        }

        $campo    = $tipo === 'certificado' ? 'arquivo_certificado' : 'arquivo_curriculo';
        $caminho  = $candidatura[$campo] ?? '';

        if (empty($caminho) || !file_exists($caminho)) {
            $_SESSION['erro'] = "Arquivo não encontrado.";
            $this->redirect('dashboard/candidaturaDetalhe/' . $id);
            return;
        }

        $nomeArquivo = basename($caminho);
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');
        header('Content-Length: ' . filesize($caminho));
        readfile($caminho);
        exit;
    }

    // ─── Pagamentos ───────────────────────────────────────────────────────────
    public function pagamentoForm($agendamentoId = null)
    {
        $this->middleware('admin');

        if (!$agendamentoId) {
            $this->redirect('dashboard/agendamentos');
            return;
        }

        $agendamentoDAO = new AgendamentoDAO();
        $agendamento    = $agendamentoDAO->findById((int) $agendamentoId);

        if (!$agendamento) {
            $_SESSION['erro'] = "Agendamento não encontrado.";
            $this->redirect('dashboard/agendamentos');
            return;
        }

        $pagamentoDAO = new PagamentoDAO();
        $jaPago       = $pagamentoDAO->findByAgendamento((int) $agendamentoId);

        $servicoDAO = new ServicoDAO();
        $servico    = $servicoDAO->findById($agendamento['servico_id']);

        $erro    = $_SESSION['erro']    ?? null;
        $sucesso = $_SESSION['success'] ?? null;
        unset($_SESSION['erro'], $_SESSION['success']);

        $this->view('dashboard/admin/pagamento-form', [
            'agendamento' => $agendamento,
            'servico'     => $servico,
            'jaPago'      => $jaPago,
            'erro'        => $erro,
            'sucesso'     => $sucesso,
        ]);
    }

    public function pagamentoStore()
    {
        $this->middleware('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('dashboard/agendamentos');
            return;
        }

        $this->verifyCsrf();

        $agendamentoId  = (int) ($_POST['agendamento_id']    ?? 0);
        $metodo         = trim($_POST['metodo_pagamento'] ?? '');
        $valor          = (float) str_replace(',', '.', $_POST['valor'] ?? '0');
        $metodosValidos = ['pix', 'cartao', 'dinheiro', 'transferencia'];

        if (!$agendamentoId || !$metodo || $valor <= 0) {
            $_SESSION['erro'] = "Preencha todos os campos corretamente.";
            $this->redirect('dashboard/pagamentoForm/' . $agendamentoId);
            return;
        }

        if (!in_array($metodo, $metodosValidos, true)) {
            $_SESSION['erro'] = "Método de pagamento inválido.";
            $this->redirect('dashboard/pagamentoForm/' . $agendamentoId);
            return;
        }

        $pagamentoDAO = new PagamentoDAO();
        if ($pagamentoDAO->findByAgendamento($agendamentoId)) {
            $_SESSION['erro'] = "Este agendamento já possui pagamento registrado.";
            $this->redirect('dashboard/pagamentoForm/' . $agendamentoId);
            return;
        }

        try {
            $service = new PagamentoService();
            $service->pagar([
                'agendamento_id' => $agendamentoId,
                'metodo'         => $metodo,
                'valor'          => $valor,
            ]);

            (new AgendamentoDAO())->concluir($agendamentoId);

            // ── Gera comissão automaticamente (módulo financeiro) ─────────
            // Condição: agendamento concluído + pagamento aprovado (ambos OK aqui)
            // Silencioso: não interrompe o fluxo mesmo que falhe
            try {
                (new ComissaoService())->tentarGerarComissao($agendamentoId);
            } catch (Throwable $comissaoEx) {
                // Falha na comissão não cancela o pagamento
                error_log('[ComissaoService] Erro ao gerar comissão para agendamento ' . $agendamentoId . ': ' . $comissaoEx->getMessage());
            }
            // ─────────────────────────────────────────────────────────────

            $_SESSION['success'] = "Pagamento registrado e agendamento concluído!";
            $this->redirect('dashboard/agendamentos');
        } catch (Exception $e) {
            $_SESSION['erro'] = $e->getMessage();
            $this->redirect('dashboard/pagamentoForm/' . $agendamentoId);
        }
    }

    // ─── Avaliações ───────────────────────────────────────────────────────────
    public function avaliacoes()
    {
        $this->middleware('admin');
        $avaliacoes = (new AvaliacaoDAO())->findAll();
        $this->view('dashboard/admin/avaliacoes', ['avaliacoes' => $avaliacoes]);
    }

    // ─── Estorno de Pagamento (admin) ─────────────────────────────────────────
    public function estornarPagamento(int $pagamentoId = 0)
    {
        $this->middleware('admin');

        if (!$pagamentoId) {
            $_SESSION['erro'] = "Pagamento inválido.";
            $this->redirect('dashboard/agendamentos');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('dashboard/agendamentos');
            return;
        }

        $this->verifyCsrf();

        try {
            $service   = new PagamentoService();
            $resultado = $service->estornar($pagamentoId);

            $pagamentoDAO = new PagamentoDAO();
            $pagamento    = $pagamentoDAO->findById($pagamentoId);

            if ($pagamento) {
                $agendamentoDAO = new AgendamentoDAO();
                $agendamento    = $agendamentoDAO->findById((int) $pagamento['agendamento_id']);

                if ($agendamento) {
                    $clienteDAO = new ClienteDAO();
                    $cliente    = $clienteDAO->findById((int) $agendamento['cliente_id']);

                    if ($cliente) {
                        $notif = new NotificacaoService();
                        $notif->notificarPagamentoEstornado(
                            (int) $cliente['usuario_id'],
                            $resultado['servico'],
                            $resultado['valor']
                        );
                    }
                }
            }

            $_SESSION['success'] = "Pagamento estornado e agendamento reaberto com sucesso.";
        } catch (Exception $e) {
            $_SESSION['erro'] = $e->getMessage();
        }

        $this->redirect('dashboard/agendamentos');
    }

    // ─── Lista todos os pagamentos (admin) ────────────────────────────────────
    public function pagamentos()
    {
        $this->middleware('admin');

        $service    = new PagamentoService();
        $pagamentos = $service->listarTodos();

        $mensagem = $_SESSION['success'] ?? null;
        $erro     = $_SESSION['erro']    ?? null;
        unset($_SESSION['success'], $_SESSION['erro']);

        $this->view('dashboard/admin/pagamentos', [
            'pagamentos' => $pagamentos,
            'mensagem'   => $mensagem,
            'erro'       => $erro,
        ]);
    }

    // ─── Auxiliar: conta candidaturas pendentes com proteção ─────────────────
    private function contarCandidaturasPendentes(): int
    {
        try {
            return (new CandidaturaDAO())->countByStatus('pendente');
        } catch (Throwable $e) {
            // Tabela ainda não existe (migração pendente)
            return 0;
        }
    }

}
