<?php

class BaseDAO {

    protected $conn;

    // Conexão única reutilizável
    public function __construct() {
        $this->conn = Database::getConnection();
    }
}