<?php

/**
 * Model: ConfigComissao
 * Configuração de comissão de um profissional (percentual ou valor fixo).
 */
class ConfigComissao
{
    public int    $id;
    public int    $profissionalId;
    public string $tipo       = 'percentual'; // 'percentual' | 'fixo'
    public ?float $percentual = null;
    public ?float $valorFixo  = null;
    public bool   $ativo      = true;
    public ?string $observacoes = null;
    public ?int   $createdBy  = null;
    public ?int   $updatedBy  = null;
    public string $createdAt  = '';
    public string $updatedAt  = '';

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
