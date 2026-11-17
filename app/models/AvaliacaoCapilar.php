<?php

// Representa avaliacoes_capilares
class AvaliacaoCapilar {

    public $id;
    public $cliente_id;
    public $tipo_cabelo;
    public $nivel_hidratacao;
    public $observacoes;

    public function __construct($dados = []) {
        $this->id = $dados['id'] ?? null;
        $this->cliente_id = $dados['cliente_id'] ?? null;
        $this->tipo_cabelo = $dados['tipo_cabelo'] ?? null;
        $this->nivel_hidratacao = $dados['nivel_hidratacao'] ?? null;
        $this->observacoes = $dados['observacoes'] ?? null;
    }
}
