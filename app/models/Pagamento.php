<?php

// Representa a tabela pagamentos
class Pagamento {

    public $id;
    public $agendamento_id;
    public $metodo_pagamento;
    public $valor;
    public $status_pagamento;
    public $data_pagamento;

    public function __construct($dados = []) {
        $this->id = $dados['id'] ?? null;
        $this->agendamento_id = $dados['agendamento_id'] ?? null;
        $this->metodo_pagamento = $dados['metodo_pagamento'] ?? null;
        $this->valor = $dados['valor'] ?? 0;
        $this->status_pagamento = $dados['status_pagamento'] ?? 'pendente';
        $this->data_pagamento = $dados['data_pagamento'] ?? null;
    }
}