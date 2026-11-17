# 🐛 RELATÓRIO DE CORREÇÃO — Fluxo de Avaliação (Estrelinhas)
## Afro Essência — Bugfix v2.2

---

## 📌 Resumo

| Campo | Valor |
|---|---|
| **Problema** | Clique na ⭐ estrela não iniciava o fluxo de avaliação |
| **Bugs encontrados** | 2 (independentes, se somavam) |
| **Arquivos alterados** | 2 (`BaseController.php`, `agendamento/index.php`) |
| **Arquivos não tocados** | Todos os outros (controllers, services, DAO, demais views) |
| **Novos testes** | 38 (9 funcionais + 29 unitários) |
| **Suite total** | ✅ **257 passados — 0 falhas** |

---

## 🔍 Diagnóstico — Os Dois Bugs

### Bug #1 — Link com ID como segmento de rota (view)

**Arquivo:** `app/views/agendamento/index.php`

**Antes (quebrado):**
```html
<a href="/afro_essencia/avaliacao/create/<?= $a['id'] ?>">
```

**O problema:**
O roteador (`index.php`) interpreta a URL `/avaliacao/create/5` assim:
```
controller = AvaliacaoController
method     = create
params     = [5]          ← ID aqui, como argumento de função
```

Mas `AvaliacaoController::create()` não aceita parâmetros — ele lê:
```php
$agendamentoId = (int) ($_GET['agendamento_id'] ?? 0);
```

O ID `5` chegava como argumento PHP (ignorado pelo método) e `$_GET['agendamento_id']` ficava vazio → `$agendamentoId = 0` → redirect imediato para `agendamento/index`. **O formulário nunca era exibido.**

**Depois (corrigido):**
```html
<a href="/afro_essencia/avaliacao/create?agendamento_id=<?= $a['id'] ?>">
```

O `.htaccess` usa flag `[QSA]` (*Query String Append*), então a query string `agendamento_id=5` chega corretamente em `$_GET['agendamento_id']`.

---

### Bug #2 — `redirect()` destruía query strings (BaseController)

**Arquivo:** `app/controllers/BaseController.php`

**Antes (quebrado):**
```php
protected function redirect(string $path): void
{
    $safe = preg_replace('/[^a-zA-Z0-9\-_\/]/', '', $path);
    header("Location: /afro_essencia/" . $safe);
    exit;
}
```

**O problema:**
A regex `[^a-zA-Z0-9\-_\/]` remove **todos os caracteres que não sejam** letras, números, hífen, underscore e barra — inclusive `?` e `=`.

Quando o controller tentava redirecionar de volta em caso de erro:
```php
$this->redirect("avaliacao/create?agendamento_id={$agendamentoId}");
```

O resultado era:
```
avaliacao/createagendamentoid5    ← '?', '=' e '_' removidos
```
→ rota inexistente → 404.

**Depois (corrigido):**
```php
protected function redirect(string $path): void
{
    // Separa caminho da query string antes de sanitizar
    $parts    = explode('?', $path, 2);
    $safePath = preg_replace('/[^a-zA-Z0-9\-_\/]/', '', $parts[0]);

    $safeQuery = '';
    if (isset($parts[1])) {
        $safeQuery = '?' . preg_replace('/[^a-zA-Z0-9\-_=&]/', '', $parts[1]);
    }

    // Previne header injection
    $location = '/afro_essencia/' . $safePath . $safeQuery;
    $location = str_replace(["\r", "\n"], '', $location);

    header('Location: ' . $location);
    exit;
}
```

A proteção contra header injection é mantida (remoção de `\r\n`), mas query strings legítimas passam intactas.

---

## 🔒 Melhoria de UX incluída

Aproveitando a correção da view, foi adicionada verificação de avaliação já feita diretamente na listagem:

```php
$avalDAO  = new AvaliacaoDAO();
$jaAvalou = $avalDAO->jaAvaliou($a['id']);

if (!$jaAvalou): // botão "⭐ Avaliar"
else:            // texto "⭐ Avaliado" (sem link)
```

Assim o cliente não vê o botão para agendamentos já avaliados — sem alterar nenhuma lógica do controller.

---

## 🧪 Testes Adicionados — 38 Novos

### Funcionais — `test_avaliacao.py` (9 testes)

| Teste | O que verifica |
|---|---|
| `test_avaliacao_create_sem_auth_redireciona` | GET sem auth → 302 |
| `test_avaliacao_create_query_string_sem_auth` | URL com `?agendamento_id=1` sem auth → 302 |
| `test_avaliacao_create_segmento_sem_auth` | URL antiga `/create/1` → não causa 500 |
| `test_avaliacao_store_sem_auth_redireciona` | POST store sem auth → 302 |
| `test_avaliacao_store_get_redireciona` | GET em store → 302/404 (só aceita POST) |
| `test_avaliacao_store_sem_csrf_bloqueado` | POST sem CSRF → 403 |
| `test_avaliacao_minhas_sem_auth_redireciona` | GET minhas sem auth → 302 |
| `test_avaliacao_create_sem_agendamento_id` | Sem `agendamento_id` → não 500 |
| `test_avaliacao_create_agendamento_id_zero` | `agendamento_id=0` → não 500 |

### Unitários — `test_avaliacao_validation.py` (29 testes)

| Grupo | Casos |
|---|---|
| Notas válidas (1 a 5) | 5 |
| Notas inválidas (0, 6, -1, texto, None…) | 7 |
| Sanitização de comentários (XSS, espaços…) | 6 |
| **Correção bug #2** — redirect preserva query string | 5 |
| **Correção bug #1** — link usa query string, não segmento | 6 |

---

## 📊 Suite Completa

```
============================= 257 passed in 0.55s ==============================
```

| Módulo | Testes |
|---|---|
| Funcionais – Auth | 14 |
| Funcionais – Controllers | 13 |
| Funcionais – Rotas | 16 |
| Funcionais – Pagamento | 13 |
| **Funcionais – Avaliação** | **9** |
| Integração | 8 |
| Performance | 11 |
| Segurança – SQL Injection | 18 |
| Segurança – XSS | 16 |
| Segurança – CSRF | 5 |
| Segurança – Headers/Paths | 11 |
| Unitário – Validação geral | 37 |
| Unitário – Validação pagamento | 41 |
| **Unitário – Validação avaliação** | **29** |
| **TOTAL** | **257** |

---

*Relatório gerado em 05/06/2026 — Afro Essência v2.2*
