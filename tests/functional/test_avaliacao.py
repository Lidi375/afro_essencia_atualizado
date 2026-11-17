# -*- coding: utf-8 -*-
"""
test_avaliacao.py — Testes do fluxo de avaliação (estrelinhas).

Bugs corrigidos:
  1. Link usava /avaliacao/create/{id} (segmento de rota) — controller
     lia $_GET['agendamento_id'] que nunca chegava. Corrigido para query string.
  2. BaseController::redirect() sanitizava '?' e '=' destruindo query strings.
     Corrigido para separar caminho da query string antes de sanitizar.
"""

import requests

BASE_URL = "http://localhost/afro_essencia"


# ─── Rota GET create (formulário de avaliação) ────────────────────────────────

def test_avaliacao_create_sem_auth_redireciona():
    """Sem autenticação, /avaliacao/create redireciona para login."""
    r = requests.get(f"{BASE_URL}/avaliacao/create", allow_redirects=False)
    assert r.status_code in [302, 401, 403]

def test_avaliacao_create_query_string_sem_auth():
    """URL com query string ?agendamento_id=1 sem auth redireciona."""
    r = requests.get(f"{BASE_URL}/avaliacao/create?agendamento_id=1",
                     allow_redirects=False)
    assert r.status_code in [302, 401, 403]

def test_avaliacao_create_segmento_sem_auth():
    """URL com ID como segmento /create/1 sem auth redireciona (não causa 500)."""
    r = requests.get(f"{BASE_URL}/avaliacao/create/1", allow_redirects=False)
    assert r.status_code in [302, 401, 403, 404]


# ─── Rota POST store ──────────────────────────────────────────────────────────

def test_avaliacao_store_sem_auth_redireciona():
    """POST /avaliacao/store sem autenticação redireciona."""
    r = requests.post(f"{BASE_URL}/avaliacao/store",
                      data={"agendamento_id": 1, "nota": 5, "csrf_token": "x"},
                      allow_redirects=False)
    assert r.status_code in [302, 401, 403]

def test_avaliacao_store_get_redireciona():
    """GET em /avaliacao/store deve redirecionar (só aceita POST)."""
    r = requests.get(f"{BASE_URL}/avaliacao/store", allow_redirects=False)
    assert r.status_code in [302, 401, 403, 404]

def test_avaliacao_store_sem_csrf_bloqueado():
    """POST sem token CSRF deve ser bloqueado (403)."""
    r = requests.post(f"{BASE_URL}/avaliacao/store",
                      data={"agendamento_id": 1, "nota": 5},
                      allow_redirects=False)
    assert r.status_code in [302, 403]


# ─── Rota minhas avaliações ───────────────────────────────────────────────────

def test_avaliacao_minhas_sem_auth_redireciona():
    """GET /avaliacao/minhas sem auth redireciona."""
    r = requests.get(f"{BASE_URL}/avaliacao/minhas", allow_redirects=False)
    assert r.status_code in [302, 401, 403]


# ─── Validação de parâmetros ──────────────────────────────────────────────────

def test_avaliacao_create_sem_agendamento_id():
    """/avaliacao/create sem agendamento_id redireciona (não causa erro 500)."""
    r = requests.get(f"{BASE_URL}/avaliacao/create", allow_redirects=False)
    assert r.status_code not in [500], "Não deve retornar erro 500"
    assert r.status_code in [302, 401, 403]

def test_avaliacao_create_agendamento_id_zero():
    """/avaliacao/create?agendamento_id=0 deve redirecionar sem erro 500."""
    r = requests.get(f"{BASE_URL}/avaliacao/create?agendamento_id=0",
                     allow_redirects=False)
    assert r.status_code not in [500]
    assert r.status_code in [302, 401, 403]
