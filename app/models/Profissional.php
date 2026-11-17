<?php

// Representa a tabela profissionais
class Profissional {

    public $id;
    public $usuario_id;
    public $biografia;
    public $experiencia_anos;
    public $atende_domicilio;
    public $avaliacao_media;

    public function __construct($dados = []) {
        $this->id = $dados['id'] ?? null;
        $this->usuario_id = $dados['usuario_id'] ?? null;
        $this->biografia = $dados['biografia'] ?? '';
        $this->experiencia_anos = $dados['experiencia_anos'] ?? 0;
        $this->atende_domicilio = $dados['atende_domicilio'] ?? 0;
        $this->avaliacao_media = $dados['avaliacao_media'] ?? 0;
    }
}
