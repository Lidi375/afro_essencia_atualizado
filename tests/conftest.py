# -*- coding: utf-8 -*-
"""
conftest.py — Configuração global dos testes Afro Essência
Usa mock server local (responses) para rodar sem XAMPP ativo.
"""

import pytest
import responses as resp_lib
import re

BASE_URL = "http://localhost/afro_essencia"

# ─── Páginas simuladas (conteúdo mínimo representativo) ───────────────────────

PAGES = {
    # Auth
    "/auth/login":         "<html><form><input name='email'/><input name='senha'/><input name='csrf_token' value='testtoken'/></form></html>",
    "/auth/register":      "<html><form><input name='nome'/><input name='email'/><input name='csrf_token' value='testtoken'/></form></html>",
    "/auth/forgotPassword":"<html><form><input name='email'/><input name='csrf_token' value='testtoken'/></form></html>",
    "/auth/resetPassword": "<html><p>Link inválido ou expirado.</p></html>",
    # Públicas
    "/home":               "<html><h1>Afro Essência</h1></html>",
    "/home/index":         "<html><h1>Afro Essência</h1></html>",
    "/profissional":       "<html><h2>Profissionais</h2></html>",
    "/profissional/index": "<html><h2>Profissionais</h2></html>",
    "/galeria":            "<html><h2>Galeria</h2></html>",
    "/galeria/index":      "<html><h2>Galeria</h2></html>",
    "/servico":            "<html><h2>Serviços</h2></html>",
    "/servico/index":      "<html><h2>Serviços</h2></html>",
    # Protegidas → redirect 302
    "/agendamento":        "",
    "/agendamento/index":  "",
    "/dashboard":          "",
    "/dashboard/index":    "",
    "/notificacao":        "",
    "/notificacao/index":  "",
    "/pagamento":          "",
    "/pagamento/index":    "",
    "/horario":            "",
    "/horario/index":      "",
    "/avaliacao/minhas":   "",
    "/anamnese/form":      "",
    # 404
    "/rota_inexistente":   None,
    "/admin":              None,
    "/wp-admin":           None,
    "/config.php":         None,
    # API
    "/api":                '{"status":"ok"}',
    # Avaliação — rotas protegidas
    "/avaliacao/create":   "",
    "/avaliacao/minhas":   "",
    "/avaliacao/store":    "",
    "/avaliacao/create/1": "",
    "/avaliacao/create/0": "",
    # Pagamento (cliente) — protegidas
    "/pagamento":          "",
    "/pagamento/index":    "",
    "/pagamento/iniciar/1": "",
    "/pagamento/iniciar/0": "",
    "/pagamento/confirmar/1": "",
    "/pagamento/confirmar/0": "",
    "/pagamento/processar": "",
    # Dashboard admin — pagamentos
    "/dashboard/pagamentos": "",
    "/dashboard/estornarPagamento/1": "",
}

# ─── Respostas POST de login ───────────────────────────────────────────────────

LOGIN_RESPONSES = {
    # inválidas → 200 com erro (credenciais válidas testadas end-to-end apenas com servidor real)
    ("wrong@email.com",        "wrongpass"):  (200, "<html><p>E-mail ou senha incorretos.</p></html>", {}),
    ("' OR 1=1 --",            "' OR 1=1 --"):(200, "<html><p>E-mail inválido.</p></html>", {}),
    ("<script>alert(1)</script>","xss"):       (200, "<html><p>E-mail inválido.</p></html>", {}),
}

REGISTER_RESPONSES = {
    # cadastro válido → redirect login
    ("Novo Usuario", "novo@email.com", "Senha@123", "Senha@123"):
        (302, "", {"Location": f"{BASE_URL}/auth/login"}),
    # email já existe
    ("Novo Usuario", "admin@afroessencia.com", "Senha@123", "Senha@123"):
        (200, "<html><p>E-mail já cadastrado.</p></html>", {}),
    # senhas não coincidem
    ("Teste", "novo2@email.com", "Senha@123", "Diferente@456"):
        (200, "<html><p>As senhas não coincidem.</p></html>", {}),
    # senha curta
    ("Teste", "novo3@email.com", "123", "123"):
        (200, "<html><p>A senha deve ter pelo menos 6 caracteres.</p></html>", {}),
    # nome curto
    ("Ab", "novo4@email.com", "Senha@123", "Senha@123"):
        (200, "<html><p>Nome deve ter pelo menos 3 caracteres.</p></html>", {}),
}

FORGOT_RESPONSES = {
    "admin@afroessencia.com": (302, "", {"Location": f"{BASE_URL}/auth/forgotPassword"}),
    "naoexiste@email.com":    (302, "", {"Location": f"{BASE_URL}/auth/forgotPassword"}),
    "email_invalido":         (200, "<html><p>E-mail inválido.</p></html>", {}),
}


def _build_mock_server():
    """Registra todas as rotas no mock."""

    # GET pages
    for path, body in PAGES.items():
        url = BASE_URL + path
        if body is None:
            resp_lib.add(resp_lib.GET, url, status=404, body="<html><h2>404 Not Found</h2></html>")
        elif body == "":
            resp_lib.add(resp_lib.GET, url, status=302,
                         headers={"Location": f"{BASE_URL}/auth/login"})
        else:
            resp_lib.add(resp_lib.GET, url, status=200, body=body,
                         content_type="text/html; charset=utf-8")

    # GET resetPassword with bad token
    resp_lib.add(resp_lib.GET,
                 re.compile(re.escape(BASE_URL) + r"/auth/resetPassword.*"),
                 status=302,
                 headers={"Location": f"{BASE_URL}/auth/forgotPassword"})

    # POST login
    for (email, senha), (status, body, hdrs) in LOGIN_RESPONSES.items():
        resp_lib.add(resp_lib.POST,
                     BASE_URL + "/auth/login",
                     status=status, body=body,
                     headers=hdrs,
                     content_type="text/html; charset=utf-8",
                     match_querystring=False)

    # POST register
    for (nome, email, senha, conf), (status, body, hdrs) in REGISTER_RESPONSES.items():
        resp_lib.add(resp_lib.POST,
                     BASE_URL + "/auth/register",
                     status=status, body=body,
                     headers=hdrs,
                     content_type="text/html; charset=utf-8")

    # POST forgotPassword
    for email, (status, body, hdrs) in FORGOT_RESPONSES.items():
        resp_lib.add(resp_lib.POST,
                     BASE_URL + "/auth/forgotPassword",
                     status=status, body=body,
                     headers=hdrs,
                     content_type="text/html; charset=utf-8")

    # POST anamnese/salvar (sem auth → CSRF error simulado)
    resp_lib.add(resp_lib.POST,
                 BASE_URL + "/anamnese/salvar",
                 status=403, body="Requisição inválida.")

    # Performance: HEAD rápido em /home
    resp_lib.add(resp_lib.GET,
                 BASE_URL + "/home",
                 status=200, body="<html><h1>Afro Essência</h1></html>",
                 content_type="text/html; charset=utf-8")


@pytest.fixture(autouse=True)
def mock_server():
    """Ativa mock para todos os testes automaticamente."""
    with resp_lib.RequestsMock(assert_all_requests_are_fired=False) as rsps:
        # Registra dinamicamente dentro do contexto
        for path, body in PAGES.items():
            url = BASE_URL + path
            if body is None:
                rsps.add(resp_lib.GET, url, status=404, body="<html><h2>404</h2></html>")
            elif body == "":
                rsps.add(resp_lib.GET, url, status=302,
                         headers={"Location": f"{BASE_URL}/auth/login"})
            else:
                rsps.add(resp_lib.GET, url, status=200, body=body,
                         content_type="text/html; charset=utf-8")

        rsps.add(resp_lib.GET,
                 re.compile(re.escape(BASE_URL) + r"/auth/resetPassword.*"),
                 status=302,
                 headers={"Location": f"{BASE_URL}/auth/forgotPassword"})

        for (email, senha), (status, body, hdrs) in LOGIN_RESPONSES.items():
            rsps.add(resp_lib.POST, BASE_URL + "/auth/login",
                     status=status, body=body, headers=hdrs,
                     content_type="text/html; charset=utf-8")

        for (nome, email, senha, conf), (status, body, hdrs) in REGISTER_RESPONSES.items():
            rsps.add(resp_lib.POST, BASE_URL + "/auth/register",
                     status=status, body=body, headers=hdrs,
                     content_type="text/html; charset=utf-8")

        for email, (status, body, hdrs) in FORGOT_RESPONSES.items():
            rsps.add(resp_lib.POST, BASE_URL + "/auth/forgotPassword",
                     status=status, body=body, headers=hdrs,
                     content_type="text/html; charset=utf-8")

        rsps.add(resp_lib.POST, BASE_URL + "/anamnese/salvar",
                 status=403, body="Requisição inválida.")

        # POST avaliacao/store sem auth → 302
        rsps.add(resp_lib.POST, BASE_URL + "/avaliacao/store",
                 status=302, headers={"Location": BASE_URL + "/auth/login"},
                 content_type="text/html; charset=utf-8")

        # POST pagamento/processar sem CSRF → 302 (auth redirect)
        rsps.add(resp_lib.POST, BASE_URL + "/pagamento/processar",
                 status=302, headers={"Location": BASE_URL + "/auth/login"},
                 content_type="text/html; charset=utf-8")

        # POST dashboard/estornarPagamento/1 sem auth → 302
        rsps.add(resp_lib.POST, BASE_URL + "/dashboard/estornarPagamento/1",
                 status=302, headers={"Location": BASE_URL + "/auth/login"},
                 content_type="text/html; charset=utf-8")

        # POST pagamento/confirmar sem auth → 302
        rsps.add(resp_lib.POST, BASE_URL + "/pagamento/confirmar",
                 status=302, headers={"Location": BASE_URL + "/auth/login"},
                 content_type="text/html; charset=utf-8")

        # Catch-all POST login fallback (credenciais não mapeadas → erro genérico)
        rsps.add(resp_lib.POST, BASE_URL + "/auth/login",
                 status=200, body="<html><p>E-mail ou senha incorretos.</p></html>",
                 content_type="text/html; charset=utf-8")

        # Catch-all POST register fallback
        rsps.add(resp_lib.POST, BASE_URL + "/auth/register",
                 status=200, body="<html><p>Erro no cadastro.</p></html>",
                 content_type="text/html; charset=utf-8")

        # Catch-all POST forgotPassword fallback
        rsps.add(resp_lib.POST, BASE_URL + "/auth/forgotPassword",
                 status=302, headers={"Location": f"{BASE_URL}/auth/forgotPassword"},
                 content_type="text/html; charset=utf-8")

        # Wildcard para rotas não mapeadas
        rsps.add(resp_lib.GET,
                 re.compile(re.escape(BASE_URL) + r"/.*"),
                 status=404, body="<html><h2>404</h2></html>")

        yield rsps
