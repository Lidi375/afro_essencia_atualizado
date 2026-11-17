<?php

class ClienteService {

    private $clienteDAO;

    public function __construct() {
        $this->clienteDAO = new ClienteDAO();
    }

    // Buscar cliente por usuário
    public function getCliente($usuario_id) {
        return $this->clienteDAO->findByUsuarioId($usuario_id);
    }
}