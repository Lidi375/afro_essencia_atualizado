<?php

/**
 * AvaliacaoDAO — avaliações de profissionais.
 */
class AvaliacaoDAO extends BaseDAO
{
    public function create(array $dados): bool
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO avaliacoes_profissionais
                (agendamento_id, profissional_id, cliente_id, nota, comentario)
             VALUES (?, ?, ?, ?, ?)"
        );
        $ok = $stmt->execute([
            $dados['agendamento_id'],
            $dados['profissional_id'],
            $dados['cliente_id'],
            $dados['nota'],
            $dados['comentario'] ?? null,
        ]);

        if ($ok) {
            $this->atualizarMediaProfissional((int) $dados['profissional_id']);
        }

        return $ok;
    }

    public function findAll(): array
    {
        return $this->conn->query(
            "SELECT av.*,
                    uc.nome AS cliente_nome,
                    up.nome AS profissional_nome
             FROM avaliacoes_profissionais av
             JOIN clientes      c  ON av.cliente_id      = c.id
             JOIN usuarios      uc ON c.usuario_id       = uc.id
             JOIN profissionais p  ON av.profissional_id = p.id
             JOIN usuarios      up ON p.usuario_id       = up.id
             ORDER BY av.created_at DESC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByProfissional(int $profissionalId): array
    {
        $stmt = $this->conn->prepare(
            "SELECT av.*,
                    uc.nome AS cliente_nome
             FROM avaliacoes_profissionais av
             JOIN clientes c  ON av.cliente_id = c.id
             JOIN usuarios uc ON c.usuario_id  = uc.id
             WHERE av.profissional_id = ?
             ORDER BY av.created_at DESC"
        );
        $stmt->execute([$profissionalId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByCliente(int $clienteId): array
    {
        $stmt = $this->conn->prepare(
            "SELECT av.*,
                    up.nome AS profissional_nome,
                    s.nome  AS servico_nome
             FROM avaliacoes_profissionais av
             JOIN profissionais p  ON av.profissional_id = p.id
             JOIN usuarios      up ON p.usuario_id       = up.id
             JOIN agendamentos  a  ON av.agendamento_id  = a.id
             JOIN servicos      s  ON a.servico_id       = s.id
             WHERE av.cliente_id = ?
             ORDER BY av.created_at DESC"
        );
        $stmt->execute([$clienteId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function jaAvaliou(int $agendamentoId): bool
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM avaliacoes_profissionais WHERE agendamento_id = ?"
        );
        $stmt->execute([$agendamentoId]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Retorna um array indexado pelos agendamento_ids que já foram avaliados.
     * Permite pré-calcular no controller com 1 única query em vez de N queries na view.
     *
     * @param  int[] $agendamentoIds
     * @return array<int, true>
     */
    public function findAgendamentosAvaliados(array $agendamentoIds): array
    {
        if (empty($agendamentoIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($agendamentoIds), '?'));
        $stmt = $this->conn->prepare(
            "SELECT DISTINCT agendamento_id
             FROM avaliacoes_profissionais
             WHERE agendamento_id IN ({$placeholders})"
        );
        $stmt->execute(array_values($agendamentoIds));

        $result = [];
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $id) {
            $result[(int)$id] = true;
        }
        return $result;
    }

    private function atualizarMediaProfissional(int $profissionalId): void
    {
        $stmt = $this->conn->prepare(
            "UPDATE profissionais
             SET avaliacao_media = (
                 SELECT ROUND(AVG(nota), 1)
                 FROM avaliacoes_profissionais
                 WHERE profissional_id = ?
             )
             WHERE id = ?"
        );
        $stmt->execute([$profissionalId, $profissionalId]);
    }
}
