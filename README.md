# Selftrace

A self-reflection tool for people with BPD and identity diffusion.

Built on the idea that there *is* a continuous you underneath every version you've ever been — you just haven't been able to see it yet.

**Features:**
- Identity timeline — map the chapters of your life
- Parts gallery — meet the recurring aspects of yourself
- Daily reflection prompts — one thoughtful question per day
- Patterns dashboard — word frequency, writing heatmap, AI insights
- AI-powered synthesis via Claude (selftrace discovery, parts synthesis, reflection synthesis)

**Open source. Self-hosted. Your data stays on your machine.**

---

## Running locally

**Requirements:** Docker + an [Anthropic API key](https://console.anthropic.com)

```bash
git clone https://github.com/thedayafterapp/selftrace.git
cd selftrace

# Create your config file and add your Anthropic API key
cp .env.example .env
# edit .env and set ANTHROPIC_API_KEY=sk-ant-...

# Start everything
docker compose up --build
```

Visit **http://localhost:6622** — the first build takes a few minutes.

See [SETUP.md](SETUP.md) for full setup instructions, useful commands, and troubleshooting.

---

## Tech stack

PHP 8.3 / Symfony 7 · Twig · Tailwind CSS · Doctrine ORM · MySQL 8 · Claude API · Docker

## License

MIT
