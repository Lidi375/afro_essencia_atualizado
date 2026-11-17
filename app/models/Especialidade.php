<?php

class Especialidade {

    public $id;
    public $nome;

    public function __construct($dados = []) {
        $this->id = $dados['id'] ?? null;
        $this->nome = $dados['nome'] ?? '';
    }
}