# Copilot instructions for Ticket-Flow

## Project overview

- This repository is a Laravel 12 application for internal support ticket management.
- The product is role-based: `admin`, `operador`, and `solicitante`.
- Ticket lifecycle values are fixed: `open`, `in_progress`, `closed` and `low`, `medium`, `high`.
- The app is server-rendered with Blade, Tailwind CSS, and Alpine.js; Vite builds the frontend assets.

## Build, test, and lint

- `composer setup` installs dependencies, creates `.env` if needed, generates the app key, runs migrations, installs npm packages, and builds frontend assets.
- `composer dev` starts the Laravel app, queue worker, and Vite together for local development.
- Full test suite: `composer test` (this clears config before running `php artisan test`).
- Run a single file: `php artisan test tests/Feature/TicketAuthorizationTest.php`
- Run one named test: `php artisan test --filter="requesters cannot update another users ticket"`
- PHP formatting: `vendor/bin/pint --test` checks formatting; `vendor/bin/pint` applies formatting.
- Frontend build: `npm run build`
- View compilation check for Blade changes: `php artisan view:cache`
- Database migration without prompt: `php artisan migrate --force`

## Architecture

- Route entry points live in `routes/web.php` and `routes/auth.php`; middleware aliases are registered in `bootstrap/app.php`.
- Auth flow is role-driven: `/dashboard` redirects by role to `admin.dashboard`, `operador.dashboard`, or `solicitante.dashboard`.
- Admin routes are grouped under the `admin.*` prefix and gated by `auth`, `admin`, and `active`.
- Ticket domain logic is split across `app/Models/Ticket.php`, `app/Services/TicketService.php`, and `app/Policies/TicketPolicy.php`.
- `TicketService` centralizes ticket listing/query logic and status changes, while `TicketPolicy` handles authorization boundaries. Keep policy and middleware checks consistent with the actual business rules.
- Ticket updates should keep status/audit/notification side effects in the same DB transaction: `TicketAudit` stores the audit row and `TicketUpdatedNotification` sends database/email notifications.
- Shared UI is organized in `resources/views/layouts/app.blade.php` and reusable Blade components under `resources/views/components`.
- Feature tests under `tests/Feature` cover role routing, ticket authorization, ticket activity, audits, dashboards, and admin area management.

## Repository conventions

- Preserve the existing role and enum contracts; changing them requires updates to routes, policies, views, and tests together.
- Keep authorization in policies and middleware, not only in Blade conditions. Requesters act on their own tickets, operators can work with tickets in their area or assigned to them, and admins manage users and areas.
- Preserve established Eloquent relation names and foreign-key semantics: `requester`/`user_id`, `operator`/`operator_id`, and `area`/`area_id`.
- Local development uses SQLite; tests use in-memory SQLite, as configured in `phpunit.xml`.
- The seeded admin account (`admin@admin.com` / `admin`) is for local development only.
- User-facing copy and test expectations are primarily in Portuguese; reuse the existing Blade components and Tailwind design tokens instead of creating ad hoc markup/CSS.
