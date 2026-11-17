<?php

/**
 * NotificacaoController
 *
 * GET  /notificacao/index    → lista notificações do usuário logado
 * GET  /notificacao/marcarLidas → marca todas como lidas e redireciona
 */
class NotificacaoController extends BaseController
{
    public function index()
    {
        $this->middleware('auth');

        $service = new NotificacaoService();
        $service->marcarTodasLidas((int) $_SESSION['user']['id']);

        $notificacoes = $service->listar((int) $_SESSION['user']['id']);

        $this->view('notificacao/index', [
            'notificacoes' => $notificacoes,
        ]);
    }

    public function marcarLidas()
    {
        $this->middleware('auth');
        $this->verifyCsrf();
        (new NotificacaoService())->marcarTodasLidas((int) $_SESSION['user']['id']);
        $this->redirect('notificacao/index');
    }
}
