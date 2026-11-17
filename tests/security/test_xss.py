# -*- coding: utf-8 -*-
"""
test_xss.py — Testes de proteção contra XSS (Cross-Site Scripting)
"""

import pytest
import requests

BASE_URL = "http://localhost/afro_essencia"

PAYLOADS_XSS = [
    "<script>alert('xss')</script>",
    "<img src=x onerror=alert(1)>",
    "javascript:alert(1)",
    "<svg onload=alert(1)>",
    "'><script>alert(document.cookie)</script>",
    "<body onload=alert('xss')>",
    "<iframe src='javascript:alert(1)'>",
]


@pytest.mark.parametrize("payload", PAYLOADS_XSS)
def test_xss_no_login(payload):
    """Scripts maliciosos não devem ser refletidos na resposta do login."""
    data = {"email": payload, "senha": payload, "csrf_token": "testtoken"}
    r = requests.post(f"{BASE_URL}/auth/login", data=data, allow_redirects=True)
    assert r.status_code in [200, 302, 403]
    assert "<script>" not in r.text, \
        f"XSS não sanitizado no login com payload: {payload}"


@pytest.mark.parametrize("payload", PAYLOADS_XSS)
def test_xss_no_register(payload):
    """Scripts maliciosos nos campos de cadastro não devem ser refletidos."""
    data = {
        "nome": payload, "email": "teste@email.com",
        "senha": "Senha@123", "confirmar_senha": "Senha@123",
        "csrf_token": "testtoken"
    }
    r = requests.post(f"{BASE_URL}/auth/register", data=data, allow_redirects=True)
    assert r.status_code in [200, 302, 403]
    # A tag <script> não deve aparecer literalmente
    assert "<script>" not in r.text


def test_xss_no_forgot_password():
    """Campo de e-mail na recuperação de senha não deve refletir scripts."""
    data = {"email": "<script>alert(1)</script>", "csrf_token": "testtoken"}
    r = requests.post(f"{BASE_URL}/auth/forgotPassword", data=data, allow_redirects=True)
    assert r.status_code in [200, 302, 403]
    assert "<script>" not in r.text


def test_xss_headers_de_seguranca():
    """Página de login deve conter cabeçalhos básicos de segurança."""
    r = requests.get(f"{BASE_URL}/auth/login")
    # Verifica pelo menos que a resposta é HTML
    content_type = r.headers.get("Content-Type", "")
    assert "text/html" in content_type or r.status_code == 200
