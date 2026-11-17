<?php

class AvaliacaoCapilarDAO extends BaseDAO {

    // Salvar avaliação capilar personalizada
    public function create($dados) {

        $sql = "INSERT INTO avaliacoes_capilares
                (cliente_id, tipo_cabelo, nivel_hidratacao, observacoes)
                VALUES (?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            $dados['cliente_id'],
            $dados['tipo_cabelo'],
            $dados['hidratacao'],
            $dados['obs']
        ]);
    }
}