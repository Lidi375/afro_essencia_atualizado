<?php

class Galeria {

    public $id;
    public $imagem;
    public $titulo;
    public $descricao;

    public function __construct($dados = []) {
        $this->id = $dados['id'] ?? null;
        $this->imagem = $dados['imagem'] ?? '';
        $this->titulo = $dados['titulo'] ?? null;
        $this->descricao = $dados['descricao'] ?? null;
    }
}
