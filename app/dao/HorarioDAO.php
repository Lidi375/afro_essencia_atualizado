<?php

class HorarioDAO extends BaseDAO {

    // Buscar horários do profissional
    public function findByProfissional($id) {

        $stmt = $this->conn->prepare(
            "SELECT * FROM horarios WHERE profissional_id = ?"
        );

        $stmt->execute([$id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
