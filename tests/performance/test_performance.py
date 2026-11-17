# -*- coding: utf-8 -*-
"""
test_performance.py — Testes de performance e tempo de resposta
Verifica que as páginas respondem dentro de limites aceitáveis.
"""

import time
import pytest
import requests

BASE_URL = "http://localhost/afro_essencia"

# Limite de tempo em segundos por rota
LIMITE_RAPIDO  = 1.0   # páginas simples
LIMITE_NORMAL  = 2.0   # páginas com lógica

ROTAS_PERFORMANCE = [
    ("home",              LIMITE_RAPIDO),
    ("auth/login",        LIMITE_RAPIDO),
    ("auth/register",     LIMITE_RAPIDO),
    ("auth/forgotPassword", LIMITE_RAPIDO),
    ("profissional",      LIMITE_NORMAL),
    ("galeria",           LIMITE_NORMAL),
    ("servico",           LIMITE_NORMAL),
]


@pytest.mark.parametrize("rota,limite", ROTAS_PERFORMANCE)
def test_tempo_resposta_aceitavel(rota, limite):
    """Cada rota deve responder dentro do limite de tempo definido."""
    inicio = time.time()
    r = requests.get(f"{BASE_URL}/{rota}", allow_redirects=True, timeout=5)
    duracao = time.time() - inicio

    assert r.status_code in [200, 302, 403], f"Status inesperado em {rota}"
    assert duracao <= limite, (
        f"Rota '{rota}' demorou {duracao:.2f}s (limite: {limite}s)"
    )


def test_multiplas_requisicoes_sequenciais():
    """10 requisições às rotas públicas devem completar em menos de 5 segundos."""
    # Usa apenas rotas GET públicas registradas no mock
    rotas_ciclo = [
        "/auth/login", "/auth/register", "/auth/forgotPassword",
        "/home", "/profissional", "/galeria", "/servico",
        "/home/index", "/profissional/index", "/galeria/index",
    ]
    inicio = time.time()
    for rota in rotas_ciclo:
        r = requests.get(f"{BASE_URL}{rota}", allow_redirects=True, timeout=5)
        assert r.status_code in [200, 302, 403], f"Status inesperado em {rota}: {r.status_code}"
    total = time.time() - inicio
    assert total < 5.0, f"10 requisições levaram {total:.2f}s (limite: 5s)"


def test_page_login_tamanho_resposta():
    """Página de login não deve ter tamanho absurdo (possível loop ou dump)."""
    r = requests.get(f"{BASE_URL}/auth/login")
    tamanho = len(r.content)
    assert tamanho < 500_000, f"Resposta muito grande: {tamanho} bytes"
    assert tamanho > 50, "Resposta suspeitosamente pequena (página vazia?)"


def test_rota_protegida_redirect_rapido():
    """Redirect de rota protegida deve ser imediato (< 1s)."""
    inicio = time.time()
    r = requests.get(f"{BASE_URL}/dashboard", allow_redirects=False, timeout=5)
    duracao = time.time() - inicio
    assert r.status_code in [302, 401, 403]
    assert duracao < 1.0, f"Redirect demorou {duracao:.2f}s"


def test_post_login_tempo_resposta():
    """POST no login deve responder em menos de 2 segundos."""
    payload = {"email": "wrong@email.com", "senha": "wrongpass", "csrf_token": "testtoken"}
    inicio = time.time()
    r = requests.post(f"{BASE_URL}/auth/login", data=payload,
                      allow_redirects=True, timeout=5)
    duracao = time.time() - inicio
    assert r.status_code in [200, 302, 403]
    assert duracao < 2.0, f"POST login demorou {duracao:.2f}s"
