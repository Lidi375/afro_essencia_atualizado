<?php

/**
 * TrabalheconoscoController
 * Controla o formulário público de candidatura "Trabalhe Conosco".
 */
class TrabalheconoscoController extends BaseController
{
    /**
     * Exibe o formulário público de candidatura.
     */
    public function index()
    {
        $erro     = $_SESSION['erro']    ?? null;
        $sucesso  = $_SESSION['success'] ?? null;
        unset($_SESSION['erro'], $_SESSION['success']);

        $this->view('trabalhe-conosco/index', [
            'erro'    => $erro,
            'sucesso' => $sucesso,
        ]);
    }

    /**
     * Processa o envio do formulário de candidatura.
     */
    public function submeter()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('trabalheconosco');
            return;
        }

        $this->verifyCsrf();

        try {
            $service = new CandidaturaService();
            $id      = $service->submeter($_POST, $_FILES);

            $_SESSION['success'] = "Sua candidatura foi enviada com sucesso! " .
                "Entraremos em contato em breve. (Protocolo #{$id})";

        } catch (Exception $e) {
            $_SESSION['erro'] = $e->getMessage();
        }

        $this->redirect('trabalheconosco');
    }
}
