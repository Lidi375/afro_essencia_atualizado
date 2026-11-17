<?php

class ProfissionalEspecialidadeDAO extends BaseDAO {

    // Vincular especialidade ao profissional
    public function create($profissional_id, $especialidade_id) {

        $stmt = $this->conn->prepare(
            "INSERT INTO profissional_especialidade (profissional_id, especialidade_id)
             VALUES (?, ?)"
        );

        return $stmt->execute([$profissional_id, $especialidade_id]);
    }
}
