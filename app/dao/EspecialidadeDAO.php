<?php

class EspecialidadeDAO extends BaseDAO {

    public function findAll() {

        return $this->conn->query("SELECT * FROM especialidades")
            ->fetchAll(PDO::FETCH_ASSOC);
    }
}
