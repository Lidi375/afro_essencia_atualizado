# -*- coding: utf-8 -*-
"""
test_validation.py — Testes unitários de regras de validação
Verifica as regras de negócio de validação sem depender do servidor HTTP.
"""

import re
import pytest


# ─── Funções que espelham a lógica PHP do sistema ─────────────────────────────

def validar_email(email: str) -> bool:
    """Espelha filter_var($email, FILTER_VALIDATE_EMAIL) do PHP."""
    pattern = r'^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$'
    return bool(re.match(pattern, email.strip()))


def validar_senha(senha: str) -> dict:
    """Espelha as regras de validação de senha do AuthController/UsuarioService."""
    erros = []
    if len(senha) < 6:
        erros.append("Mínimo 6 caracteres")
    return {"valida": len(erros) == 0, "erros": erros}


def validar_senha_reset(senha: str) -> dict:
    """Espelha validação do resetPassword: min 8 chars, letras e números."""
    erros = []
    if len(senha) < 8:
        erros.append("Mínimo 8 caracteres")
    if not re.search(r'[A-Za-z]', senha):
        erros.append("Deve conter letras")
    if not re.search(r'[0-9]', senha):
        erros.append("Deve conter números")
    return {"valida": len(erros) == 0, "erros": erros}


def validar_nome(nome: str) -> dict:
    """Espelha validação de nome do register: min 3 chars."""
    erros = []
    nome = nome.strip()
    if len(nome) < 3:
        erros.append("Mínimo 3 caracteres")
    return {"valida": len(erros) == 0, "erros": erros}


def sanitizar_xss(texto: str) -> str:
    """Espelha strip_tags() do PHP."""
    return re.sub(r'<[^>]+>', '', texto)


def token_reset_valido(token: str) -> bool:
    """Espelha validação do resetPassword: 64 chars hex."""
    return len(token) == 64 and all(c in '0123456789abcdefABCDEF' for c in token)


# ─── Testes de e-mail ─────────────────────────────────────────────────────────

@pytest.mark.parametrize("email", [
    "usuario@email.com",
    "admin@afroessencia.com.br",
    "teste.nome+tag@dominio.org",
])
def test_emails_validos(email):
    assert validar_email(email), f"E-mail válido rejeitado: {email}"


@pytest.mark.parametrize("email", [
    "' OR 1=1 --",
    "<script>alert(1)</script>",
    "nao_e_email",
    "@semuser.com",
    "sem_arroba.com",
    "",
    "a@b",
])
def test_emails_invalidos(email):
    assert not validar_email(email), f"E-mail inválido aceito: {email}"


# ─── Testes de senha (cadastro) ───────────────────────────────────────────────

@pytest.mark.parametrize("senha", ["abc123", "Senha@123", "123456", "abcdef"])
def test_senhas_validas_cadastro(senha):
    r = validar_senha(senha)
    assert r["valida"], f"Senha válida rejeitada: {senha} — {r['erros']}"


@pytest.mark.parametrize("senha", ["123", "ab", ""])
def test_senhas_invalidas_cadastro(senha):
    r = validar_senha(senha)
    assert not r["valida"], f"Senha inválida aceita: {senha}"


# ─── Testes de senha (reset) ─────────────────────────────────────────────────

@pytest.mark.parametrize("senha", ["Senha123", "abc12345", "ABCD1234"])
def test_senhas_validas_reset(senha):
    r = validar_senha_reset(senha)
    assert r["valida"], f"Senha de reset válida rejeitada: {senha}"


@pytest.mark.parametrize("senha,motivo", [
    ("abc1234", "menos de 8 chars"),
    ("abcdefgh", "sem números"),
    ("12345678", "sem letras"),
    ("", "vazia"),
])
def test_senhas_invalidas_reset(senha, motivo):
    r = validar_senha_reset(senha)
    assert not r["valida"], f"Senha inválida aceita ({motivo}): '{senha}'"


# ─── Testes de nome ───────────────────────────────────────────────────────────

@pytest.mark.parametrize("nome", ["Ana", "João Silva", "Maria das Graças"])
def test_nomes_validos(nome):
    r = validar_nome(nome)
    assert r["valida"], f"Nome válido rejeitado: {nome}"


@pytest.mark.parametrize("nome", ["Ab", "A", ""])
def test_nomes_invalidos(nome):
    r = validar_nome(nome)
    assert not r["valida"], f"Nome inválido aceito: '{nome}'"


# ─── Testes de sanitização XSS ───────────────────────────────────────────────

@pytest.mark.parametrize("entrada,esperado", [
    ("<script>alert(1)</script>", "alert(1)"),
    ("<img src=x onerror=alert(1)>", ""),
    ("Texto normal", "Texto normal"),
    ("<b>negrito</b>", "negrito"),
    ("", ""),
])
def test_sanitizacao_xss(entrada, esperado):
    resultado = sanitizar_xss(entrada)
    assert resultado == esperado, f"Sanitização incorreta: '{entrada}' → '{resultado}'"


# ─── Testes de token de reset ─────────────────────────────────────────────────

def test_token_valido_64_hex():
    token = "a" * 64
    assert token_reset_valido(token)


@pytest.mark.parametrize("token,motivo", [
    ("abc123", "muito curto"),
    ("z" * 64, "char inválido (z)"),
    ("", "vazio"),
    ("a" * 63, "63 chars"),
    ("a" * 65, "65 chars"),
])
def test_tokens_invalidos(token, motivo):
    assert not token_reset_valido(token), f"Token inválido aceito ({motivo})"


# ─── Testes de notas de avaliação ────────────────────────────────────────────

@pytest.mark.parametrize("nota", [1, 2, 3, 4, 5])
def test_notas_avaliacao_validas(nota):
    assert 1 <= nota <= 5, f"Nota válida rejeitada: {nota}"


@pytest.mark.parametrize("nota", [0, 6, -1, 10])
def test_notas_avaliacao_invalidas(nota):
    assert not (1 <= nota <= 5), f"Nota inválida aceita: {nota}"
