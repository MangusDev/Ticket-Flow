# Ticket-Flow Agent Guide

## Project shape

- This is a Laravel 12 application targeting PHP 8.2+, with server-rendered Blade views, Alpine.js interactions, and Vite-managed frontend assets.
- HTTP routes are split between [routes/web.php](routes/web.php) and Breeze authentication routes in [routes/auth.php](routes/auth.php).
- Domain models live in [app/Models](app/Models); controllers are under [app/Http/Controllers](app/Http/Controllers), with admin controllers in its `Admin` subdirectory.
- The main domain is ticket management: `Ticket` belongs to a requester, optional operator, and `Area`; users also belong to `Role` and `Area`. Ticket movements are stored in `TicketAudit`.

## Commands

- Install and initialize locally with `composer setup`.
- Run the application stack with `composer dev` (Laravel server, queue listener, and Vite).
- Run the test suite with `composer test` or `php artisan test`; narrow it with `php artisan test --filter="..."`.
- Build frontend assets with `npm run build`; use `npm run dev` during frontend development.
- Run database seeders explicitly with `php artisan db:seed`. `composer setup` migrates but does not seed.
- Run `php artisan migrate --force` when applying migrations to the local database.
- Run `php artisan queue:work` to process queued ticket notification emails.
- Run `php artisan view:cache` to compile Blade templates before deployment checks.

## Conventions

- Keep web behavior in Laravel controllers, requests, models, routes, and Blade views rather than introducing a separate frontend framework.
- Use the shared application shell in [resources/views/layouts/app.blade.php](resources/views/layouts/app.blade.php): fixed responsive sidebar, topbar, breadcrumbs, and global toasts.
- Prefer reusable Blade components in [resources/views/components](resources/views/components), including `x-button`, `x-badge`, `x-card`, `x-data-table`, `x-toast`, `x-skeleton`, `x-input-label`, `x-text-input`, and `x-input-error`.
- Use Tailwind CSS utility classes and the custom `brand`/`ink` tokens, shadows, and radii configured in [tailwind.config.js](tailwind.config.js). Use Alpine.js for small client-side interactions.
- Put ticket views under `resources/views/tickets` and admin views under `resources/views/admin` to match route/controller boundaries.
- Preserve route naming: normal ticket routes use `tickets.*`; admin routes use the `admin.` prefix and `admin` middleware. The middleware alias is registered in [bootstrap/app.php](bootstrap/app.php).
- Follow existing Pest feature-test patterns and use `RefreshDatabase` for database-backed behavior. Add coverage for ticket, role, area, and admin authorization changes because those areas currently have little or no test coverage.
- Use migrations for schema changes and update model relationships and `$fillable` fields when persistence behavior changes.
- Keep authorization in [app/Policies/TicketPolicy.php](app/Policies/TicketPolicy.php) and middleware, not only in Blade conditions. Validate every submitted field server-side.
- Ticket updates may create an audit entry in [app/Models/TicketAudit.php](app/Models/TicketAudit.php) and notify the requester through [app/Notifications/TicketUpdatedNotification.php](app/Notifications/TicketUpdatedNotification.php). Preserve the transaction around these operations.

## Current implementation notes

- Verify ticket relationship names before changing queries: [app/Models/Ticket.php](app/Models/Ticket.php) defines `requester`, `operator`, and `area`; older controller code may still refer to `user`.
- Ticket resource routes currently expect views under `resources/views/tickets`; check that the view exists before relying on a controller action.
- Admin authorization is role-name based in [app/Http/Middleware/AdminMiddleware.php](app/Http/Middleware/AdminMiddleware.php), so preserve the seeded role contract (`admin`, `operador`, `solicitante`) unless changing the authorization design deliberately.
- Role dashboards are protected by the `role` middleware alias registered in [bootstrap/app.php](bootstrap/app.php): admins use `admin.dashboard`, operators use `operador.dashboard`, and requesters use `solicitante.dashboard`.
- Operators can view tickets from their area, filter tickets explicitly assigned to them with `?assigned=1`, create tickets, respond, change status, and assign another operator. Requesters can create and update their own ticket content but cannot assign operators, respond as staff, or change status.
- The default local database is SQLite. Tests use in-memory SQLite and array-backed cache/session settings from [phpunit.xml](phpunit.xml).
- The development seeder creates `admin@admin.com` with password `admin`; never treat seeded credentials as production credentials.
- The notification channel uses the mail configuration in [config/mail.php](config/mail.php) and the database `notifications` table; local development defaults to the log mailer.

## Change checklist

- Inspect related routes, controller, model, migration, and Blade view before editing a feature.
- Add or update focused Pest coverage for changed behavior, then run the narrow test and the full suite when practical.
- Run `npm run build` when changing CSS, JavaScript, or Blade markup that affects compiled assets.
- Run `php artisan view:cache` when changing shared Blade layouts or components.
- Keep generated outputs such as `public/build`, `public/hot`, and dependency directories out of source changes.

See [README.md](README.md) for local setup, roles, workflows, and deployment notes.