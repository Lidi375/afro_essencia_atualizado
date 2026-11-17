<?php

class AgendamentoController extends BaseController
{

    // =========================
    // LISTAR AGENDAMENTOS
    // Trata cliente E profissional corretamente
    // =========================
    public function index()
    {
        $this->middleware('auth');

        $tipo = $_SESSION['user']['tipo_usuario'] ?? 'cliente';

        // ── PROFISSIONAL: vai ao dashboard próprio ─────────────
        if ($tipo === 'profissional') {
            $this->redirect('dashboard');
            return;
        }

        // ── ADMIN: vai ao painel admin ─────────────────────────
        if ($tipo === 'admin') {
            $this->redirect('dashboard/agendamentos');
            return;
        }

        // ── CLIENTE ────────────────────────────────────────────
        $clienteDAO = new ClienteDAO();
        $cliente    = $clienteDAO->findByUsuarioId($_SESSION['user']['id']);

        if (!$cliente) {
            $_SESSION['erro'] = "Perfil de cliente não encontrado. Contate a administração.";
            $this->redirect('dashboard');
            return;
        }

        $agendamentoDAO = new AgendamentoDAO();
        $agendamentos   = $agendamentoDAO->findByClienteId($cliente['id']);

        $proximos  = [];
        $historico = [];
        $agora     = time();

        foreach ($agendamentos as $ag) {
            $dataHoraFim  = $ag['data_agendamento'] . ' ' . ($ag['hora_fim'] ?? '00:00:00');
            $timestampFim = strtotime($dataHoraFim);
            $isFuturo     = $timestampFim > $agora;

            // Nunca mostrar concluído como futuro
            if ($isFuturo && $ag['status'] === 'concluido') {
                $ag['status'] = 'pendente';
            }

            if ($isFuturo && $ag['status'] !== 'cancelado') {
                $proximos[] = $ag;
            } else {
                $historico[] = $ag;
            }
        }

        // Próximos: crescente
        usort($proximos, function ($a, $b) {
            return strtotime($a['data_agendamento'] . ' ' . $a['hora_inicio'])
                 - strtotime($b['data_agendamento'] . ' ' . $b['hora_inicio']);
        });

        // Histórico: decrescente
        usort($historico, function ($a, $b) {
            return strtotime($b['data_agendamento'] . ' ' . $b['hora_inicio'])
                 - strtotime($a['data_agendamento'] . ' ' . $a['hora_inicio']);
        });

        $mensagem = $_SESSION['success'] ?? null;
        $erro     = $_SESSION['erro']    ?? null;
        unset($_SESSION['success'], $_SESSION['erro']);

        // Pré-calcular agendamentos já avaliados com 1 query (evita N queries na view)
        $idsConcluidos = array_map(
            fn($a) => $a['id'],
            array_filter($historico, fn($a) => $a['status'] === 'concluido')
        );

        $avaliacaoDAO = new AvaliacaoDAO();
        $jaAvaliados  = $avaliacaoDAO->findAgendamentosAvaliados(array_values($idsConcluidos));

        $this->view('agendamento/index', [
            'proximos'    => $proximos,
            'historico'   => $historico,
            'mensagem'    => $mensagem,
            'erro'        => $erro,
            'jaAvaliados' => $jaAvaliados,
        ]);
    }


    // =========================
    // LIMPAR HISTÓRICO
    // =========================
    public function limparHistorico()
    {
        $this->middleware('auth');

        // Apenas clientes podem limpar histórico
        if (($_SESSION['user']['tipo_usuario'] ?? '') !== 'cliente') {
            $this->redirect('dashboard');
            return;
        }

        // CSRF obrigatório pois é ação destrutiva
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();
        }

        $clienteDAO = new ClienteDAO();
        $cliente = $clienteDAO->findByUsuarioId((int) $_SESSION['user']['id']);

        if (!$cliente) {
            $_SESSION['erro'] = "Perfil de cliente não encontrado.";
            $this->redirect('dashboard');
            return;
        }

        $agendamentoDAO = new AgendamentoDAO();

        // ✅ limpa pelo tempo (não por status)
        $agendamentoDAO->limparHistoricoCliente($cliente['id']);

        $_SESSION['success'] = "Histórico limpo com sucesso!";
        $this->redirect('agendamento/index');
    }


    // =========================
    // FORM CRIAÇÃO
    // Apenas clientes podem agendar
    // =========================
    public function create()
    {
        $this->middleware('auth');

        // Profissional e admin não agendam como cliente
        $tipo = $_SESSION['user']['tipo_usuario'] ?? 'cliente';
        if ($tipo === 'profissional') {
            $this->redirect('dashboard');
            return;
        }
        if ($tipo === 'admin') {
            $this->redirect('dashboard/agendamentos');
            return;
        }

        $profissionalDAO = new ProfissionalDAO();
        $servicoDAO      = new ServicoDAO();

        $erro    = $_SESSION['erro']    ?? null;
        $sucesso = $_SESSION['success'] ?? null;
        unset($_SESSION['erro'], $_SESSION['success']);

        $this->view('agendamento/create', [
            'profissionais' => $profissionalDAO->findAll(),
            'servicos'      => $servicoDAO->findAll(),
            'erro'          => $erro,
            'sucesso'       => $sucesso,
        ]);
    }


    // =========================
    // CANCELAR AGENDAMENTO
    // =========================
    public function cancelar(int $id = 0)
    {
        $this->middleware('auth');

        // Aceita POST (via form com CSRF) ou GET para compatibilidade
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();
        }

        if (!$id) {
            $_SESSION['erro'] = "Agendamento inválido.";
            $this->redirect('agendamento/index');
            return;
        }

        $clienteDAO = new ClienteDAO();
        $cliente = $clienteDAO->findByUsuarioId($_SESSION['user']['id']);

        if (!$cliente) {
            $_SESSION['erro'] = "Acesso inválido. Faça login novamente.";
        $this->redirect('auth/login');
        return;
        }

        $agendamentoDAO = new AgendamentoDAO();
        $agendamento = $agendamentoDAO->findById($id);

        if (!$agendamento || (int)$agendamento['cliente_id'] !== (int)$cliente['id']) {
            $_SESSION['erro'] = "Agendamento não encontrado.";
            $this->redirect('agendamento/index');
            return;
        }

        try {
            $service = new AgendamentoService();
            $service->cancelar($id, $agendamento);

            // Notificar cliente
            $notificacaoService = new NotificacaoService();
            $notificacaoService->notificarAgendamentoCancelado(
                (int)$_SESSION['user']['id'],
                $agendamento['servico'] ?? 'Serviço',
                $agendamento['data_agendamento']
            );

            $_SESSION['success'] = "Agendamento cancelado com sucesso.";
        } catch (Exception $e) {
            $_SESSION['erro'] = $e->getMessage();
        }

        $this->redirect('agendamento/index');
    }


    // =========================
    // REAGENDAR AGENDAMENTO
    // =========================
    public function reagendar()
    {
        $this->middleware('auth');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('agendamento/index');
            return;
        }

        $this->verifyCsrf();

        $agendamentoId = (int)($_POST['agendamento_id'] ?? 0);
        $novaData      = trim($_POST['data'] ?? '');
        $novaHora      = trim($_POST['hora'] ?? '');

        if (!$agendamentoId || !$novaData || !$novaHora) {
            $_SESSION['erro'] = "Preencha todos os campos para reagendar.";
            $this->redirect('agendamento/index');
            return;
        }

        $clienteDAO = new ClienteDAO();
        $cliente = $clienteDAO->findByUsuarioId($_SESSION['user']['id']);

        if (!$cliente) {
            $_SESSION['erro'] = "Acesso inválido. Faça login novamente.";
        $this->redirect('auth/login');
        return;
        }

        $agendamentoDAO = new AgendamentoDAO();
        $agendamento = $agendamentoDAO->findById($agendamentoId);

        if (!$agendamento || (int)$agendamento['cliente_id'] !== (int)$cliente['id']) {
            $_SESSION['erro'] = "Agendamento não encontrado.";
            $this->redirect('agendamento/index');
            return;
        }

        try {
            $servicoDAO = new ServicoDAO();
            $servico = $servicoDAO->findById($agendamento['servico_id']);

            $duracaoPersonalizada = $agendamentoDAO->getDuracaoServico(
                $agendamento['profissional_id'],
                $agendamento['servico_id']
            );

            $duracao = $duracaoPersonalizada !== null
                ? $duracaoPersonalizada + 30
                : ($servico['duracao_minutos'] ?? 60) + 30;

            $novaHoraFim = date('H:i:s', strtotime("+{$duracao} minutes", strtotime($novaHora)));

            $service = new AgendamentoService();
            $service->reagendar($agendamentoId, $agendamento, $novaData, $novaHora, $novaHoraFim);

            // Notificar cliente
            $notificacaoService = new NotificacaoService();
            $notificacaoService->notificarAgendamentoReagendado(
                (int)$_SESSION['user']['id'],
                $agendamento['servico'] ?? 'Serviço',
                $novaData,
                $novaHora
            );

            $_SESSION['success'] = "Agendamento reagendado com sucesso!";
        } catch (Exception $e) {
            $_SESSION['erro'] = $e->getMessage();
        }

        $this->redirect('agendamento/index');
    }


    // =========================
    // HORÁRIOS DISPONÍVEIS PARA REAGENDAMENTO (JSON)
    // =========================
    public function horariosReagendar()
    {
        $this->middleware('auth');

        $agendamentoId = (int)($_GET['agendamento_id'] ?? 0);
        $data          = trim($_GET['data'] ?? '');

        if (!$agendamentoId || !$data) {
            header('Content-Type: application/json');
            echo json_encode([]);
            exit;
        }

        $agendamentoDAO = new AgendamentoDAO();
        $agendamento    = $agendamentoDAO->findById($agendamentoId);

        if (!$agendamento) {
            header('Content-Type: application/json');
            echo json_encode([]);
            exit;
        }

        $servicoDAO = new ServicoDAO();
        $servico    = $servicoDAO->findById($agendamento['servico_id']);

        $duracaoPersonalizada = $agendamentoDAO->getDuracaoServico(
            $agendamento['profissional_id'],
            $agendamento['servico_id']
        );

        $duracao = $duracaoPersonalizada !== null
            ? $duracaoPersonalizada + 30
            : ($servico['duracao_minutos'] ?? 60) + 30;

        $service  = new AgendamentoService();
        $horarios = $service->gerarHorariosDisponiveis(
            $agendamento['profissional_id'],
            $data,
            $duracao
        );

        $disponiveis = array_values(array_map(
            fn($h) => $h['hora'],
            array_filter($horarios, fn($h) => $h['disponivel'])
        ));

        header('Content-Type: application/json');
        echo json_encode($disponiveis);
        exit;
    }


    // =========================
    // SALVAR AGENDAMENTO
    // Apenas clientes podem criar
    // =========================
    public function store()
    {
        $this->middleware('auth');

        // Apenas clientes podem criar agendamentos
        $tipo = $_SESSION['user']['tipo_usuario'] ?? 'cliente';
        if ($tipo !== 'cliente') {
            $_SESSION['erro'] = "Apenas clientes podem realizar agendamentos.";
            $this->redirect('dashboard');
            return;
        }

        if (!$_POST) return;

        $this->verifyCsrf();

        if (
            empty($_POST['profissional_id']) ||
            empty($_POST['servico_id']) ||
            empty($_POST['data']) ||
            empty($_POST['hora'])
        ) {
            $_SESSION['erro'] = "Preencha todos os campos.";
            $this->redirect('agendamento/create');
            return;
        }

        $clienteDAO = new ClienteDAO();
        $cliente    = $clienteDAO->findByUsuarioId($_SESSION['user']['id']);

        if (!$cliente) {
            $_SESSION['erro'] = "Perfil de cliente não encontrado. Contate a administração.";
            $this->redirect('dashboard');
            return;
        }

        $profissionalDAO = new ProfissionalDAO();
        if (!$profissionalDAO->findById($_POST['profissional_id'])) {
            $_SESSION['erro'] = "Profissional inválido";
            $this->redirect('agendamento/create');
            return;
        }

        $servicoDAO = new ServicoDAO();
        $servico = $servicoDAO->findById($_POST['servico_id']);

        if (!$servico) {
            $_SESSION['erro'] = "Serviço inválido";
            $this->redirect('agendamento/create');
            return;
        }

        $horaInicio = $_POST['hora'];

        $agendamentoDAO = new AgendamentoDAO();

        $duracaoPersonalizada = $agendamentoDAO->getDuracaoServico(
            $_POST['profissional_id'],
            $_POST['servico_id']
        );

        $duracao = $duracaoPersonalizada !== null
            ? $duracaoPersonalizada + 30
            : $servico['duracao_minutos'] + 30;

        $horaFim = date('H:i:s', strtotime("+$duracao minutes", strtotime($horaInicio)));

        $dados = [
            'cliente_id' => $cliente['id'],
            'profissional_id' => $_POST['profissional_id'],
            'servico_id' => $_POST['servico_id'],
            'data_agendamento' => $_POST['data'],
            'hora_inicio' => $horaInicio,
            'hora_fim' => $horaFim
        ];

        $service = new AgendamentoService();

        try {
            $service->create($dados);

            // Notificar cliente
            $notif = new NotificacaoService();
            $notif->notificarAgendamentoCriado(
                (int) $_SESSION['user']['id'],
                $servico['nome'],
                $dados['data_agendamento']
            );

            $_SESSION['success'] = "Agendamento realizado com sucesso! Você receberá uma confirmação.";
            $this->redirect('agendamento/index');

        } catch (Exception $e) {
            $_SESSION['erro'] = $e->getMessage();
            $this->redirect('agendamento/create');
        }
    }


    // ─────────────────────────────────────────────────────────────
    // API: duração do serviço para exibição no formulário
    // GET /agendamento/duracao?profissional_id=X&servico_id=Y
    // ─────────────────────────────────────────────────────────────
    public function duracao()
    {
        $this->middleware('auth');

        $profissionalId = (int)($_GET['profissional_id'] ?? 0);
        $servicoId      = (int)($_GET['servico_id']      ?? 0);

        if (!$profissionalId || !$servicoId) {
            header('Content-Type: application/json');
            echo json_encode(['duracao' => null]);
            exit;
        }

        $servicoDAO = new ServicoDAO();
        $servico    = $servicoDAO->findById($servicoId);

        $agendamentoDAO = new AgendamentoDAO();
        $duracaoPersonalizada = $agendamentoDAO->getDuracaoServico(
            $profissionalId,
            $servicoId
        );

        // Duração real do serviço (sem buffer de 30min)
        $duracao = $duracaoPersonalizada !== null
            ? $duracaoPersonalizada
            : ($servico['duracao_minutos'] ?? 60);

        header('Content-Type: application/json');
        echo json_encode(['duracao' => (int)$duracao]);
        exit;
    }

    public function horariosDisponiveis()
{
    $this->middleware('auth');

    $profissionalId = (int)($_GET['profissional_id'] ?? 0);
    $servicoId      = (int)($_GET['servico_id'] ?? 0);
    $data           = trim($_GET['data'] ?? '');

    if (!$profissionalId || !$servicoId || !$data) {
        echo json_encode([]);
        exit;
    }

    $servicoDAO = new ServicoDAO();
    $servico = $servicoDAO->findById($servicoId);

    $agendamentoDAO = new AgendamentoDAO();

    $duracaoPersonalizada = $agendamentoDAO->getDuracaoServico(
        $profissionalId,
        $servicoId
    );

    $duracao = $duracaoPersonalizada !== null
        ? $duracaoPersonalizada + 30
        : ($servico['duracao_minutos'] ?? 60) + 30;

    $service = new AgendamentoService();

    $horarios = $service->gerarHorariosDisponiveis(
        $profissionalId,
        $data,
        $duracao
    );

    $disponiveis = array_values(array_map(
        fn($h) => $h['hora'],
        array_filter($horarios, fn($h) => $h['disponivel'])
    ));

    header('Content-Type: application/json');
    echo json_encode($disponiveis);
    exit;
}

public function horarios()
{
    $this->middleware('auth');

    $profissionalId = (int)($_GET['profissional_id'] ?? 0);
    $servicoId      = (int)($_GET['servico_id'] ?? 0);
    $data           = trim($_GET['data'] ?? '');

    if (!$profissionalId || !$servicoId || !$data) {
        echo json_encode([]);
        exit;
    }

    $servicoDAO = new ServicoDAO();
    $servico = $servicoDAO->findById($servicoId);

    $agendamentoDAO = new AgendamentoDAO();

    $duracaoPersonalizada = $agendamentoDAO->getDuracaoServico(
        $profissionalId,
        $servicoId
    );

    $duracao = $duracaoPersonalizada !== null
        ? $duracaoPersonalizada + 30
        : ($servico['duracao_minutos'] ?? 60) + 30;

    $service = new AgendamentoService();

    $horarios = $service->gerarHorariosDisponiveis(
        $profissionalId,
        $data,
        $duracao
    );

    header('Content-Type: application/json');
    echo json_encode($horarios);
    exit;
}


}
