<?php

// Representa avaliacoes_profissionais
class Avaliacao {

    public $id;
    public $agendamento_id;
    public $profissional_id;
    public $cliente_id;
    public $nota;
    public $comentario;

    public function __construct($dados = []) {
        $this->id = $dados['id'] ?? null;
        $this->agendamento_id = $dados['agendamento_id'] ?? null;
        $this->profissional_id = $dados['profissional_id'] ?? null;
        $this->cliente_id = $dados['cliente_id'] ?? null;
        $this->nota = $dados['nota'] ?? 0;
        $this->comentario = $dados['comentario'] ?? null;
    }
}
