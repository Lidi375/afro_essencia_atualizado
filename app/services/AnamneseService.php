<?php

/**
 * AnamneseService
 * Regras de negócio para a ficha de anamnese capilar.
 */
class AnamneseService
{
    private AnamneseDAO $dao;

    public function __construct()
    {
        $this->dao = new AnamneseDAO();
    }

    /**
     * Salva ou atualiza a anamnese do cliente.
     * Cada cliente tem apenas UMA ficha (upsert).
     */
    public function salvar(int $clienteId, array $post): void
    {
        $dados = $this->sanitizar($clienteId, $post);

        $existente = $this->dao->findByClienteId($clienteId);

        if ($existente) {
            $this->dao->update($existente['id'], $dados);
        } else {
            $this->dao->create($dados);
        }
    }

    /**
     * Retorna a ficha do cliente ou null se não existir.
     */
    public function getByClienteId(int $clienteId): ?array
    {
        return $this->dao->findByClienteId($clienteId);
    }

    /**
     * Retorna todas as fichas para o admin.
     */
    public function listarAdmin(): array
    {
        return $this->dao->findAllAdmin();
    }

    /**
     * Retorna detalhe completo de uma ficha (admin).
     */
    public function detalheAdmin(int $id): ?array
    {
        return $this->dao->findDetalheAdmin($id);
    }

    /**
     * Saneia e formata os dados vindos do POST.
     */
    private function sanitizar(int $clienteId, array $post): array
    {
        return [
            'cliente_id'         => $clienteId,
            'tipo_cabelo'        => $this->str($post['tipo_cabelo']        ?? ''),
            'subtipo_cabelo'     => $this->str($post['subtipo_cabelo']     ?? ''),
            'nivel_hidratacao'   => $this->str($post['nivel_hidratacao']   ?? ''),
            'comprimento'        => $this->str($post['comprimento']        ?? ''),
            'densidade'          => $this->str($post['densidade']          ?? ''),
            'usa_quimica'        => isset($post['usa_quimica'])  ? 1 : 0,
            'tipo_quimica'       => $this->str($post['tipo_quimica']       ?? ''),
            'ultima_quimica'     => $this->str($post['ultima_quimica']     ?? ''),
            'frequencia_lavagem' => $this->str($post['frequencia_lavagem'] ?? ''),
            'usa_calor'          => isset($post['usa_calor'])    ? 1 : 0,
            'frequencia_calor'   => $this->str($post['frequencia_calor']   ?? ''),
            'problemas_couro'    => $this->str($post['problemas_couro']    ?? ''),
            'alergias'           => $this->str($post['alergias']           ?? ''),
            'objetivo'           => $this->str($post['objetivo']           ?? ''),
            'servicos_interesse' => $this->str($post['servicos_interesse'] ?? ''),
            'observacoes'        => $this->str($post['observacoes']        ?? ''),
        ];
    }

    private function str(string $val): ?string
    {
        $v = trim(strip_tags($val));
        return $v === '' ? null : $v;
    }
}
