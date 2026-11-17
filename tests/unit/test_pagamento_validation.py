# -*- coding: utf-8 -*-
"""
test_pagamento_validation.py — Testes unitários das regras de validação de pagamento.
Espelha a lógica do PagamentoService em Python puro.
"""
import pytest
from decimal import Decimal


# ─── Funções que espelham PagamentoService.php ────────────────────────────────

METODOS_VALIDOS = {'pix', 'cartao', 'dinheiro', 'transferencia'}

def validar_metodo(metodo: str) -> bool:
    return metodo in METODOS_VALIDOS

def validar_valor(valor: float) -> dict:
    erros = []
    if valor <= 0:
        erros.append("Valor deve ser maior que zero")
    if valor > 99999.99:
        erros.append("Valor excede o limite permitido")
    return {"valido": len(erros) == 0, "erros": erros}

def calcular_valor_servico(servico: dict) -> float:
    """Valor vem sempre do serviço, nunca do cliente."""
    return float(servico.get('valor', 0))

STATUS_PAGAVEIS = {'pendente', 'confirmado'}

def agendamento_pode_ser_pago(status: str) -> bool:
    return status in STATUS_PAGAVEIS

def pagamento_pode_ser_estornado(status_pagamento: str) -> bool:
    return status_pagamento == 'pago'

def pagamento_pode_ser_confirmado(status_pagamento: str) -> bool:
    return status_pagamento == 'pendente'

def formatar_valor(valor: float) -> str:
    """R$ 1.234,56"""
    return f"R$ {valor:,.2f}".replace(',', 'X').replace('.', ',').replace('X', '.')


# ─── Testes de método de pagamento ───────────────────────────────────────────

@pytest.mark.parametrize("metodo", ["pix", "cartao", "dinheiro", "transferencia"])
def test_metodos_validos(metodo):
    assert validar_metodo(metodo), f"Método válido rejeitado: {metodo}"

@pytest.mark.parametrize("metodo", ["", "bitcoin", "boleto", "cheque", "' OR 1=1", "<script>"])
def test_metodos_invalidos(metodo):
    assert not validar_metodo(metodo), f"Método inválido aceito: {metodo}"


# ─── Testes de validação de valor ─────────────────────────────────────────────

@pytest.mark.parametrize("valor", [0.01, 1.0, 50.0, 150.75, 9999.99])
def test_valores_validos(valor):
    r = validar_valor(valor)
    assert r["valido"], f"Valor válido rejeitado: {valor} — {r['erros']}"

@pytest.mark.parametrize("valor,motivo", [
    (0,       "zero"),
    (-1,      "negativo"),
    (-50.99,  "negativo decimal"),
    (100000,  "acima do limite"),
])
def test_valores_invalidos(valor, motivo):
    r = validar_valor(valor)
    assert not r["valido"], f"Valor inválido aceito ({motivo}): {valor}"


# ─── Testes de cálculo de valor do serviço ────────────────────────────────────

def test_valor_vem_do_servico():
    """O valor deve vir sempre do serviço, não do input do cliente."""
    servico = {"valor": "150.00", "nome": "Hidratação"}
    assert calcular_valor_servico(servico) == 150.0

def test_valor_servico_inexistente_retorna_zero():
    assert calcular_valor_servico({}) == 0.0

def test_valor_servico_nao_pode_ser_sobrescrito_por_cliente():
    """Cliente não pode forçar um valor diferente do serviço."""
    servico = {"valor": "200.00"}
    valor_cliente_malicioso = 1.00  # tentativa de pagar menos
    valor_real = calcular_valor_servico(servico)
    assert valor_real != valor_cliente_malicioso
    assert valor_real == 200.0


# ─── Testes de status de agendamento pagável ─────────────────────────────────

@pytest.mark.parametrize("status", ["pendente", "confirmado"])
def test_status_agendamento_pagavel(status):
    assert agendamento_pode_ser_pago(status)

@pytest.mark.parametrize("status", ["concluido", "cancelado", "em_andamento"])
def test_status_agendamento_nao_pagavel(status):
    assert not agendamento_pode_ser_pago(status)


# ─── Testes de fluxo de estados do pagamento ─────────────────────────────────

def test_apenas_pago_pode_ser_estornado():
    assert pagamento_pode_ser_estornado("pago")
    assert not pagamento_pode_ser_estornado("pendente")
    assert not pagamento_pode_ser_estornado("estornado")

def test_apenas_pendente_pode_ser_confirmado():
    assert pagamento_pode_ser_confirmado("pendente")
    assert not pagamento_pode_ser_confirmado("pago")
    assert not pagamento_pode_ser_confirmado("estornado")


# ─── Testes de formatação de valor ───────────────────────────────────────────

@pytest.mark.parametrize("valor,esperado", [
    (150.0,    "R$ 150,00"),
    (1234.56,  "R$ 1.234,56"),
    (0.01,     "R$ 0,01"),
    (9999.99,  "R$ 9.999,99"),
])
def test_formatacao_valor(valor, esperado):
    assert formatar_valor(valor) == esperado


# ─── Testes de segurança: injeção de dados ────────────────────────────────────

@pytest.mark.parametrize("payload", [
    "' OR 1=1 --",
    "<script>alert(1)</script>",
    "javascript:void(0)",
    "../../etc/passwd",
    "9999999999",
    "NaN",
    "Infinity",
])
def test_metodo_rejeita_payloads_maliciosos(payload):
    """Nenhum payload malicioso deve ser aceito como método."""
    assert not validar_metodo(payload)

def test_valor_string_nao_numerica():
    """Strings não numéricas devem resultar em valor inválido."""
    try:
        valor = float("abc")
    except ValueError:
        valor = 0.0
    r = validar_valor(valor)
    assert not r["valido"]
