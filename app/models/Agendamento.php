<?php

// Representa a tabela agendamentos
class Agendamento {

    public $id;
    public $cliente_id;
    public $profissional_id;
    public $servico_id;
    public $data_agendamento;
    public $hora_inicio;
    public $hora_fim;
    public $status;
    public $observacoes;
    public $valor_final;

    public function __construct($dados = []) {
        $this->id = $dados['id'] ?? null;
        $this->cliente_id = $dados['cliente_id'] ?? null;
        $this->profissional_id = $dados['profissional_id'] ?? null;
        $this->servico_id = $dados['servico_id'] ?? null;
        $this->data_agendamento = $dados['data_agendamento'] ?? null;
        $this->hora_inicio = $dados['hora_inicio'] ?? null;
        $this->hora_fim = $dados['hora_fim'] ?? null;
        $this->status = $dados['status'] ?? 'pendente';
        $this->observacoes = $dados['observacoes'] ?? null;
        $this->valor_final = $dados['valor_final'] ?? null;
    }
}
