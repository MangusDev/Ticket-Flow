# Copilot instructions for Ticket-Flow

## Build, test, and lint

- `composer setup` installs PHP and npm dependencies, initializes `.env` and the app key, migrates the local database, and builds frontend assets.
- `composer dev` runs the Laravel server, queue listener, and Vite together.
- `composer test` clears cached configuration and runs the full Pest suite. `php artisan test` runs the suite without Composer's config-clear step.
- Run one test file with `php artisan test tests/Feature/TicketAuthorizationTest.php`. Run one named test with `php artisan test --filter="requesters cannot update another users ticket"`.
- `vendor/bin/pint --test` checks PHP formatting; `vendor/bin/pint` applies the repository's Pint formatting.
- `npm run build` compiles the Vite frontend assets. For Blade changes, `php artisan view:cache` checks that views compile.
- Tests use in-memory SQLite, array-backed sessions/cache/mail, and a synchronous queue as configured in `phpunit.xml`.

## Architecture

- This is a Laravel 12 server-rendered application: `routes/web.php` and `routes/auth.php` define web and authentication endpoints; controllers validate requests and return Blade views or redirects. The frontend is Blade with Tailwind CSS and Alpine.js, bundled by Vite; shared layout and reusable components live under `resources/views`.
- `routes/web.php` routes authenticated users through the `active` middleware, redirects `/dashboard` by role, and groups administration routes behind `auth`, `admin`, and `active`. The `role`, `admin`, and `active` middleware aliases are registered in `bootstrap/app.php`.
- Ticket persistence and relationships are defined in `app/Models/Ticket.php`. `TicketPolicy` is the source of ticket authorization rules; `TicketController` enforces abilities and validates role-specific updates, while `App\Services\TicketService` handles ticket queries and mutations.
- Ticket updates can change the ticket, create `TicketAudit` history, and notify requesters or operators. Keep these related writes and notification dispatch in the service's transaction path. `TicketUpdatedNotification` delivers database and queued mail notifications.
- Administrative user and area CRUD is handled by controllers under `app/Http/Controllers/Admin`; their routes use the `admin.*` name prefix.
- Feature tests exercise complete HTTP workflows and authorization, including dashboard role redirects, ticket visibility/assignment, audit and notification behavior, and area administration.

## Repository conventions

- Preserve the role names `admin`, `operador`, and `solicitante`; ticket statuses `open`, `in_progress`, and `closed`; and priorities `low`, `medium`, and `high`. Keep validation, policy logic, views, and tests aligned when changing these contracts.
- Authorization belongs in policies and middleware, not only in Blade visibility checks. Ticket access differs by role: requesters act on their own tickets, operators work with tickets in their area or assigned to them, and admins manage users/areas and have administrative ticket permissions. Verify both policy and route/middleware behavior when changing access.
- Requester ticket updates are replies/comments; operators manage ticket fields such as status, priority, area, and assignment. The feature tests in `tests/Feature/TicketAuthorizationTest.php` and `tests/Feature/TicketActivityTest.php` capture these boundaries and side effects.
- Preserve the established Eloquent relation names and foreign-key semantics (`requester` / `user_id`, `operator` / `operator_id`, and `area` / `area_id`) when changing ticket queries or views.
- Local development uses SQLite; tests use in-memory SQLite. Seeded admin credentials are for local development only (`admin@admin.com` / `admin`).
- User-facing interface text and test expectations are primarily Portuguese. Reuse the existing Blade components and Tailwind design tokens in `tailwind.config.js` for UI changes.
