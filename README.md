# OS Manager (Plugin GLPI 11)

Gera **Ordem de Serviço** a partir de chamados existentes do GLPI, com checklist
por modelo, diagnóstico, assinaturas e exportação **PDF profissional**.

Tudo roda **dentro do próprio GLPI** — sem app externa, sem banco separado, sem Docker.

## O que faz

- Adiciona botão **"Ordem de Serviço"** no Ticket.
- Lê os dados do chamado direto do banco do GLPI.
- Checklist por categoria (Notebook, Desktop, Servidor, Impressora...).
- Diagnóstico em texto.
- Assinatura do técnico e do cliente (canvas HTML5 → PNG).
- Botão **Gerar PDF** (layout estilo Dell/Lenovo) usando o TCPDF do GLPI.

## Estrutura

```
osmanager/
├── setup.php        # install/uninstall (tabelas) + hook de botão
├── hook.php         # injeta botão no Ticket + helpers de config
├── inc/             # classes (Order, ChecklistTemplate, OrderChecklist, Signature)
├── front/
│   ├── order.php    # tela principal da OS
│   ├── pdf.php      # geração do PDF
│   └── config.php   # empresa/logo + seed de modelos
└── ajax/
    └── signature.php # salva assinatura
```

## Instalação

1. Copie a pasta `osmanager/` para `glpi/plugins/osmanager`.
2. Acesse **Configuração > Plugins** e instale o **OS Manager**.
3. Em **Configuração > Plugins > OS Manager**, defina Empresa/Logo e clique em
   "Criar modelos de checklist padrão".
4. Dê direito de uso (`plugin_osmanager`) ao perfil desejado em **Administração > Perfis**.

## Tabelas criadas (no DB do GLPI)

- `glpi_plugin_osmanager_orders`
- `glpi_plugin_osmanager_templates`
- `glpi_plugin_osmanager_template_items`
- `glpi_plugin_osmanager_order_checklist`
- `glpi_plugin_osmanager_signatures`
- `glpi_plugin_osmanager_configs`

## Roadmap (opcional, futuro)

- Anexar o PDF automaticamente ao Ticket (timeline).
- QR Code de autenticidade no PDF.
- Fotos (antes/durante/depois) com upload.
- Cronômetro de atendimento.
- Webhook/Notificações.

## Notas de produção (segurança)

O plugin foi feito para funcionar localmente sem complicação. Para produção:

- **CSRF:** atualmente NÃO declaramos `$PLUGIN_HOOKS['csrf_compliant']['osmanager'] = true;`
  em `plugin_init_osmanager()`. Isso relaxa a checagem de token nos POSTs (form do
  `front/order.php` e AJAX de assinatura). Em produção, ative o CSRF e:
  - use `Html::closeForm()` (que injeta o token) em vez de `</form>` manual;
  - envie o token GLPI no `fetch()` de `ajax/signature.php` via header/campo `_glpi_csrf_token`.
- **Direitos:** o rightname é `plugin_osmanager` (READ/UPDATE). Conceda apenas aos
  perfis que realmente abrem/manipulam OS. Sem o direito, o botão some do Ticket.
- **Assinaturas:** os PNGs ficam em `GLPI_PLUGIN_DOC_DIR/osmanager/signatures`. Proteja
  esse diretório no servidor (fora do docroot ou com regra de acesso) para não expor
  dados pessoais (LGPD).
- **Logo:** o caminho do logo é absoluto no servidor; nunca exponha via web direto.
- **Validação:** os campos de checklist/diagnóstico são texto livre; sanitize na saída
  (já usamos `htmlspecialchars` na tela e o TCPDF com UTF-8 no PDF).
- **Backup:** como as tabelas vivem no DB do GLPI, o backup do GLPI já cobre a OS.

