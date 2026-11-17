<?php

// Representa a tabela clientes
class Cliente {

    public $id;
    public $usuario_id;
    public $data_nascimento;
    public $instagram;
    public $observacoes;

    public function __construct($dados = []) {
        $this->id = $dados['id'] ?? null;
        $this->usuario_id = $dados['usuario_id'] ?? null;
        $this->data_nascimento = $dados['data_nascimento'] ?? null;
        $this->instagram = $dados['instagram'] ?? null;
        $this->observacoes = $dados['observacoes'] ?? null;
    }
}
