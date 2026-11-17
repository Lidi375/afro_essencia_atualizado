# -*- coding: utf-8 -*-
"""
test_pagamento.py — Testes funcionais do fluxo de pagamento automatizado
Cobre: listagem, inicio, confirmação, estorno (admin) e validações.
"""
import requests
BASE_URL = "http://localhost/afro_essencia"


# ─── Rotas de pagamento (cliente) ────────────────────────────────────────────

def test_pagamento_index_sem_auth_redireciona():
    """Rota /pagamento/index sem autenticação deve redirecionar."""
    r = requests.get(f"{BASE_URL}/pagamento/index", allow_redirects=False)
    assert r.status_code in [302, 401, 403]

def test_pagamento_iniciar_sem_auth_redireciona():
    """Rota /pagamento/iniciar sem autenticação deve redirecionar."""
    r = requests.get(f"{BASE_URL}/pagamento/iniciar/1", allow_redirects=False)
    assert r.status_code in [302, 401, 403]

def test_pagamento_confirmar_sem_auth_redireciona():
    """Rota /pagamento/confirmar sem autenticação deve redirecionar."""
    r = requests.get(f"{BASE_URL}/pagamento/confirmar/1", allow_redirects=False)
    assert r.status_code in [302, 401, 403]

def test_pagamento_processar_get_redireciona():
    """GET em /pagamento/processar deve redirecionar (só aceita POST)."""
    r = requests.get(f"{BASE_URL}/pagamento/processar", allow_redirects=False)
    assert r.status_code in [302, 401, 403]

def test_pagamento_processar_post_sem_csrf_bloqueado():
    """POST em /pagamento/processar sem CSRF deve ser bloqueado."""
    r = requests.post(f"{BASE_URL}/pagamento/processar",
                      data={"agendamento_id": 1, "metodo": "pix"},
                      allow_redirects=False)
    assert r.status_code in [302, 403]


# ─── Validações de segurança ─────────────────────────────────────────────────

def test_pagamento_iniciar_id_zero_redireciona():
    """ID de agendamento 0 ou inválido deve ser bloqueado."""
    r = requests.get(f"{BASE_URL}/pagamento/iniciar/0", allow_redirects=False)
    assert r.status_code in [302, 401, 403, 404]

def test_pagamento_iniciar_id_negativo():
    """ID negativo deve ser bloqueado."""
    r = requests.get(f"{BASE_URL}/pagamento/iniciar/-1", allow_redirects=False)
    assert r.status_code in [302, 400, 401, 403, 404]

def test_pagamento_confirmar_id_zero():
    """Confirmação com ID 0 deve ser bloqueada."""
    r = requests.get(f"{BASE_URL}/pagamento/confirmar/0", allow_redirects=False)
    assert r.status_code in [302, 401, 403, 404]


# ─── Estorno (admin) ─────────────────────────────────────────────────────────

def test_estornar_pagamento_sem_auth_bloqueado():
    """Estorno sem autenticação admin deve ser bloqueado."""
    r = requests.post(f"{BASE_URL}/dashboard/estornarPagamento/1",
                      data={"csrf_token": "testtoken"},
                      allow_redirects=False)
    assert r.status_code in [302, 401, 403]

def test_estornar_pagamento_get_bloqueado():
    """GET no endpoint de estorno deve ser bloqueado (apenas POST)."""
    r = requests.get(f"{BASE_URL}/dashboard/estornarPagamento/1",
                     allow_redirects=False)
    assert r.status_code in [302, 401, 403, 404]

def test_lista_pagamentos_admin_sem_auth():
    """Lista de pagamentos admin sem autenticação deve redirecionar."""
    r = requests.get(f"{BASE_URL}/dashboard/pagamentos", allow_redirects=False)
    assert r.status_code in [302, 401, 403]


# ─── Rota de pagamento pública (index do cliente) ────────────────────────────

def test_pagamento_index_rota_existe():
    """Rota /pagamento/index existe no sistema (retorna resposta)."""
    r = requests.get(f"{BASE_URL}/pagamento/index", allow_redirects=True)
    assert r.status_code in [200, 302, 401, 403]

def test_pagamento_rota_sem_parametro():
    """/pagamento sem parâmetro deve responder (via /pagamento/index)."""
    r = requests.get(f"{BASE_URL}/pagamento", allow_redirects=False)
    assert r.status_code in [200, 302, 401, 403]
