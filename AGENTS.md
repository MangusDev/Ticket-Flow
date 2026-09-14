# Ticket-Flow agent guide

This repository is a Laravel 12 app built around ticket management, Blade views, Tailwind CSS, Alpine.js, and Vite. See [README.md](README.md) for the full product workflow and setup notes.

## Project shape

- App entry points: [routes/web.php](routes/web.php) and [routes/auth.php](routes/auth.php)
- Domain models: [app/Models](app/Models)
- HTTP behavior: [app/Http/Controllers](app/Http/Controllers)
- Shared layout: [resources/views/layouts/app.blade.php](resources/views/layouts/app.blade.php)
- Reusable UI: [resources/views/components](resources/views/components)
- Ticket authorization: [app/Policies/TicketPolicy.php](app/Policies/TicketPolicy.php)
- Role checks: [app/Http/Middleware/AdminMiddleware.php](app/Http/Middleware/AdminMiddleware.php)

## Common commands

- Install and initialize the project: `composer setup`
- Run the app stack: `composer dev`
- Run tests: `composer test` or `php artisan test --filter="..."`
- Build frontend assets: `npm run build`
- Run the Vite dev server: `npm run dev`
- Apply migrations: `php artisan migrate --force`
- Process queued mail notifications: `php artisan queue:work`
- Compile Blade templates before deploy checks: `php artisan view:cache`

## Coding conventions

- Keep behavior in Laravel controllers, requests, models, routes, and Blade views rather than introducing a separate frontend framework.
- Preserve route naming and role flow: regular ticket routes use `tickets.*`, admin routes use `admin.*`, and dashboards rely on the middleware aliases in [bootstrap/app.php](bootstrap/app.php).
- Use the shared application shell and reusable components instead of custom page scaffolding.
- Use Tailwind utility classes and Alpine for lightweight client-side interactions.
- Keep authorization in policies and middleware, not only in Blade conditions.
- Validate submitted fields server-side and update model relationships and `$fillable` fields whenever the schema changes.
- Preserve the seeded role contract: `admin`, `operador`, and `solicitante`.
- Check [app/Models/Ticket.php](app/Models/Ticket.php) before changing ticket relation names; older code may still refer to `user` instead of `requester`, `operator`, or `area`.
- Ticket updates may create audit entries in [app/Models/TicketAudit.php](app/Models/TicketAudit.php) and notifications in [app/Notifications/TicketUpdatedNotification.php](app/Notifications/TicketUpdatedNotification.php); keep those operations inside the same transaction.
- When changing shared layouts, components, CSS, or JavaScript, run the relevant build checks and keep generated assets out of source changes.
- Add or update focused Pest coverage for changed behavior before or alongside the fix.

## Important project notes

- Default local database is SQLite; tests use in-memory SQLite and array-backed cache/session settings from [phpunit.xml](phpunit.xml).
- The seeded admin account is `admin@admin.com` with password `admin` for local development only.
- Local mail is configured to log by default; queue workers are required for actual notification delivery in development.

## Change checklist

- Inspect the related route, controller, model, migration, and Blade view before editing a feature.
- Add focused Pest coverage for ticket, role, area, and admin authorization changes.
- Run the narrow test that checks the broken path, then the relevant broader suite when practical.
- Run `npm run build` for any CSS, JS, or Blade change that affects the compiled frontend.
- Run `php artisan view:cache` when changing shared layouts or components.