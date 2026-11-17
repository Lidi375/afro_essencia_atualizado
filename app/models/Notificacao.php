<?php

class Notificacao {

    public $id;
    public $usuario_id;
    public $titulo;
    public $mensagem;
    public $lida;

    public function __construct($dados = []) {
        $this->id = $dados['id'] ?? null;
        $this->usuario_id = $dados['usuario_id'] ?? null;
        $this->titulo = $dados['titulo'] ?? '';
        $this->mensagem = $dados['mensagem'] ?? '';
        $this->lida = $dados['lida'] ?? 0;
    }
}
