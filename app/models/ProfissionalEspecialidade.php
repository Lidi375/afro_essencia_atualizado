<?php

class ProfissionalEspecialidade {

    public $profissional_id;
    public $especialidade_id;

    public function __construct($dados = []) {
        $this->profissional_id = $dados['profissional_id'] ?? null;
        $this->especialidade_id = $dados['especialidade_id'] ?? null;
    }
}