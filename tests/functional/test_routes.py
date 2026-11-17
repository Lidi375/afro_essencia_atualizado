# -*- coding: utf-8 -*-
"""
test_routes.py — Testes de acessibilidade de rotas principais
"""

import pytest
import requests

BASE_URL = "http://localhost/afro_essencia"

ROTAS_ABERTAS = [
    "home",
    "auth/login",
    "auth/register",
    "auth/forgotPassword",
    "profissional",
    "galeria",
    "servico",
]

ROTAS_RESTRITAS = [
    "agendamento",
    "dashboard",
    "notificacao",
    "pagamento",
    "horario",
    "avaliacao/minhas",
    "anamnese/form",
]


@pytest.mark.parametrize("rota", ROTAS_ABERTAS)
def test_rotas_abertas_acessiveis(rota):
    """Rotas públicas devem retornar 200."""
    r = requests.get(f"{BASE_URL}/{rota}", allow_redirects=True)
    assert r.status_code == 200, f"Rota pública inacessível: {rota}"


@pytest.mark.parametrize("rota", ROTAS_RESTRITAS)
def test_rotas_restritas_sem_auth(rota):
    """Rotas restritas devem bloquear acesso não autenticado (302/401/403)."""
    r = requests.get(f"{BASE_URL}/{rota}", allow_redirects=False)
    assert r.status_code in [302, 401, 403], (
        f"Rota restrita deveria redirecionar: {rota} → {r.status_code}"
    )


def test_rota_login_contem_form():
    """Página de login deve ter campo de e-mail."""
    r = requests.get(f"{BASE_URL}/auth/login")
    assert r.status_code == 200
    assert "email" in r.text.lower()


def test_rota_register_contem_form():
    """Página de cadastro deve ter campo de nome."""
    r = requests.get(f"{BASE_URL}/auth/register")
    assert r.status_code == 200
    assert "nome" in r.text.lower() or "email" in r.text.lower()


def test_rota_home_contem_html():
    """Página inicial deve retornar HTML válido."""
    r = requests.get(f"{BASE_URL}/home")
    assert r.status_code == 200
    assert "<html" in r.text.lower()


def test_rota_protegida_redireciona_para_login():
    """Rota protegida deve redirecionar para /auth/login."""
    r = requests.get(f"{BASE_URL}/dashboard", allow_redirects=False)
    assert r.status_code == 302
    location = r.headers.get("Location", "")
    assert "login" in location.lower() or location == ""
