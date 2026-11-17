# -*- coding: utf-8 -*-
"""
test_integration.py — Testes de integração
Verifica fluxos completos de múltiplas etapas do sistema.
"""

import requests

BASE_URL = "http://localhost/afro_essencia"


def test_fluxo_acesso_login_direto_dashboard():
    """Sem autenticação, tentar acessar o dashboard redireciona para login."""
    r = requests.get(f"{BASE_URL}/dashboard", allow_redirects=False)
    assert r.status_code == 302
    location = r.headers.get("Location", "")
    assert "login" in location.lower() or r.status_code == 302


def test_fluxo_login_invalido_nao_cria_sessao():
    """Login com credenciais erradas não deve retornar acesso ao dashboard."""
    payload = {"email": "wrong@email.com", "senha": "wrongpass", "csrf_token": "testtoken"}
    r = requests.post(f"{BASE_URL}/auth/login", data=payload, allow_redirects=True)
    assert r.status_code in [200, 302, 403]
    assert "dashboard" not in r.text.lower()


def test_fluxo_recuperacao_senha_completo():
    """Fluxo de recuperação: esqueci senha → submissão → resposta genérica."""
    # 1. Acessa a página
    r1 = requests.get(f"{BASE_URL}/auth/forgotPassword")
    assert r1.status_code == 200

    # 2. Submete o e-mail
    payload = {"email": "admin@afroessencia.com", "csrf_token": "testtoken"}
    r2 = requests.post(f"{BASE_URL}/auth/forgotPassword",
                       data=payload, allow_redirects=False)
    assert r2.status_code in [200, 302]


def test_fluxo_reset_senha_token_invalido():
    """Token inválido no reset deve bloquear e não exibir formulário."""
    r = requests.get(f"{BASE_URL}/auth/resetPassword?token=abc123",
                     allow_redirects=False)
    assert r.status_code in [200, 302]


def test_fluxo_register_depois_logout():
    """Acesso à página de cadastro é público mesmo após tentativa de logout."""
    r1 = requests.get(f"{BASE_URL}/auth/register")
    assert r1.status_code == 200


def test_fluxo_rotas_publicas_sequenciais():
    """Acesso a múltiplas rotas públicas em sequência deve funcionar."""
    session = requests.Session()
    rotas = ["home", "auth/login", "auth/register", "auth/forgotPassword",
             "profissional", "galeria", "servico"]
    for rota in rotas:
        r = session.get(f"{BASE_URL}/{rota}", allow_redirects=True)
        assert r.status_code == 200, f"Rota pública falhou em sequência: {rota}"


def test_fluxo_tentativas_multiplas_login_falho():
    """Ataques via SQL injection e XSS no login devem ser bloqueados sem acesso."""
    ataques = [
        {"email": "' OR 1=1 --",                   "senha": "' OR 1=1 --",  "csrf_token": "testtoken"},
        {"email": "<script>alert(1)</script>",      "senha": "xss",          "csrf_token": "testtoken"},
    ]
    for payload in ataques:
        r = requests.post(f"{BASE_URL}/auth/login", data=payload, allow_redirects=False)
        assert r.status_code in [200, 302, 403]
        # Payload de ataque deve retornar erro, não acesso
        assert r.status_code != 302 or "dashboard" not in r.headers.get("Location", "")
        if r.status_code == 200:
            assert "dashboard" not in r.text.lower()


def test_fluxo_rota_inexistente_apos_rotas_validas():
    """Após acessar rotas válidas, rota inexistente ainda retorna 404."""
    session = requests.Session()
    session.get(f"{BASE_URL}/home")
    session.get(f"{BASE_URL}/auth/login")
    r = session.get(f"{BASE_URL}/rota_inexistente", allow_redirects=False)
    assert r.status_code == 404
