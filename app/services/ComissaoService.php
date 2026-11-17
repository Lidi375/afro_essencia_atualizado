<?php

/**
 * ComissaoService — lógica de negócio do módulo de comissões.
 *
 * REGRA DE NEGÓCIO CENTRAL:
 *   Comissão só é gerada quando:
 *     agendamento.status = 'concluido'
 *   E
 *     pagamento.status_pagamento = 'pago'
 *
 *   Ambas as condições devem ser verdadeiras simultaneamente.
 *   A comissão é gerada UMA ÚNICA VEZ por agendamento (idempotência via UNIQUE KEY).
 */
class ComissaoService
{
    private ComissaoDAO $comissaoDAO;
    private PagamentoDAO $pagamentoDAO;
    private AgendamentoDAO $agendamentoDAO;

    public function __construct()
    {
        $this->comissaoDAO    = new ComissaoDAO();
        $this->pagamentoDAO   = new PagamentoDAO();
        $this->agendamentoDAO = new AgendamentoDAO();
    }

    // ═══════════════════════════════════════════════════════
    // GERAÇÃO DE COMISSÃO
    // ═══════════════════════════════════════════════════════

    /**
     * Ponto de entrada principal: verifica condições e gera comissão se válido.
     * Deve ser chamado pelo AgendamentoService ao concluir um agendamento,
     * ou pelo PagamentoService ao aprovar um pagamento.
     *
     * @param  int $agendamentoId
     * @return bool  true se comissão foi gerada, false se condições não cumpridas
     */
    public function tentarGerarComissao(int $agendamentoId): bool
    {
        // 1. Busca o agendamento
        $agendamento = $this->agendamentoDAO->findById($agendamentoId);
        if (!$agendamento) {
            return false;
        }

        // 2. Verifica status do agendamento
        if ($agendamento['status'] !== 'concluido') {
            return false;
        }

        // 3. Verifica pagamento aprovado
        $pagamento = $this->pagamentoDAO->findByAgendamento($agendamentoId);
        if (!$pagamento || $pagamento['status_pagamento'] !== 'pago') {
            return false;
        }

        // 4. Verifica idempotência — não gera comissão duplicada
        if ($this->comissaoDAO->existeParaAgendamento($agendamentoId)) {
            return false;
        }

        // 5. Busca configuração de comissão do profissional
        $config = $this->comissaoDAO->findConfigAtiva((int)$agendamento['profissional_id']);
        if (!$config) {
            return false; // Profissional sem configuração: não gera comissão
        }

        // 6. Calcula valor da comissão
        $valorServico = (float)($pagamento['valor'] ?? $agendamento['valor_final'] ?? 0);
        if ($valorServico <= 0) {
            return false;
        }

        $valorComissao = $this->calcularComissao($valorServico, $config);

        // 7. Cria a comissão
        $comissaoId = $this->comissaoDAO->create([
            'profissional_id' => (int)$agendamento['profissional_id'],
            'agendamento_id'  => $agendamentoId,
            'config_id'       => (int)$config['id'],
            'valor_servico'   => $valorServico,
            'tipo_comissao'   => $config['tipo'],
            'percentual'      => $config['tipo'] === 'percentual' ? (float)$config['percentual'] : null,
            'valor_comissao'  => $valorComissao,
            'observacoes'     => null,
        ]);

        // 8. Registra no log de auditoria
        $this->comissaoDAO->log(
            'comissao_gerada',
            'comissao',
            $comissaoId,
            null, // gerado automaticamente pelo sistema
            [
                'agendamento_id' => $agendamentoId,
                'profissional_id' => $agendamento['profissional_id'],
                'valor_servico'   => $valorServico,
                'valor_comissao'  => $valorComissao,
                'tipo'            => $config['tipo'],
            ]
        );

        return true;
    }

    /**
     * Calcula o valor de comissão com base na configuração.
     */
    private function calcularComissao(float $valorServico, array $config): float
    {
        if ($config['tipo'] === 'percentual') {
            $pct = (float)($config['percentual'] ?? 0);
            return round($valorServico * ($pct / 100), 2);
        }

        // fixo
        return round((float)($config['valor_fixo'] ?? 0), 2);
    }

    // ═══════════════════════════════════════════════════════
    // CONFIGURAÇÃO (somente admin)
    // ═══════════════════════════════════════════════════════

    /**
     * Salva ou atualiza a configuração de comissão de um profissional.
     * Validações completas antes de persistir.
     */
    public function salvarConfig(array $dados, int $adminId): int
    {
        // Validações
        if (empty($dados['profissional_id'])) {
            throw new Exception("Profissional obrigatório.");
        }
        if (empty($dados['tipo']) || !in_array($dados['tipo'], ['percentual', 'fixo'], true)) {
            throw new Exception("Tipo de comissão inválido. Use 'percentual' ou 'fixo'.");
        }
        if ($dados['tipo'] === 'percentual') {
            $pct = (float)($dados['percentual'] ?? 0);
            if ($pct <= 0 || $pct > 100) {
                throw new Exception("Percentual deve ser entre 0.01 e 100.");
            }
            $dados['percentual'] = $pct;
            $dados['valor_fixo'] = null;
        } else {
            $vf = (float)($dados['valor_fixo'] ?? 0);
            if ($vf <= 0) {
                throw new Exception("Valor fixo deve ser maior que zero.");
            }
            $dados['valor_fixo']  = $vf;
            $dados['percentual']  = null;
        }

        $configId = $this->comissaoDAO->salvarConfig($dados, $adminId);

        $this->comissaoDAO->log(
            'config_alterada',
            'config_comissao',
            $configId,
            $adminId,
            $dados
        );

        return $configId;
    }

    // ═══════════════════════════════════════════════════════
    // PAGAMENTO DE COMISSÃO (somente admin)
    // ═══════════════════════════════════════════════════════

    /**
     * Marca uma comissão como paga.
     */
    public function marcarComoPago(int $comissaoId, string $formaPagamento, int $adminId): void
    {
        $formasValidas = ['pix', 'transferencia', 'dinheiro', 'outro'];
        if (!in_array($formaPagamento, $formasValidas, true)) {
            throw new Exception("Forma de pagamento inválida.");
        }

        $comissao = $this->comissaoDAO->findById($comissaoId);
        if (!$comissao) {
            throw new Exception("Comissão não encontrada.");
        }
        if ($comissao['status_pagamento'] !== 'pendente') {
            throw new Exception("Apenas comissões pendentes podem ser marcadas como pagas.");
        }

        $ok = $this->comissaoDAO->marcarPago($comissaoId, $formaPagamento, $adminId);
        if (!$ok) {
            throw new Exception("Falha ao atualizar status da comissão.");
        }

        $this->comissaoDAO->log(
            'comissao_paga',
            'comissao',
            $comissaoId,
            $adminId,
            ['forma_pagamento' => $formaPagamento]
        );
    }

    /**
     * Cancela uma comissão pendente.
     */
    public function cancelar(int $comissaoId, int $adminId): void
    {
        $comissao = $this->comissaoDAO->findById($comissaoId);
        if (!$comissao) {
            throw new Exception("Comissão não encontrada.");
        }
        if ($comissao['status_pagamento'] !== 'pendente') {
            throw new Exception("Apenas comissões pendentes podem ser canceladas.");
        }

        $this->comissaoDAO->cancelar($comissaoId, $adminId);

        $this->comissaoDAO->log(
            'comissao_cancelada',
            'comissao',
            $comissaoId,
            $adminId,
            []
        );
    }

    // ═══════════════════════════════════════════════════════
    // CONSULTAS (expostas ao controller)
    // ═══════════════════════════════════════════════════════

    public function findAll(array $filtros = []): array
    {
        return $this->comissaoDAO->findAll($filtros);
    }

    /**
     * Lista comissões de um profissional AUTENTICADO.
     * Segurança: usa profissional_id derivado da sessão.
     */
    public function findByProfissional(int $profissionalId, array $filtros = []): array
    {
        return $this->comissaoDAO->findByProfissional($profissionalId, $filtros);
    }

    public function findAllConfigs(): array
    {
        return $this->comissaoDAO->findAllConfigs();
    }

    public function findConfigAtiva(int $profissionalId): ?array
    {
        return $this->comissaoDAO->findConfigAtiva($profissionalId);
    }

    public function totaisAdmin(array $filtros = []): array
    {
        return $this->comissaoDAO->totaisAdmin($filtros);
    }

    public function totaisPorProfissional(array $filtros = []): array
    {
        return $this->comissaoDAO->totaisPorProfissional($filtros);
    }

    public function totaisProfissional(int $profissionalId): array
    {
        return $this->comissaoDAO->totaisProfissional($profissionalId);
    }

    public function evolucaoMensal(int $profissionalId = 0): array
    {
        return $this->comissaoDAO->evolucaoMensal($profissionalId);
    }

    public function findById(int $id): ?array
    {
        return $this->comissaoDAO->findById($id);
    }
}
