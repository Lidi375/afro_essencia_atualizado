<?php

// Representa a tabela servicos
class Servico {

    public $id;
    public $categoria_id;
    public $nome;
    public $descricao;
    public $valor;
    public $duracao_minutos;
    public $nivel_dificuldade;
    public $imagem;
    public $status;

    public function __construct($dados = []) {
        $this->id = $dados['id'] ?? null;
        $this->categoria_id = $dados['categoria_id'] ?? null;
        $this->nome = $dados['nome'] ?? '';
        $this->descricao = $dados['descricao'] ?? '';
        $this->valor = $dados['valor'] ?? 0;
        $this->duracao_minutos = $dados['duracao_minutos'] ?? 0;
        $this->nivel_dificuldade = $dados['nivel_dificuldade'] ?? null;
        $this->imagem = $dados['imagem'] ?? null;
        $this->status = $dados['status'] ?? 'ativo';
    }
}
