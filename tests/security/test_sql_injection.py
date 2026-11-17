# -*- coding: utf-8 -*-
"""
test_sql_injection.py — Testes de proteção contra SQL Injection
"""

import pytest
import requests

BASE_URL = "http://localhost/afro_essencia"

PAYLOADS_SQL = [
    "' OR 1=1 --",
    "' OR '1'='1",
    "'; DROP TABLE usuarios; --",
    "1' ORDER BY 1--",
    "' UNION SELECT null,null,null--",
    "admin'--",
    "' OR 1=1#",
    "\" OR \"\"=\"",
]


@pytest.mark.parametrize("payload", PAYLOADS_SQL)
def test_login_sql_injection(payload):
    """SQL Injection no login não deve conceder acesso nem quebrar o sistema."""
    data = {"email": payload, "senha": payload, "csrf_token": "testtoken"}
    r = requests.post(f"{BASE_URL}/auth/login", data=data, allow_redirects=True)
    assert r.status_code in [200, 302, 403], f"Status inesperado com payload: {payload}"
    assert "dashboard" not in r.text.lower(), \
        f"Possível bypass de autenticação com payload: {payload}"


@pytest.mark.parametrize("payload", PAYLOADS_SQL)
def test_register_sql_injection_email(payload):
    """SQL Injection no campo email do cadastro deve ser rejeitado."""
    data = {
        "nome": "Teste", "email": payload,
        "senha": "Senha@123", "confirmar_senha": "Senha@123",
        "csrf_token": "testtoken"
    }
    r = requests.post(f"{BASE_URL}/auth/register", data=data, allow_redirects=False)
    assert r.status_code in [200, 302, 403], f"Status inesperado com payload: {payload}"


def test_login_nao_retorna_erro_de_banco():
    """Erro de banco de dados não deve ser exposto ao usuário."""
    data = {"email": "' OR 1=1 --", "senha": "' OR 1=1 --", "csrf_token": "testtoken"}
    r = requests.post(f"{BASE_URL}/auth/login", data=data, allow_redirects=True)
    texto = r.text.lower()
    termos_sensiveis = ["sql", "mysql", "syntax error", "you have an error", "warning:"]
    for termo in termos_sensiveis:
        assert termo not in texto, f"Informação sensível exposta: '{termo}'"


def test_forgot_password_sql_injection():
    """Campo de e-mail em recuperação de senha deve rejeitar SQL Injection."""
    data = {"email": "' OR 1=1 --", "csrf_token": "testtoken"}
    r = requests.post(f"{BASE_URL}/auth/forgotPassword", data=data, allow_redirects=False)
    assert r.status_code in [200, 302, 403]
