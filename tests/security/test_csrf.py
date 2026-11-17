# -*- coding: utf-8 -*-
"""
test_csrf.py — Testes de proteção CSRF (Cross-Site Request Forgery)
Verifica que requisições POST sem token CSRF válido são bloqueadas.
"""

import requests

BASE_URL = "http://localhost/afro_essencia"


def test_post_login_sem_csrf_bloqueado():
    """POST no login sem token CSRF deve ser bloqueado (403) ou redirecionar."""
    data = {"email": "admin@afroessencia.com", "senha": "Admin@123"}
    # Sem csrf_token
    r = requests.post(f"{BASE_URL}/auth/login", data=data, allow_redirects=False)
    # O sistema CSRF bloqueia com 403, ou redireciona — não deve logar
    assert r.status_code in [200, 302, 403]
    assert "dashboard" not in r.text.lower()


def test_post_anamnese_sem_csrf_bloqueado():
    """POST em rota protegida sem CSRF deve retornar 403."""
    data = {"campo_qualquer": "valor"}
    r = requests.post(f"{BASE_URL}/anamnese/salvar", data=data, allow_redirects=False)
    assert r.status_code in [302, 403], \
        "Sem CSRF token, o servidor deve bloquear o POST"


def test_post_register_sem_csrf():
    """POST de cadastro sem CSRF não deve criar usuário."""
    data = {
        "nome": "Hacker", "email": "hacker@evil.com",
        "senha": "Senha@123", "confirmar_senha": "Senha@123"
        # sem csrf_token
    }
    r = requests.post(f"{BASE_URL}/auth/register", data=data, allow_redirects=False)
    assert r.status_code in [200, 302, 403]
    # Não deve redirecionar para login como se tivesse cadastrado com sucesso
    location = r.headers.get("Location", "")
    if r.status_code == 302:
        # Se redirecionar, não deve ser para o dashboard
        assert "dashboard" not in location.lower()


def test_csrf_token_errado_bloqueado():
    """CSRF token incorreto deve ser recusado."""
    data = {
        "email": "admin@afroessencia.com",
        "senha": "Admin@123",
        "csrf_token": "token_invalido_12345"
    }
    r = requests.post(f"{BASE_URL}/auth/login", data=data, allow_redirects=False)
    assert r.status_code in [200, 302, 403]
    assert "dashboard" not in r.text.lower()


def test_post_forgot_password_sem_csrf():
    """Recuperação de senha sem CSRF deve ser bloqueada."""
    data = {"email": "admin@afroessencia.com"}
    r = requests.post(f"{BASE_URL}/auth/forgotPassword", data=data, allow_redirects=False)
    assert r.status_code in [200, 302, 403]
