# AFRO ESSÊNCIA — Sistema de Agendamento

> 🌐 **Vitrine online:** `https://SEU-USUARIO.github.io/NOME-DO-REPOSITORIO/`
> (veja "Publicar no GitHub Pages" abaixo)

Sistema web de agendamento para salão especializado em beleza afro.

---

## Requisitos

| Item | Versão mínima |
|------|---------------|
| PHP  | 7.4 (recomendado 8.1+) |
| MySQL / MariaDB | 5.7 / 10.3 |
| Apache | mod_rewrite habilitado |

---

## Instalação no XAMPP (desenvolvimento)

### 1. Copiar o projeto
```
htdocs/afro_essencia/
```

### 2. Criar o banco de dados
- Abra o **phpMyAdmin**: http://localhost/phpmyadmin
- Crie um banco chamado `afro_essencia`
- Importe o arquivo **`database/afro_essencia.sql`**

> **Banco já existente de versão anterior?** Execute apenas as migrations necessárias:
> - `database/servico_detalhes_migration.sql` — slugs e cuidados pós-serviço
> - `database/pagamento_migracao.sql` — sistema de pagamentos
> - `database/pagamento_gateway_migration.sql` — gateway online
> - `database/trabalhe_conosco_migration.sql` — candidaturas

### 3. Configurar credenciais
Copie os arquivos de exemplo e preencha:
```bash
cp config/database.example.php  config/database.php
cp config/mail.example.php      config/mail.php
cp config/pagamento.example.php config/pagamento.php
```

Edite cada arquivo com suas credenciais reais.

### 4. Acessar
```
http://localhost/afro_essencia
```

**Usuários de teste (senha: `password`):**

| E-mail | Perfil |
|--------|--------|
| admin@teste.com | Administrador |
| profissional@teste.com | Profissional |
| cliente@teste.com | Cliente |

---

## Deploy em produção (hospedagem compartilhada / VPS)

### 1. Upload dos arquivos
Envie todos os arquivos para `public_html/` (ou subpasta, ex: `public_html/afro_essencia/`).

### 2. Banco de dados
Crie o banco no painel do hosting (cPanel, Plesk, etc.) e importe `database/afro_essencia.sql`.

### 3. Ajustar configurações

**`config/database.php`** — credenciais do banco de produção.

**`config/mail.php`** — credenciais SMTP de produção.

**`config/pagamento.php`** — substituir token TEST do MercadoPago pelo token de produção (`APP_USR-...`).

**`.htaccess`** — se o projeto estiver na raiz do domínio (não em subpasta), altere:
```apache
RewriteBase /
```
E ajuste `APP_BASE` em `config/database.php`:
```php
define('APP_BASE', '');
```

### 4. Variáveis de ambiente (recomendado)
Em vez de editar os arquivos de config, defina no cPanel → "Variables d'ambiente":
```
APP_ENV=production
MP_ACCESS_TOKEN=APP_USR-seu-token-real
MP_WEBHOOK_SECRET=sua-chave-webhook
APP_BASE_URL=https://seudominio.com.br
```

### 5. SSL/HTTPS
Após ativar o certificado SSL, ative o HSTS descomentando no `.htaccess`:
```apache
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
```

### 6. Webhook do MercadoPago
Configure no painel do MercadoPago a URL de notificação:
```
https://seudominio.com.br/afro_essencia/pagamento/webhook
```

---

## Segurança — checklist de produção

- [ ] `APP_ENV=production` definido (desativa display_errors)
- [ ] Token MercadoPago de **produção** configurado (não TEST-)
- [ ] Credenciais Gmail de produção em `config/mail.php`
- [ ] `APP_URL` apontando para o domínio real em `config/mail.php`
- [ ] SSL ativo no domínio
- [ ] HSTS habilitado no `.htaccess`
- [ ] Usuários de teste removidos ou com senhas alteradas
- [ ] Conta admin com senha forte (não "password")
- [ ] Backup automático do banco configurado no hosting

---

## Checklist completo de produção

### 🔴 Obrigatório antes do deploy

- [ ] Definir `MAIL_USERNAME` e `MAIL_PASSWORD` como variáveis de ambiente no servidor (cPanel → "Variables d'ambiente" ou `.env`)
- [ ] Token MercadoPago de **produção** em `config/pagamento.php` (começa com `APP_USR-`, não `TEST-`)
- [ ] `APP_ENV=production` definido no servidor (desativa `display_errors`)
- [ ] Remover ou alterar usuários de teste (`admin@teste.com` / senha `password`)
- [ ] Alterar senha da conta admin para algo forte (min. 16 chars)

### 🟠 No momento do deploy

- [ ] Rodar `composer install --no-dev` no servidor (sem dependências de desenvolvimento)
- [ ] Verificar que `config/mail.php`, `config/database.php` e `config/pagamento.php` **não** estão no repositório
- [ ] Confirmar que `APP_BASE` em `config/database.php` está correto (`''` para raiz, `'/afro_essencia'` para subpasta)
- [ ] Ativar certificado SSL no domínio
- [ ] Descomentar redirect HTTPS no `.htaccess`
- [ ] Descomentar HSTS no `.htaccess` (apenas após confirmar HTTPS funcionando)
- [ ] Atualizar URL do Webhook MercadoPago no painel para o domínio real
- [ ] Atualizar `robots.txt` com o domínio real no campo `Sitemap:`

### 🟡 Primeira semana em produção

- [ ] Configurar backup automático do banco no hosting (cPanel → Backup Wizard)
- [ ] Testar fluxo completo de agendamento e pagamento em ambiente real
- [ ] Testar e-mails transacionais (cadastro, redefinição de senha, confirmação)
- [ ] Verificar que `logs/` está vazio e monitorar crescimento
- [ ] Bump do `APP_VERSION` em `index.php` a cada deploy (invalida cache dos assets)
- [ ] Instalar certificado SSL renovável automaticamente (Let's Encrypt via cPanel)
- [ ] Testar responsividade em dispositivos reais (iOS Safari, Android Chrome)

### 🟢 Boas práticas contínuas

- [ ] Usar `.gitignore` — nunca commitar `config/mail.php`, `config/database.php`, `config/pagamento.php`
- [ ] Rotacionar senha de app Gmail a cada 6 meses
- [ ] Manter PHPMailer atualizado: `composer update`
- [ ] Verificar logs de erro semanalmente
- [ ] Página de manutenção: renomear `manutencao.php` para `index.php` quando precisar de downtime planejado

---

## Variáveis de ambiente recomendadas

Configure no cPanel → "Variables d'ambiente" ou no painel do seu hosting:

```
APP_ENV=production
MAIL_USERNAME=seuemail@gmail.com
MAIL_PASSWORD=sua-senha-de-app-gmail
MP_ACCESS_TOKEN=APP_USR-seu-token-producao
MP_WEBHOOK_SECRET=sua-chave-webhook
APP_BASE_URL=https://seudominio.com.br
```

---

## Estrutura de pastas

```
afro_essencia/
├── app/
│   ├── controllers/       ← Lógica de requisição
│   ├── dao/               ← Acesso ao banco
│   ├── gateways/          ← Integrações de pagamento
│   ├── helpers/           ← Funções auxiliares
│   ├── models/            ← Modelos de dados
│   ├── services/          ← Regras de negócio
│   └── views/             ← Templates HTML/PHP
├── config/                ← Configurações (não versionar com credenciais)
├── logs/                  ← Logs de erro da aplicação
├── public/
│   ├── css/
│   ├── js/
│   └── imagens/
├── uploads/               ← Arquivos enviados por usuários
├── vendor/                ← PHPMailer (incluído)
├── .htaccess
├── database/              ← Schema + migrations SQL
├── docs/                  ← Vitrine estática (GitHub Pages)
└── index.php              ← Front controller
```

---

## Suporte PHP

O sistema usa recursos do **PHP 7.4+**. O gateway de pagamento usa `typed properties` (7.4).  
Recursos PHP 8.x foram evitados (`match`, `str_contains`) para máxima compatibilidade.

---

## Publicar no GitHub Pages

O GitHub Pages hospeda apenas arquivos estáticos, então ele **não executa o PHP/MySQL** do sistema.
Por isso a pasta `docs/` contém uma vitrine estática (HTML + CSS + imagens otimizadas) que é o que vai ao ar.

1. Crie um repositório no GitHub e envie o projeto:
   ```bash
   git init
   git add .
   git commit -m "Primeira versão"
   git branch -M main
   git remote add origin https://github.com/SEU-USUARIO/NOME-DO-REPOSITORIO.git
   git push -u origin main
   ```
2. No GitHub: **Settings → Pages → Build and deployment**
   - Source: **Deploy from a branch**
   - Branch: **main** · Folder: **/docs** → **Save**
3. Em ~1 minuto o site estará em `https://SEU-USUARIO.github.io/NOME-DO-REPOSITORIO/`.

> Para rodar o sistema completo (login, agendamento, pagamentos) é preciso um servidor com PHP e MySQL
> (XAMPP local, hospedagem compartilhada ou VPS) — veja as seções acima.

## Arquivos que NÃO sobem ao GitHub

O `.gitignore` já protege: `config/database.php`, `config/mail.php`, `config/pagamento.php`, `.env`,
logs e currículos enviados em `uploads/candidaturas/`. Use os arquivos `*.example.php` como modelo.
