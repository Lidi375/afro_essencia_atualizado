<?php

/**
 * PagamentoController — rotas de pagamento.
 *
 *   GET  /pagamento/index              → lista transações do cliente
 *   GET  /pagamento/checkout/{id}      → inicia checkout real no gateway
 *   GET  /pagamento/retorno            → tela de retorno pós-pagamento
 *   POST /pagamento/webhook/{gateway}  → endpoint de webhook (público, sem sessão)
 *
 * REMOVIDO: processar() e confirmar() com aprovação manual fake.
 */
class PagamentoController extends BaseController
{
    // ─── Lista transações do cliente ──────────────────────────────────
    public function index()
    {
        $this->middleware('auth');

        $tipo = $_SESSION['user']['tipo_usuario'] ?? '';
        if ($tipo !== 'cliente') {
            $this->redirect('dashboard');
            return;
        }

        $service    = new PagamentoService();
        $transacoes = $service->listarPorCliente((int) $_SESSION['user']['id']);

        $mensagem = $_SESSION['success'] ?? null;
        $erro     = $_SESSION['erro']    ?? null;
        unset($_SESSION['success'], $_SESSION['erro']);

        $this->view('pagamento/index', [
            'transacoes' => $transacoes,
            'mensagem'   => $mensagem,
            'erro'       => $erro,
        ]);
    }

    // ─── Inicia checkout real no gateway ──────────────────────────────
    public function checkout(int $agendamentoId = 0)
    {
        $this->middleware('auth');

        $tipo = $_SESSION['user']['tipo_usuario'] ?? '';
        if ($tipo !== 'cliente') {
            $this->redirect('dashboard');
            return;
        }

        if (!$agendamentoId) {
            $_SESSION['erro'] = "Agendamento inválido.";
            $this->redirect('agendamento/index');
            return;
        }

        try {
            $service  = new PagamentoService();
            $resultado = $service->iniciarCheckout($agendamentoId, (int) $_SESSION['user']['id']);

            // Redireciona para o gateway externo (Mercado Pago, etc.)
            header('Location: ' . $resultado['checkout_url']);
            exit;

        } catch (Exception $e) {
            $_SESSION['erro'] = $e->getMessage();
            $this->redirect("agendamento/index");
        }
    }

    // ─── Tela de retorno após pagamento no gateway ────────────────────
    public function retorno()
    {
        $this->middleware('auth');

        $tipo = $_SESSION['user']['tipo_usuario'] ?? '';
        if ($tipo !== 'cliente') {
            $this->redirect('dashboard');
            return;
        }

        $statusParam   = $_GET['status'] ?? '';
        $transactionId = trim($_GET['tid'] ?? '');

        // Valida transaction_id (UUID format)
        if (!preg_match('/^[0-9a-f\-]{36}$/', $transactionId)) {
            $_SESSION['erro'] = "Referência de transação inválida.";
            $this->redirect('pagamento/index');
            return;
        }

        try {
            $service  = new PagamentoService();
            $resultado = $service->processarRetorno($transactionId, (int) $_SESSION['user']['id']);

            $status      = $resultado['status'];
            $transaction = $resultado['transaction'];

            $this->view('pagamento/retorno', [
                'status'      => $status,
                'transaction' => $transaction,
            ]);

        } catch (Exception $e) {
            $_SESSION['erro'] = $e->getMessage();
            $this->redirect('pagamento/index');
        }
    }

    // ─── Página de início de pagamento (resumo + botão "Pagar Agora") ─
    public function iniciar(int $agendamentoId = 0)
    {
        $this->middleware('auth');

        $tipo = $_SESSION['user']['tipo_usuario'] ?? '';
        if ($tipo !== 'cliente') {
            $this->redirect('dashboard');
            return;
        }

        if (!$agendamentoId) {
            $_SESSION['erro'] = "Agendamento inválido.";
            $this->redirect('agendamento/index');
            return;
        }

        $agendamentoDAO = new AgendamentoDAO();
        $agendamento    = $agendamentoDAO->findById($agendamentoId);

        if (!$agendamento) {
            $_SESSION['erro'] = "Agendamento não encontrado.";
            $this->redirect('agendamento/index');
            return;
        }

        $clienteDAO = new ClienteDAO();
        $cliente    = $clienteDAO->findByUsuarioId((int) $_SESSION['user']['id']);

        if (!$cliente || (int) $agendamento['cliente_id'] !== (int) $cliente['id']) {
            $_SESSION['erro'] = "Acesso negado.";
            $this->redirect('agendamento/index');
            return;
        }

        // Verifica se já tem pagamento aprovado
        $transactionDAO = new PaymentTransactionDAO();
        if ($transactionDAO->hasApproved($agendamentoId)) {
            $_SESSION['erro'] = "Este agendamento já foi pago.";
            $this->redirect('pagamento/index');
            return;
        }

        $servicoDAO = new ServicoDAO();
        $servico    = $servicoDAO->findById((int) $agendamento['servico_id']);

        // Busca nome do profissional
        $profissionalDAO = new ProfissionalDAO();
        $profissional    = $profissionalDAO->findById((int) $agendamento['profissional_id']);
        $nomeProfissional = '';
        if ($profissional) {
            $usuarioDAO = new UsuarioDAO();
            $usuProf    = $usuarioDAO->findById((int) $profissional['usuario_id']);
            $nomeProfissional = $usuProf['nome'] ?? '';
        }

        $erro    = $_SESSION['erro']    ?? null;
        $sucesso = $_SESSION['success'] ?? null;
        unset($_SESSION['erro'], $_SESSION['success']);

        $this->view('pagamento/iniciar', [
            'agendamento'      => $agendamento,
            'servico'          => $servico,
            'nomeProfissional' => $nomeProfissional,
            'erro'             => $erro,
            'sucesso'          => $sucesso,
        ]);
    }

    // ─── Webhook (público — sem sessão, sem CSRF) ─────────────────────
    public function webhook(string $gateway = 'mercadopago')
    {
        // Sem autenticação de sessão — validação é feita pela assinatura do gateway

        // Lê payload
        $rawBody = file_get_contents('php://input');
        $payload = json_decode($rawBody ?: '{}', true) ?? [];

        // Também aceita query params (IPN legado do MP)
        if (empty($payload)) {
            $payload = $_GET;
        }

        // Coleta headers relevantes
        $headers = [];
        foreach ($_SERVER as $key => $val) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = strtolower(str_replace(['HTTP_', '_'], ['', '-'], $key));
                $headers[$header] = $val;
            }
        }

        try {
            $service  = new PagamentoService();
            $resultado = $service->processarWebhook($gateway, $payload, $headers);

            http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode(['ok' => true, 'status' => $resultado['status']]);

        } catch (Exception $e) {
            error_log("[Webhook/{$gateway}] Erro: " . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'error' => 'Erro interno']);
        }

        exit;
    }
}
