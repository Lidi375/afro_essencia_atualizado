<?php

/**
 * Model: Anamnese
 * Representa a ficha de anamnese capilar do cliente.
 */
class Anamnese
{
    public $id;
    public $cliente_id;

    // Identificação capilar
    public $tipo_cabelo;
    public $subtipo_cabelo;
    public $nivel_hidratacao;
    public $comprimento;
    public $densidade;

    // Histórico químico
    public $usa_quimica;
    public $tipo_quimica;
    public $ultima_quimica;

    // Hábitos e saúde
    public $frequencia_lavagem;
    public $usa_calor;
    public $frequencia_calor;
    public $problemas_couro;
    public $alergias;

    // Objetivo
    public $objetivo;
    public $servicos_interesse;
    public $observacoes;

    public $created_at;
    public $updated_at;

    public function __construct(array $dados = [])
    {
        $this->id                 = $dados['id']                 ?? null;
        $this->cliente_id         = $dados['cliente_id']         ?? null;
        $this->tipo_cabelo        = $dados['tipo_cabelo']        ?? null;
        $this->subtipo_cabelo     = $dados['subtipo_cabelo']     ?? null;
        $this->nivel_hidratacao   = $dados['nivel_hidratacao']   ?? null;
        $this->comprimento        = $dados['comprimento']        ?? null;
        $this->densidade          = $dados['densidade']          ?? null;
        $this->usa_quimica        = $dados['usa_quimica']        ?? 0;
        $this->tipo_quimica       = $dados['tipo_quimica']       ?? null;
        $this->ultima_quimica     = $dados['ultima_quimica']     ?? null;
        $this->frequencia_lavagem = $dados['frequencia_lavagem'] ?? null;
        $this->usa_calor          = $dados['usa_calor']          ?? 0;
        $this->frequencia_calor   = $dados['frequencia_calor']   ?? null;
        $this->problemas_couro    = $dados['problemas_couro']    ?? null;
        $this->alergias           = $dados['alergias']           ?? null;
        $this->objetivo           = $dados['objetivo']           ?? null;
        $this->servicos_interesse = $dados['servicos_interesse'] ?? null;
        $this->observacoes        = $dados['observacoes']        ?? null;
        $this->created_at         = $dados['created_at']         ?? null;
        $this->updated_at         = $dados['updated_at']         ?? null;
    }
}
