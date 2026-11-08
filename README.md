# Ticket-Flow

Sistema interno de gestão de chamados com Laravel, Blade, Tailwind CSS e Alpine.js.

## Visão geral

O Ticket-Flow organiza solicitações de suporte por área, operador, prioridade e status. O sistema possui três perfis:

- **Admin**: gerencia usuários e áreas e tem acesso administrativo, gera relatorios.
- **Operador**: acompanha tickets da sua área, visualiza tickets atribuídos, responde, altera status e atribui operadores.
- **Solicitante**: cria tickets, acompanha os próprios chamados e atualiza título, descrição, área e prioridade.

Tickets usam os status `open`, `in_progress` e `closed`, e as prioridades `low`, `medium` e `high`.

## Requisitos

- PHP 8.2+
- Composer
- Node.js e npm
- SQLite para desenvolvimento local, ou outro banco suportado pelo Laravel

## Instalação

```bash
composer setup
php artisan db:seed
```

`composer setup` instala dependências, cria o `.env`, gera a chave, executa migrations, instala pacotes npm e compila os assets. A seed é executada separadamente para evitar dados de desenvolvimento em ambientes indevidos.

## Desenvolvimento

Para iniciar servidor Laravel, listener de filas e Vite em paralelo:

```bash
composer dev
```

Ou execute os processos separadamente:

```bash
php artisan serve
php artisan queue:work
npm run dev
```

A aplicação ficará disponível, por padrão, em `http://127.0.0.1:8000`.

## Dados de desenvolvimento

O seeder cria as áreas e papéis necessários e também o usuário administrativo:

- E-mail: `admin@admin.com`
- Senha: `admin`

Essas credenciais são somente para desenvolvimento local. Não as use em produção.

Usuários operadores e solicitantes podem ser criados pelo painel administrativo depois do login como admin.

## Fluxos principais

### Solicitante

- É redirecionado para `solicitante.dashboard`.
- Cria tickets em `tickets.create`.
- Acessa somente os próprios tickets.
- Pode editar título, descrição, área e prioridade.

### Operador

- É redirecionado para `operador.dashboard`.
- Visualiza tickets da própria área.
- Usa `tickets.index?assigned=1` para ver somente tickets atribuídos a si.
- Pode criar tickets, responder, mudar status e atribuir outro operador na edição.

### Admin

- É redirecionado para `admin.dashboard`.
- Gerencia usuários em `admin.users.*`.
- Gerencia áreas em `admin.areas.*`.
- A autorização administrativa usa o middleware `admin`.

## Auditoria e notificações

Alterações de status ou respostas de operadores são registradas em `ticket_audits` com:

- usuário responsável;
- status anterior;
- novo status;
- comentário;
- data e hora.

O solicitante recebe uma notificação pelos canais de banco de dados e e-mail. O e-mail é enfileirado; mantenha um worker ativo com:

```bash
php artisan queue:work
```

Em desenvolvimento, o mailer padrão é `log`. Para envio real, configure `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD` e `MAIL_FROM_ADDRESS` no `.env`.

## Frontend e componentes

O layout autenticado usa uma sidebar fixa responsiva em [resources/views/layouts/app.blade.php](resources/views/layouts/app.blade.php). Os componentes reutilizáveis ficam em [resources/views/components](resources/views/components):

- `x-button`
- `x-badge`
- `x-card`
- `x-data-table`
- `x-toast`
- `x-skeleton`
- `x-input-label`
- `x-text-input`
- `x-input-error`

Tokens de cor, tipografia, sombras e bordas estão em [tailwind.config.js](tailwind.config.js). Ao alterar Blade, CSS ou JavaScript, compile os assets:

```bash
npm run build
```

## Testes

Execute toda a suíte:

```bash
composer test
```

Ou:

```bash
php artisan test
php artisan test --filter="nome do teste"
```

Os testes de feature usam SQLite em memória e `RefreshDatabase`. As áreas de maior risco cobertas atualmente incluem autorização de tickets, atribuição de operadores, auditoria, notificações, dashboards por papel e CRUD de áreas.

## Banco de dados

O banco local padrão é SQLite. Depois de alterar o schema:

```bash
php artisan migrate
```

Para aplicar migrations sem prompt em um ambiente controlado:

```bash
php artisan migrate --force
```

As migrations principais cobrem usuários, papéis, áreas, tickets, auditoria de tickets e notificações.

## Estrutura relevante

- [app/Models](app/Models): entidades Eloquent.
- [app/Policies/TicketPolicy.php](app/Policies/TicketPolicy.php): autorização de tickets.
- [app/Notifications](app/Notifications): notificações de atualização.
- [app/Http/Controllers](app/Http/Controllers): comportamento HTTP.
- [resources/views/layouts](resources/views/layouts): layouts compartilhados.
- [resources/views/components](resources/views/components): componentes Blade.
- [resources/views/tickets](resources/views/tickets): criação, listagem e edição de tickets.
- [routes/web.php](routes/web.php): rotas de aplicação e dashboards.
- [database/seeders](database/seeders): dados iniciais locais.

## Referências

- [Documentação oficial do Laravel](https://laravel.com/docs)
- [Documentação do Blade](https://laravel.com/docs/blade)
- [Documentação do Tailwind CSS](https://tailwindcss.com/docs)
- [Documentação do Alpine.js](https://alpinejs.dev/start-here)
