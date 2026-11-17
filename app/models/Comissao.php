<?php

/**
 * Model: Comissao
 * Representa uma comissão gerada para um profissional.
 */
class Comissao
{
    public int    $id            = 0;
    public int    $profissionalId;
    public int    $agendamentoId;
    public ?int   $configId      = null;
    public float  $valorServico;
    public string $tipoComissao; // 'percentual' | 'fixo'
    public ?float $percentual    = null;
    public float  $valorComissao;
    public string $statusPagamento = 'pendente'; // 'pendente' | 'pago' | 'cancelado'
    public ?string $formaPagamento = null;
    public string $dataGeracao;
    public ?string $dataPagamento  = null;
    public ?int    $pagoPor        = null;
    public ?string $observacoes    = null;
    public string  $createdAt      = '';
    public string  $updatedAt      = '';

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            $camel = lcfirst(str_replace('_', '', ucwords($key, '_')));
            if (property_exists($this, $camel)) {
                $this->$camel = $value;
            }
        }
    }
}
