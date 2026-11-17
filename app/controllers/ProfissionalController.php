<?php

class ProfissionalController extends BaseController
{
    /**
     * Lista profissionais ativos (página pública)
     */
    public function index()
    {
        $profissionais = (new ProfissionalDAO())->findAll();
        $this->view('profissional/index', ['profissionais' => $profissionais]);
    }

    /**
     * Perfil público do profissional
     */
    public function profile($id = null)
    {
        if (!$id) {
            $this->redirect('profissional/index');
            return;
        }

        $profissional = (new ProfissionalDAO())->findById((int) $id);

        if (!$profissional) {
            http_response_code(404);
            echo "<h2>Profissional não encontrado.</h2>";
            return;
        }

        $avaliacoes = (new AvaliacaoDAO())->findByProfissional((int) $id);

        $this->view('profissional/profile', [
            'profissional' => $profissional,
            'avaliacoes'   => $avaliacoes,
        ]);
    }

    /**
     * Desativar profissional (apenas admin)
     */
    public function desligar($id = null)
    {
        $this->middleware('admin');

        if (!$id) {
            $_SESSION['erro'] = "ID inválido.";
            $this->redirect('dashboard/profissionais');
            return;
        }

        $dao = new ProfissionalDAO();
        if ($dao->desativar((int) $id)) {
            // Reverte tipo_usuario para 'cliente'
            $prof = $dao->findById((int) $id);
            if ($prof) {
                $conn = Database::getConnection();
                $conn->prepare("UPDATE usuarios SET tipo_usuario = 'cliente' WHERE id = ?")
                     ->execute([$prof['usuario_id']]);
            }
            $_SESSION['success'] = "Profissional desativado com sucesso.";
        } else {
            $_SESSION['erro'] = "Erro ao desativar profissional.";
        }

        $this->redirect('dashboard/profissionais');
    }
}
