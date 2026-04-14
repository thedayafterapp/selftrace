# Running Selftrace locally

## Prerequisites

- [Docker](https://docs.docker.com/get-docker/) + Docker Compose
- An [Anthropic API key](https://console.anthropic.com) (Claude powers the AI features)

## Setup

**1. Clone the repo**

```bash
git clone https://github.com/thedayafterapp/selftrace.git
cd selftrace
```

**2. Create your config file**

```bash
cp .env.example .env
```

Then open `.env` and set your Anthropic API key:

```
ANTHROPIC_API_KEY=sk-ant-your-key-here
```

**3. Start the app**

```bash
docker compose up --build
```

The first build takes a few minutes. It will:
- Install PHP dependencies (Composer)
- Wait for MySQL to be ready
- Run database migrations + seed 30 daily reflection prompts
- Build Tailwind CSS
- Compile assets and warm the Symfony cache

**4. Open the app**

Visit **http://localhost:8080** and create an account.

---

## Useful commands

```bash
# View logs
docker compose logs -f app

# Rebuild Tailwind CSS after CSS changes
docker compose exec app bin/console tailwind:build

# Reset the database completely
docker compose down -v && docker compose up --build

# Open a shell in the app container
docker compose exec app sh
```

## Development extras (phpMyAdmin + live reloading)

Use `docker-compose.dev.yml` for local development. It adds phpMyAdmin and live source mounts (no rebuild needed for template/PHP changes):

```bash
docker compose -f docker-compose.yml -f docker-compose.dev.yml up --build
```

This file is **not** loaded automatically — production only runs `docker-compose.yml`.

## What's running

| Service    | URL / port                        |
|------------|-----------------------------------|
| App        | http://localhost:8080             |
| MySQL      | localhost:3306 (user: selftrace, pass: selftrace) |
| phpMyAdmin | http://localhost:8081 (dev only — see above) |

## Tech stack

- **PHP 8.3** / Symfony 7
- **Twig** templates
- **Tailwind CSS** via Symfony Asset Mapper
- **Doctrine ORM** + MySQL 8
- **Claude API** (claude-sonnet-4-6) for AI features
- **Docker** (PHP-FPM + Nginx + MySQL)

## AI features

The AI features (Selftrace Discovery, Parts Synthesis, Reflection Synthesis) require a valid `ANTHROPIC_API_KEY`. Without it the app works fine — you just won't be able to generate AI insights.

The app uses `claude-sonnet-4-6` by default, configured in `src/Service/ClaudeService.php`.
