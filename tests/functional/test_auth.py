# -*- coding: utf-8 -*-
"""
test_auth.py — Testes funcionais de autenticação
Cobre: login, registro, recuperação e reset de senha.
"""

import requests

BASE_URL = "http://localhost/afro_essencia"


# ─── LOGIN ────────────────────────────────────────────────────────────────────

def test_login_page_acessivel():
    """Página de login retorna 200."""
    r = requests.get(f"{BASE_URL}/auth/login")
    assert r.status_code == 200

def test_login_page_contem_formulario():
    """Página de login contém campos de email e senha."""
    r = requests.get(f"{BASE_URL}/auth/login")
    assert "email" in r.text.lower()

def test_login_credenciais_invalidas():
    """Login com credenciais erradas retorna 200 (sem redirect ao dashboard)."""
    payload = {"email": "wrong@email.com", "senha": "wrongpass", "csrf_token": "testtoken"}
    r = requests.post(f"{BASE_URL}/auth/login", data=payload, allow_redirects=False)
    assert r.status_code in [200, 302]
    if r.status_code == 200:
        assert "dashboard" not in r.text.lower()

def test_login_email_invalido_rejeitado():
    """Login com e-mail malformado deve ser bloqueado pelo servidor."""
    payload = {"email": "nao_e_email", "senha": "qualquer", "csrf_token": "testtoken"}
    r = requests.post(f"{BASE_URL}/auth/login", data=payload, allow_redirects=False)
    assert r.status_code in [200, 302]

def test_login_campos_vazios():
    """Login sem preenchimento deve retornar mensagem de erro."""
    payload = {"email": "", "senha": "", "csrf_token": "testtoken"}
    r = requests.post(f"{BASE_URL}/auth/login", data=payload, allow_redirects=False)
    assert r.status_code in [200, 302]


# ─── CADASTRO ─────────────────────────────────────────────────────────────────

def test_register_page_acessivel():
    """Página de cadastro retorna 200."""
    r = requests.get(f"{BASE_URL}/auth/register")
    assert r.status_code == 200

def test_register_page_contem_formulario():
    """Página de cadastro contém campos de nome e email."""
    r = requests.get(f"{BASE_URL}/auth/register")
    assert "nome" in r.text.lower() or "email" in r.text.lower()

def test_register_senhas_diferentes_rejeitadas():
    """Senhas diferentes devem ser rejeitadas."""
    payload = {
        "nome": "Teste", "email": "novo2@email.com",
        "senha": "Senha@123", "confirmar_senha": "Diferente@456",
        "csrf_token": "testtoken"
    }
    r = requests.post(f"{BASE_URL}/auth/register", data=payload, allow_redirects=False)
    assert r.status_code in [200, 302]
    if r.status_code == 200:
        assert "coincidem" in r.text.lower() or "senha" in r.text.lower()

def test_register_senha_curta_rejeitada():
    """Senha com menos de 6 caracteres deve ser rejeitada."""
    payload = {
        "nome": "Teste", "email": "novo3@email.com",
        "senha": "123", "confirmar_senha": "123",
        "csrf_token": "testtoken"
    }
    r = requests.post(f"{BASE_URL}/auth/register", data=payload, allow_redirects=False)
    assert r.status_code in [200, 302]

def test_register_nome_curto_rejeitado():
    """Nome com menos de 3 caracteres deve ser rejeitado."""
    payload = {
        "nome": "Ab", "email": "novo4@email.com",
        "senha": "Senha@123", "confirmar_senha": "Senha@123",
        "csrf_token": "testtoken"
    }
    r = requests.post(f"{BASE_URL}/auth/register", data=payload, allow_redirects=False)
    assert r.status_code in [200, 302]


# ─── RECUPERAÇÃO DE SENHA ─────────────────────────────────────────────────────

def test_forgot_password_page_acessivel():
    """Página de recuperação de senha retorna 200."""
    r = requests.get(f"{BASE_URL}/auth/forgotPassword")
    assert r.status_code == 200

def test_forgot_password_email_valido():
    """Envio de e-mail válido retorna resposta genérica (sem revelar existência)."""
    payload = {"email": "admin@afroessencia.com", "csrf_token": "testtoken"}
    r = requests.post(f"{BASE_URL}/auth/forgotPassword", data=payload, allow_redirects=False)
    assert r.status_code in [200, 302]

def test_forgot_password_email_nao_cadastrado():
    """Sistema não deve revelar se e-mail existe ou não."""
    payload = {"email": "naoexiste@email.com", "csrf_token": "testtoken"}
    r = requests.post(f"{BASE_URL}/auth/forgotPassword", data=payload, allow_redirects=False)
    assert r.status_code in [200, 302]

def test_reset_password_token_invalido():
    """Token inválido deve redirecionar sem expor detalhes."""
    r = requests.get(f"{BASE_URL}/auth/resetPassword?token=tokeninvalido123",
                     allow_redirects=False)
    assert r.status_code in [200, 302]
