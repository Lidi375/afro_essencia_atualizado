<?php

/**
 * AnamneseController
 *
 * Rotas:
 *   GET  /afro_essencia/anamnese/form   → exibe o formulário (novo ou edição)
 *   POST /afro_essencia/anamnese/salvar → processa e salva
 *   GET  /afro_essencia/anamnese/show   → exibe a ficha preenchida do cliente
 */
class AnamneseController extends BaseController
{
    // ==============================================
    // FORMULÁRIO (cliente)
    // ==============================================
    public function form()
    {
        $this->middleware('auth');

        $clienteDAO = new ClienteDAO();
        $cliente    = $clienteDAO->findByUsuarioId($_SESSION['user']['id']);

        if (!$cliente) {
            $_SESSION['erro'] = 'Acesso inválido. Faça login novamente.';
        $this->redirect('auth/login');
        return;
        }

        $service  = new AnamneseService();
        $anamnese = $service->getByClienteId($cliente['id']);

        $erro     = $_SESSION['anamnese_erro']    ?? null;
        $sucesso  = $_SESSION['anamnese_sucesso'] ?? null;
        unset($_SESSION['anamnese_erro'], $_SESSION['anamnese_sucesso']);

        $this->view('anamnese/form', [
            'anamnese' => $anamnese,
            'erro'     => $erro,
            'sucesso'  => $sucesso,
        ]);
    }

    // ==============================================
    // SALVAR (cliente) — processa POST
    // ==============================================
    public function salvar()
    {
        $this->middleware('auth');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('anamnese/form');
            return;
        }

        $this->verifyCsrf();

        $clienteDAO = new ClienteDAO();
        $cliente    = $clienteDAO->findByUsuarioId($_SESSION['user']['id']);

        if (!$cliente) {
            $_SESSION['erro'] = 'Acesso inválido. Faça login novamente.';
        $this->redirect('auth/login');
        return;
        }

        try {
            $service = new AnamneseService();
            $service->salvar($cliente['id'], $_POST);

            $_SESSION['anamnese_sucesso'] = 'Ficha de anamnese salva com sucesso!';
        } catch (Exception $e) {
            $_SESSION['anamnese_erro'] = 'Erro ao salvar: ' . $e->getMessage();
        }

        $this->redirect('anamnese/show');
    }

    // ==============================================
    // VISUALIZAR FICHA (cliente)
    // ==============================================
    public function show()
    {
        $this->middleware('auth');

        $clienteDAO = new ClienteDAO();
        $cliente    = $clienteDAO->findByUsuarioId($_SESSION['user']['id']);

        if (!$cliente) {
            $_SESSION['erro'] = 'Acesso inválido. Faça login novamente.';
        $this->redirect('auth/login');
        return;
        }

        $service  = new AnamneseService();
        $anamnese = $service->getByClienteId($cliente['id']);

        // Se ainda não preencheu, redireciona para o formulário
        if (!$anamnese) {
            $_SESSION['anamnese_info'] = 'Preencha sua ficha de anamnese antes de visualizá-la.';
            $this->redirect('anamnese/form');
            return;
        }

        $sucesso = $_SESSION['anamnese_sucesso'] ?? null;
        unset($_SESSION['anamnese_sucesso']);

        $this->view('anamnese/show', [
            'anamnese' => $anamnese,
            'sucesso'  => $sucesso,
        ]);
    }
}
