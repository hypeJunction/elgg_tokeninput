# Development & Testing

This plugin ships a self-contained Docker stack under `docker/` for running
its PHPUnit, Playwright, and Vitest suites in isolation. Each plugin owns
its own copy of this stack — its own containers, volumes, network, and
host ports — so nothing is shared between plugins. Cloning this repo and
running `docker compose up` is enough to get a working Elgg site with
only this plugin activated.

## Prerequisites

- Docker + Docker Compose v2
- A local checkout of this plugin repository

## One-time setup

```bash
cp docker/.env.example docker/.env
# edit docker/.env and set PLUGIN_ID to this plugin's lowercase id
```

`PLUGIN_ID` must match the plugin's directory name on disk **and** the
`name` field of `composer.json` (Elgg 4+ rejects mismatches).

## Start the stack

```bash
docker compose -f docker/docker-compose.yml up -d
```

First boot installs Elgg into a named volume, mounts this plugin at
`/var/www/html/mod/$PLUGIN_ID`, and activates it. Subsequent boots reuse
the volume and are fast.

Open `http://localhost:${ELGG_PORT}` for the running site (default port
depends on the Elgg major version — see `docker/.env.example`). Default
admin: `admin` / `admin12345`.

## Running tests

### PHPUnit (backend)

```bash
# Install PHPUnit into Elgg's vendor (once per fresh volume)
docker compose -f docker/docker-compose.yml exec elgg \
  composer require --dev phpunit/phpunit:^9.6 --no-interaction

# Run the suite
docker compose -f docker/docker-compose.yml exec elgg \
  vendor/bin/phpunit --configuration mod/$PLUGIN_ID/tests/phpunit.xml --no-coverage
```

Use PHPUnit 9.x on PHP 7.4 (Elgg 3.x/4.x), PHPUnit 10.x on PHP 8.1+ (Elgg 5.x+).

### Playwright (UI + DB)

```bash
docker compose -f docker/docker-compose.yml --profile test run --rm node sh -c \
  "cd /plugin/tests/playwright && npm ci && npx playwright test"
```

The `node` service mounts **only this plugin's source** at `/plugin` and
joins the same network as `elgg` and `db`, so tests talk to `http://elgg`
and `db:3306` directly.

### Vitest (JS unit)

```bash
docker compose -f docker/docker-compose.yml --profile test run --rm node sh -c \
  "cd /plugin && npm ci && npm run test:js"
```

## Debugging

```bash
# Tail Apache/PHP errors
docker compose -f docker/docker-compose.yml exec elgg tail -f /var/log/apache2/error.log

# Interactive shell inside Elgg
docker compose -f docker/docker-compose.yml exec elgg bash

# MySQL shell
docker compose -f docker/docker-compose.yml exec db mysql -uelgg -pelgg elgg

# Rebuild after Dockerfile changes
docker compose -f docker/docker-compose.yml build --no-cache
```

## Tear down

```bash
# Stop containers, keep volumes (fast restart)
docker compose -f docker/docker-compose.yml down

# Stop and wipe the Elgg install + database (clean slate)
docker compose -f docker/docker-compose.yml down -v
```

## Isolation guarantees

- The compose project name is scoped to `${PLUGIN_ID}-elggN`, so the
  containers, volumes, and network created for this plugin never
  collide with another plugin's stack on the same host.
- The only host path mounted into any container is **this plugin's
  source directory**. No sibling plugin is visible from inside Docker,
  so a destructive in-container command cannot touch anything outside
  this repo.
- Apache and MySQL ports are configurable via `docker/.env` if the
  defaults collide with another stack on your host.
