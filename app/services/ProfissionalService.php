<?php

class ProfissionalService {

    private $profissionalDAO;

    public function __construct() {
        $this->profissionalDAO = new ProfissionalDAO();
    }

    // Listar profissionais
    public function listar() {
        return $this->profissionalDAO->findAll();
    }
}
