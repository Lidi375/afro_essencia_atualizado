# -*- coding: utf-8 -*-
"""
test_avaliacao_validation.py — Testes unitários das regras de validação de avaliação
e da correção de URL/redirect (sem servidor PHP).
"""

import pytest
import re


# ─── Funções que espelham a lógica PHP ────────────────────────────────────────

def validar_nota(nota) -> dict:
    """Espelha validação do AvaliacaoController::store()."""
    try:
        n = int(nota)
    except (ValueError, TypeError):
        return {"valida": False, "erros": ["Nota deve ser um número inteiro"]}
    erros = []
    if n < 1 or n > 5:
        erros.append("Nota deve ser entre 1 e 5")
    return {"valida": len(erros) == 0, "erros": erros}


def sanitizar_comentario(texto: str) -> str:
    """Espelha strip_tags() + trim() do PHP."""
    limpo = re.sub(r'<[^>]+>', '', texto)
    return limpo.strip()


def redirect_sanitizar(path: str) -> str:
    """
    Espelha a lógica CORRIGIDA do BaseController::redirect().
    Separa caminho de query string antes de sanitizar.
    """
    parts = path.split('?', 1)
    safe_path = re.sub(r'[^a-zA-Z0-9\-_/]', '', parts[0])
    safe_query = ''
    if len(parts) > 1:
        safe_query = '?' + re.sub(r'[^a-zA-Z0-9\-_=&]', '', parts[1])
    location = '/afro_essencia/' + safe_path + safe_query
    location = location.replace('\r', '').replace('\n', '')
    return location


def url_link_avaliacao(agendamento_id: int) -> str:
    """Gera o href correto para o botão Avaliar (com query string)."""
    return f"/afro_essencia/avaliacao/create?agendamento_id={agendamento_id}"


# ─── Testes de nota ───────────────────────────────────────────────────────────

@pytest.mark.parametrize("nota", [1, 2, 3, 4, 5])
def test_notas_validas(nota):
    r = validar_nota(nota)
    assert r["valida"], f"Nota {nota} deveria ser válida"

@pytest.mark.parametrize("nota,motivo", [
    (0,   "abaixo do mínimo"),
    (6,   "acima do máximo"),
    (-1,  "negativo"),
    (10,  "muito alto"),
    ("",  "vazio"),
    ("abc", "texto"),
    (None,  "None"),
])
def test_notas_invalidas(nota, motivo):
    r = validar_nota(nota)
    assert not r["valida"], f"Nota inválida aceita ({motivo}): {nota!r}"


# ─── Testes de sanitização de comentário ─────────────────────────────────────

@pytest.mark.parametrize("entrada,esperado", [
    ("Ótimo atendimento!", "Ótimo atendimento!"),
    ("<script>alert(1)</script>", "alert(1)"),
    ("<b>Muito bom</b>", "Muito bom"),
    ("  espaços  ", "espaços"),
    ("", ""),
    ("<img src=x onerror=alert(1)>", ""),
])
def test_sanitizacao_comentario(entrada, esperado):
    assert sanitizar_comentario(entrada) == esperado


# ─── Testes da correção do redirect (bug #2) ──────────────────────────────────

def test_redirect_preserva_query_string():
    """
    Bug corrigido: redirect('avaliacao/create?agendamento_id=5')
    antes destruía o '?' e '=' — agora deve preservar.
    """
    resultado = redirect_sanitizar("avaliacao/create?agendamento_id=5")
    assert "agendamento_id=5" in resultado, \
        f"Query string destruída: {resultado}"
    assert "?" in resultado


def test_redirect_sem_query_string_funciona():
    """Redirect sem query string continua funcionando normalmente."""
    resultado = redirect_sanitizar("agendamento/index")
    assert resultado == "/afro_essencia/agendamento/index"


def test_redirect_nao_permite_header_injection():
    """Redirect com \\r\\n não deve vazar para o header."""
    payload = "agendamento/index\r\nX-Evil: injected"
    resultado = redirect_sanitizar(payload)
    assert "\r" not in resultado
    assert "\n" not in resultado


def test_redirect_preserva_multiplos_params():
    """Query string com múltiplos parâmetros é preservada."""
    resultado = redirect_sanitizar("avaliacao/create?agendamento_id=3&ref=index")
    assert "agendamento_id=3" in resultado
    assert "ref=index" in resultado


def test_redirect_remove_chars_invalidos_no_caminho():
    """Caracteres especiais no caminho são removidos."""
    resultado = redirect_sanitizar("avaliacao/<script>/create")
    assert "<" not in resultado
    assert ">" not in resultado
    assert "script" in resultado  # apenas o texto é mantido


# ─── Testes do link correto (bug #1) ─────────────────────────────────────────

def test_link_avaliacao_usa_query_string():
    """
    Bug corrigido: link usava /avaliacao/create/{id} (segmento de rota)
    — controller lia $_GET['agendamento_id'] que nunca chegava.
    Agora deve usar query string.
    """
    link = url_link_avaliacao(42)
    assert "agendamento_id=42" in link, f"ID não está na query string: {link}"
    assert "?" in link
    assert link.endswith("?agendamento_id=42")


def test_link_avaliacao_nao_usa_segmento():
    """O link não deve colocar o ID como segmento de rota."""
    link = url_link_avaliacao(42)
    # Não deve terminar com /42 sem query string
    assert not link.endswith("/42")


@pytest.mark.parametrize("agendamento_id", [1, 5, 99, 1000])
def test_link_avaliacao_vários_ids(agendamento_id):
    """Link gerado corretamente para diferentes IDs."""
    link = url_link_avaliacao(agendamento_id)
    assert f"agendamento_id={agendamento_id}" in link
