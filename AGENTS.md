# Ticket-Flow agent guide

This repository is a Laravel 12 app for internal ticket management. Use [README.md](README.md) for product workflows and setup, and keep this file focused on repo-specific coding guidance.

## Repo map

- App entry points: [routes/web.php](routes/web.php), [routes/auth.php](routes/auth.php)
- Models: [app/Models](app/Models)
- HTTP behavior: [app/Http/Controllers](app/Http/Controllers)
- Ticket authorization: [app/Policies/TicketPolicy.php](app/Policies/TicketPolicy.php)
- Role enforcement: [app/Http/Middleware](app/Http/Middleware), especially [bootstrap/app.php](bootstrap/app.php)
- Shared layout: [resources/views/layouts/app.blade.php](resources/views/layouts/app.blade.php)
- Reusable UI: [resources/views/components](resources/views/components)
- Feature coverage: [tests/Feature](tests/Feature)

## Project conventions

- Keep business logic in Laravel controllers, requests, models, routes, and Blade views; do not introduce a separate client-side framework.
- Preserve the role contract: `admin`, `operador`, and `solicitante`.
- Keep ticket status and priority values aligned with the app: `open`, `in_progress`, `closed`; `low`, `medium`, `high`.
- Route and middleware naming matter: regular ticket routes use `tickets.*`, admin routes use `admin.*`, and dashboard access depends on the `admin` and `role` middleware aliases configured in [bootstrap/app.php](bootstrap/app.php).
- Keep authorization in policies and middleware, not only in Blade conditions.
- Before renaming relation names or changing ticket fields, check [app/Models/Ticket.php](app/Models/Ticket.php); older code still references fields like `user`, `requester`, `operator`, and `area`.
- Ticket updates may create audit rows in [app/Models/TicketAudit.php](app/Models/TicketAudit.php) and notifications in [app/Notifications/TicketUpdatedNotification.php](app/Notifications/TicketUpdatedNotification.php); keep those updates in the same transaction.
- Requesters can reply to their own tickets but cannot edit ticket fields; operators manage visible/assigned tickets; admins manage users and areas and can delete tickets.
- Preserve the default local setup: SQLite for local dev and in-memory SQLite for tests, as configured in [phpunit.xml](phpunit.xml).
- The seeded admin account is `admin@admin.com` / `admin` for local development only.

## Common commands

- Install dependencies and initialize the app: `composer setup`
- Run the full local stack: `composer dev`
- Run tests: `composer test` or `php artisan test --filter="..."`
- Build frontend assets: `npm run build`
- Run Vite in dev mode: `npm run dev`
- Apply migrations without prompting: `php artisan migrate --force`
- Process queued mail/notifications: `php artisan queue:work`
- Cache Blade views before deploy checks: `php artisan view:cache`

## Change checklist

- Inspect the matching route, controller, model, migration, and Blade view before editing a feature.
- Add or update focused Pest coverage for ticket, role, area, and admin authorization changes.
- When changing shared layouts/components/CSS/JS, run the relevant frontend validation and keep generated assets out of source changes.
- When editing ticket permissions or dashboard redirects, verify both middleware and policy behavior.
- For layout/component changes, run `php artisan view:cache` after the update.

## Relevant docs

- [README.md](README.md)
- [composer.json](composer.json)
- [package.json](package.json)
- [phpunit.xml](phpunit.xml)