<?php

class GaleriaDAO extends BaseDAO {

    // Listar imagens
    public function findAll() {

        return $this->conn->query("SELECT * FROM galerias")
            ->fetchAll(PDO::FETCH_ASSOC);
    }
}