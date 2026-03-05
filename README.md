# TreniTardi

[![CI](https://github.com/your-org/traintrack/actions/workflows/ci.yml/badge.svg)](https://github.com/your-org/traintrack/actions/workflows/ci.yml)
[![Deploy](https://github.com/your-org/traintrack/actions/workflows/fly-deploy.yml/badge.svg)](https://github.com/your-org/traintrack/actions/workflows/fly-deploy.yml)
[![PHP 8.4](https://img.shields.io/badge/PHP-8.4-777BB4?logo=php&logoColor=white)](https://www.php.net)
[![Laravel 12](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![Livewire 4](https://img.shields.io/badge/Livewire-4-FB70A9?logo=livewire&logoColor=white)](https://livewire.laravel.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

A mobile-first web app to monitor Italian train status in real time, powered by the ViaggiaTreno public API. No accounts, no database — just punch in a train number and get live updates.

## What it does

- Real-time train status via the ViaggiaTreno public API
- Stop-by-stop breakdown with delays and platform info
- Save favorite trains locally in your browser (no login needed)
- Deep-linkable train pages — share a `/status/{trainNumber}` URL directly

## Stack

- **Laravel 12** — application framework
- **Livewire 4** — reactive components (single-file, no build step for PHP)
- **Tailwind CSS 4** — styling
- **Alpine.js** — client-side state (favorites via `localStorage`)
- **ViaggiaTreno API** — the data source

## Getting started

```bash
git clone git@github.com:marcocot/trenitardi.git
cd trenitardi
composer run setup
composer run dev
```

The app will be available at `http://localhost:8000`.

> `composer run setup` handles everything: installing PHP/JS deps, copying `.env`, generating the app key, and building assets.

## Running tests

```bash
php artisan test --compact
```

To check code style without fixing anything:

```bash
composer lint:check
```

To auto-fix style issues:

```bash
composer lint
```

## Architecture notes

All data comes from two ViaggiaTreno API calls:

1. `/cercaNumeroTrenoTrenoAutocomplete/{trainNumber}` — resolves the train ID and origin station
2. `/andamentoTreno/{trainId}/{trainNumber}/{timestamp}` — returns the full status JSON

The `TrainService` handles both calls. DTOs in `app/DTOs/` map the Italian API keys to typed English properties, validated with `webmozart/assert`. The Livewire component stores raw API data as `?array` and exposes typed DTOs via `#[Computed]` properties.

## Deployment

The app deploys automatically to [Fly.io](https://fly.io) on every push to `master` via GitHub Actions. To deploy manually:

```bash
fly deploy
```

## Contributing

PRs are welcome. The CI pipeline (lint + tests) runs automatically on every pull request — make sure both jobs are green before requesting a review.
