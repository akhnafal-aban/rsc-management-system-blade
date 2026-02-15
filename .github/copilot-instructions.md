<!--
Project-specific Copilot / AI agent instructions.
Keep this file ~20-50 lines. Be precise and reference repository patterns.
-->

# Copilot Instructions — rsc-msystem-blade

Preferred model: Claude Haiku 4.5 (enable for all clients).

Purpose
- Help maintainers and automation agents make small, focused code changes in this Laravel 12 application.

Architecture (big picture)
- Laravel backend (PHP 8.2) with a thin-controller / service-layer pattern:
  - Controllers live in `app/Http/Controllers/*` and delegate business logic to `app/Services/*`.
  - Examples: `app/Services/MemberService.php`, `app/Services/AttendanceService.php`.
  - `app/Models/*` are plain Eloquent models (e.g. `Member.php`, `Membership.php`, `Attendance.php`).
- Blade views and components in `resources/views/` (UI pieces are componentized; e.g. `resources/views/components/navigation/navbar.blade.php`).

Key workflows & exact commands
- Local dev (frontend + backend):
  - Install deps: `composer install` and `npm install`.
  - Run dev: `composer run dev` (this uses `npx concurrently` to start `php artisan serve` + `npm run dev`).
  - Frontend only: `npm run dev` (Vite).
- Database: migrations and factories are under `database/migrations` and `database/factories`.
  - Run migrations: `php artisan migrate`.
  - Seeders: `php artisan db:seed` (exists when needed).
- Tests: `composer test` (runs `@php artisan test`) — PHPUnit config at `phpunit.xml` and tests live in `tests/`.

Project-specific conventions
- Business logic lives in `app/Services/*`. Controllers should remain thin — make changes in services when adding behavior.
- Services may call each other (e.g., `MemberService` uses `MembershipService`)—preserve existing call structure when refactoring.
- Use constructor dependency injection (readonly typed properties) for services in controllers (see `app/Http/Controllers/Main/AttendanceController.php`).
- Enums are in `app/Enums` (e.g., `MemberStatus.php`, `UserRole.php`) — prefer enums over raw strings for statuses/roles.

Integration points & external deps
- Redis (via `predis/predis`) may be used; queue/redis settings in `config/queue.php` and `.env`.
- Telescope is included (`laravel/telescope`) and has a service provider in `app/Providers/TelescopeServiceProvider.php` — don’t disable it in production changes without explicit reason.

Editing guidelines for AI agents
- Make minimal, focused edits. Prefer changing or adding a single file per pull request unless a cross-cutting fix requires more.
- Run or suggest the exact commands above to validate changes (migrations, `composer test`, `npm run dev`).
- When modifying templates, reference the component path; for example, to change the navbar, edit `resources/views/components/navigation/navbar.blade.php`.
- When adding behavior, update or add unit/feature tests under `tests/` and run `composer test`.

Examples (in-repo pointers)
- Business logic change: edit `app/Services/MemberService.php` and update `app/Http/Controllers/Main/MemberController.php` routes in `routes/web.php` as needed.
- New background job: add file under `app/Jobs/`, dispatch from a service, and ensure queue config (see `config/queue.php`).

Security & safety
- Do not commit secrets — check `.env.example` for expected env keys. If a change requires new secrets, document required keys and do not add them to the repo.

If unsure
- When uncertain about the impact of a change, open a draft PR and add `WIP` to the title; include a short summary of the risk and manual steps to validate.

Questions / Feedback
- If this guidance misses a local workflow or tool, please update this file and ping maintainers.
