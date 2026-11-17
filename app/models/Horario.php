<?php

class Horario {

    public $id;
    public $profissional_id;
    public $dia_semana;
    public $hora_inicio;
    public $hora_fim;
    public $disponivel;

    public function __construct($dados = []) {
        $this->id = $dados['id'] ?? null;
        $this->profissional_id = $dados['profissional_id'] ?? null;
        $this->dia_semana = $dados['dia_semana'] ?? null;
        $this->hora_inicio = $dados['hora_inicio'] ?? null;
        $this->hora_fim = $dados['hora_fim'] ?? null;
        $this->disponivel = $dados['disponivel'] ?? 1;
    }
}
