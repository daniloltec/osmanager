# OS Manager — versão standalone (fora do GLPI)

Página simples em PHP para controle interno de Ordens de Serviço, lendo os
chamados do banco do GLPI e gravando os dados da OS em tabelas próprias.

## O que faz
- Lista os chamados do GLPI e permite criar uma OS vinculada a cada um.
- Campos: diagnóstico, status (Rascunho / Em andamento / Concluída),
  técnico, cliente e checklist dinâmico.
- Tela de impressão "Salvar como PDF" pelo navegador.
- Sem login (uso em rede interna).

## Deploy na VPS
1. Copie a pasta `app/` para o servidor, por exemplo `/var/www/osmanager`.
2. Edite `app/config.php` e ajuste `GLPI_ROOT` para o caminho do seu GLPI
   (ex: `/var/www/glpi`). Ele já puxa usuário/senha do banco do GLPI sozinho.
3. Acesse no navegador: `http://SEU_SERVIDOR/osmanager/install.php`
   para criar as tabelas `osmanager_orders` e `osmanager_checklist`.
4. Apague o `install.php` e acesse `index.php`.

## Atualizar na VPS (git)
Na VPS, dentro da pasta do app:
```bash
git pull origin main
```

## Arquivos
- `config.php`  — conexão com o banco (reusa credenciais do GLPI)
- `common.php`  — helpers de interface
- `install.php` — cria as tabelas (use uma vez)
- `index.php`   — lista chamados do GLPI
- `os.php`      — criar/editar OS
- `print.php`   — visualização de impressão / PDF
