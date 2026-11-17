<?php

// Representa a tabela usuarios
class Usuario {

    public $id;
    public $nome;
    public $email;
    public $senha;
    public $tipo_usuario;
    public $telefone;
    public $foto;
    public $status;

    public function __construct($dados = []) {
        $this->id = $dados['id'] ?? null;
        $this->nome = $dados['nome'] ?? '';
        $this->email = $dados['email'] ?? '';
        $this->senha = $dados['senha'] ?? '';
        $this->tipo_usuario = $dados['tipo_usuario'] ?? 'cliente';
        $this->telefone = $dados['telefone'] ?? null;
        $this->foto = $dados['foto'] ?? null;
        $this->status = $dados['status'] ?? 'ativo';
    }
}
