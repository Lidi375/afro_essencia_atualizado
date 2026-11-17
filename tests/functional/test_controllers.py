# -*- coding: utf-8 -*-
"""
test_controllers.py — Testes de Controllers (rotas protegidas e públicas)
"""

import pytest
import requests

BASE_URL = "http://localhost/afro_essencia"

# Rotas públicas — devem retornar 200
ROTAS_PUBLICAS = [
    "home/index",
    "profissional/index",
    "galeria/index",
    "servico/index",
]

# Rotas protegidas — sem auth devem redirecionar (302) para login
ROTAS_PROTEGIDAS = [
    "agendamento/index",
    "horario/index",
    "notificacao/index",
    "pagamento/index",
    "dashboard/index",
]


@pytest.mark.parametrize("rota", ROTAS_PUBLICAS)
def test_controllers_publicos(rota):
    """Controllers públicos respondem com 200."""
    r = requests.get(f"{BASE_URL}/{rota}", allow_redirects=True, timeout=5)
    assert r.status_code == 200, f"Falha na rota pública: {rota}"


@pytest.mark.parametrize("rota", ROTAS_PROTEGIDAS)
def test_controllers_protegidos_redirecionam(rota):
    """Controllers protegidos redirecionam para login quando não autenticado."""
    r = requests.get(f"{BASE_URL}/{rota}", allow_redirects=False, timeout=5)
    assert r.status_code in [302, 401, 403], (
        f"Rota protegida deveria redirecionar: {rota} → {r.status_code}"
    )


def test_home_controller_conteudo():
    """Home controller retorna conteúdo HTML válido."""
    r = requests.get(f"{BASE_URL}/home/index")
    assert r.status_code == 200
    assert "<html" in r.text.lower()


def test_profissional_controller_conteudo():
    """Profissional controller retorna lista de profissionais."""
    r = requests.get(f"{BASE_URL}/profissional/index")
    assert r.status_code == 200


def test_rota_inexistente_retorna_404():
    """Rota inexistente deve retornar 404."""
    r = requests.get(f"{BASE_URL}/rota_inexistente", allow_redirects=False)
    assert r.status_code == 404


def test_acesso_sem_metodo_post_invalido():
    """POST em rota protegida sem CSRF resulta em 403 ou redirect."""
    r = requests.post(f"{BASE_URL}/anamnese/salvar",
                      data={"campo": "valor"},
                      allow_redirects=False)
    assert r.status_code in [302, 403], "CSRF deve bloquear POST sem token"
