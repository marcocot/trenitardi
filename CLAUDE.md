<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4.12
- laravel/framework (LARAVEL) - v12
- livewire/livewire (LIVEWIRE) - v4
- laravel/boost (BOOST) - v2
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- phpunit/phpunit (PHPUNIT) - v11
- tailwindcss (TAILWINDCSS) - v4
- webmozart/assert - v2

## Application Overview

**TrainTrack** — A mobile-first web app to monitor Italian train status in real time via the ViaggiaTreno public API.

**No database is used.** All data comes from external APIs. Favorites are stored client-side in `localStorage` via Alpine.js.

### Architecture

- `app/Services/TrainService.php` — proxies the ViaggiaTreno API (two methods: `searchTrain` returns `TrainSearchResultDto`, `getRawTrainStatus` returns raw array, `getTrainStatus` returns `TrainStatusDto`)
- `app/DTOs/` — readonly PHP classes with English property names and `webmozart/assert` validation in constructors; `fromArray()` reads Italian API keys
- `resources/views/components/⚡train-monitor.blade.php` — Livewire v4 SFC (single-file component); stores raw API response as `?array $trainStatusData`; exposes `#[Computed] trainStatus(): ?TrainStatusDto`
- `resources/views/layouts/app.blade.php` — base layout

### Key Patterns

- Livewire SFC components live in `resources/views/components/` with the `⚡` prefix
- Livewire state must be scalar/array types only — use `#[Computed]` to expose DTOs from stored arrays
- Alpine.js `trainFavorites()` function handles `localStorage` for favorites (key: `train_favorites`)
- Routes: `GET /` and `GET /status/{trainNumber}` (deeplink) both render the `train-monitor` component

### ViaggiaTreno API Flow

1. `GET /cercaNumeroTrenoTrenoAutocomplete/{trainNumber}` → plain text: `"9642 - ORIGIN - date|9642-S11781-timestamp"`
2. Parse to extract `trainId`, `trainNumber`, `timestamp`
3. `GET /andamentoTreno/{trainId}/{trainNumber}/{timestamp}` → JSON with train status

## Skills Activation

- `livewire-development` — Activate for any Livewire component work
- `tailwindcss-development` — Activate for any styling work

## Conventions

- Follow all existing code conventions. Check sibling files before creating new ones.
- Use descriptive names for variables and methods.
- Check for existing components to reuse before writing a new one.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.
- No Eloquent models or database migrations needed — this app has no database.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality.

## Frontend Bundling

- If the user doesn't see a frontend change, they may need to run `npm run build`, `npm run dev`, or `composer run dev`.

## Documentation Files

- Only create documentation files if explicitly requested.

## Replies

- Be concise — focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan

- Use the `list-artisan-commands` tool when you need to call an Artisan command to double-check the available parameters.

## URLs

- Whenever you share a project URL with the user, you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain/IP, and port.

## Tinker / Debugging

- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.

## Reading Browser Logs With the `browser-logs` Tool

- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)

- Boost comes with a powerful `search-docs` tool you should use before trying other approaches when working with Laravel or Laravel ecosystem packages.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic-based queries at once.
- Do not add package names to queries.

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.

## Constructors

- Use PHP 8 constructor property promotion in `__construct()`.
- Do not allow empty `__construct()` methods with zero parameters unless the constructor is private.

## Type Declarations

- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

## Enums

- Keys in an Enum should be TitleCase.

## Comments

- Prefer PHPDoc blocks over inline comments. Never use comments within the code itself unless the logic is exceptionally complex.

## PHPDoc Blocks

- Add useful array shape type definitions when appropriate.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files. You can list available Artisan commands using the `list-artisan-commands` tool.
- Pass `--no-interaction` to all Artisan commands.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Configuration

- Use environment variables only in configuration files — never use `env()` directly outside of config files.

## Vite Error

- If you receive a ViteException, run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v12 rules ===

# Laravel 12

- CRITICAL: ALWAYS use `search-docs` tool for version-specific Laravel documentation.
- Since Laravel 11, Laravel has a streamlined file structure which this project uses.

## Laravel 12 Structure

- Middleware are configured in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/providers.php` contains application-specific service providers.
- Console commands in `app/Console/Commands/` are auto-registered.

=== livewire/core rules ===

# Livewire

- Livewire v4 uses single-file components (SFC) by default — PHP class and Blade template in one `.blade.php` file.
- SFC components live in `resources/views/components/` with the `⚡` prefix.
- Livewire component properties must be scalar or array types. Use `#[Computed]` to expose DTOs from stored arrays.
- Use Alpine.js for client-side interactions (e.g., localStorage).
- IMPORTANT: Activate `livewire-development` every time you're working with Livewire-related tasks.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes.
- Do not run `--test`; simply run to fix issues.

=== phpunit/core rules ===

# PHPUnit

- This application uses PHPUnit. Use `php artisan make:test --phpunit {name}` to create tests.
- Run the minimal number of tests using an appropriate filter.
- To run all tests: `php artisan test --compact`.

=== tailwindcss/core rules ===

# Tailwind CSS

- Always use existing Tailwind conventions; check project patterns before adding new ones.
- IMPORTANT: Always use `search-docs` tool for version-specific Tailwind CSS documentation.
- IMPORTANT: Activate `tailwindcss-development` every time you're working with a Tailwind CSS or styling-related task.

</laravel-boost-guidelines>
