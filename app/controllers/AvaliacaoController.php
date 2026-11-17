<?php

/**
 * AvaliacaoController — apenas clientes podem avaliar.
 */
class AvaliacaoController extends BaseController
{
    // GET /avaliacao/create?agendamento_id=X
    public function create()
    {
        $this->middleware('auth');

        $tipo = $_SESSION['user']['tipo_usuario'] ?? '';
        if ($tipo !== 'cliente') {
            $this->redirect('dashboard');
            return;
        }

        $agendamentoId = (int) ($_GET['agendamento_id'] ?? 0);
        if (!$agendamentoId) {
            $this->redirect('agendamento/index');
            return;
        }

        $clienteDAO = new ClienteDAO();
        $cliente    = $clienteDAO->findByUsuarioId((int) $_SESSION['user']['id']);

        if (!$cliente) {
            $this->redirect('agendamento/index');
            return;
        }

        $agendamentoDAO = new AgendamentoDAO();
        $agendamento    = $agendamentoDAO->findById($agendamentoId);

        // Verifica que o agendamento pertence ao cliente e está concluído
        if (
            !$agendamento ||
            (int) $agendamento['cliente_id'] !== (int) $cliente['id'] ||
            $agendamento['status'] !== 'concluido'
        ) {
            $_SESSION['erro'] = "Só é possível avaliar agendamentos concluídos.";
            $this->redirect('agendamento/index');
            return;
        }

        $avaliacaoDAO = new AvaliacaoDAO();
        if ($avaliacaoDAO->jaAvaliou($agendamentoId)) {
            $_SESSION['erro'] = "Este agendamento já foi avaliado.";
            $this->redirect('agendamento/index');
            return;
        }

        $erro = $_SESSION['erro'] ?? null;
        unset($_SESSION['erro']);

        $this->view('avaliacao/create', [
            'agendamento' => $agendamento,
            'erro'        => $erro,
        ]);
    }

    // POST /avaliacao/store
    public function store()
    {
        $this->middleware('auth');

        $tipo = $_SESSION['user']['tipo_usuario'] ?? '';
        if ($tipo !== 'cliente') {
            $this->redirect('dashboard');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('agendamento/index');
            return;
        }

        $this->verifyCsrf();

        $clienteDAO = new ClienteDAO();
        $cliente    = $clienteDAO->findByUsuarioId((int) $_SESSION['user']['id']);

        if (!$cliente) {
            $this->redirect('agendamento/index');
            return;
        }

        $agendamentoId = (int) ($_POST['agendamento_id'] ?? 0);
        $nota          = (int) ($_POST['nota']           ?? 0);
        $comentario    = trim(strip_tags($_POST['comentario'] ?? ''));

        if (!$agendamentoId || $nota < 1 || $nota > 5) {
            $_SESSION['erro'] = "Preencha a nota (1 a 5).";
            $this->redirect("avaliacao/create?agendamento_id={$agendamentoId}");
            return;
        }

        $agendamentoDAO = new AgendamentoDAO();
        $agendamento    = $agendamentoDAO->findById($agendamentoId);

        if (
            !$agendamento ||
            (int) $agendamento['cliente_id'] !== (int) $cliente['id'] ||
            $agendamento['status'] !== 'concluido'
        ) {
            $_SESSION['erro'] = "Agendamento inválido para avaliação.";
            $this->redirect('agendamento/index');
            return;
        }

        try {
            $service = new AvaliacaoService();
            $service->criar([
                'agendamento_id'  => $agendamentoId,
                'profissional_id' => $agendamento['profissional_id'],
                'cliente_id'      => $cliente['id'],
                'nota'            => $nota,
                'comentario'      => $comentario,
            ]);

            $_SESSION['success'] = "Avaliação enviada! Obrigada pelo feedback.";
        } catch (Exception $e) {
            $_SESSION['erro'] = $e->getMessage();
        }

        $this->redirect('agendamento/index');
    }

    // GET /avaliacao/minhas
    public function minhas()
    {
        $this->middleware('auth');

        $tipo = $_SESSION['user']['tipo_usuario'] ?? '';
        if ($tipo !== 'cliente') {
            $this->redirect('dashboard');
            return;
        }

        $clienteDAO = new ClienteDAO();
        $cliente    = $clienteDAO->findByUsuarioId((int) $_SESSION['user']['id']);

        if (!$cliente) {
            $this->redirect('dashboard');
            return;
        }

        $service    = new AvaliacaoService();
        $avaliacoes = $service->listarByCliente((int) $cliente['id']);

        $this->view('avaliacao/minhas', ['avaliacoes' => $avaliacoes]);
    }
}
