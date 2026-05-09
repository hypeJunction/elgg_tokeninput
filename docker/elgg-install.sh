#!/bin/bash
set -e

# Per-plugin Elgg 6.x install + activation script.
# PLUGIN_ID must be set in the container environment (passed by docker-compose
# from <plugin>/docker/.env). Only that one plugin is activated — no fleet
# activation, no plugin-order.txt, no cross-plugin side effects.

if [ -z "${PLUGIN_ID:-}" ]; then
    echo "ERROR: PLUGIN_ID environment variable is required." >&2
    echo "Set it in docker/.env before starting the stack." >&2
    exit 1
fi

echo "Waiting for MySQL..."
until php -r "new PDO('mysql:host=${ELGG_DB_HOST:-db}', '${ELGG_DB_USER:-elgg}', '${ELGG_DB_PASS:-elgg}');" 2>/dev/null; do
    sleep 1
done
echo "MySQL is ready."

cd /var/www/html

# Elgg core ships bundled plugins under vendor/elgg/elgg/mod/. They need to
# be reachable via /var/www/html/mod/ for the plugin loader to find them.
# Symlink any core plugin that isn't already bind-mounted by the per-plugin
# compose stack — the bind mount always wins.
if [ -d /var/www/html/vendor/elgg/elgg/mod ]; then
    for core_plugin_dir in /var/www/html/vendor/elgg/elgg/mod/*/; do
        core_plugin_id=$(basename "${core_plugin_dir}")
        if [ ! -e "/var/www/html/mod/${core_plugin_id}" ]; then
            ln -s "${core_plugin_dir%/}" "/var/www/html/mod/${core_plugin_id}"
        fi
    done
fi

if [ ! -f /var/www/html/.elgg-installed ]; then
    echo "Installing Elgg 6.x..."

    mkdir -p elgg-config
    cat > elgg-config/settings.php <<'SETTINGS_TEMPLATE'
<?php
global $CONFIG;
if (!isset($CONFIG)) {
    $CONFIG = new \stdClass;
}
SETTINGS_TEMPLATE

    cat >> elgg-config/settings.php <<SETTINGS_VALUES
\$CONFIG->dbuser = '${ELGG_DB_USER:-elgg}';
\$CONFIG->dbpass = '${ELGG_DB_PASS:-elgg}';
\$CONFIG->dbname = '${ELGG_DB_NAME:-elgg}';
\$CONFIG->dbhost = '${ELGG_DB_HOST:-db}';
\$CONFIG->dbport = '3306';
\$CONFIG->dbprefix = 'elgg_';
\$CONFIG->dbencoding = 'utf8mb4';
\$CONFIG->dataroot = '${ELGG_DATA_ROOT:-/var/www/data/}';
\$CONFIG->wwwroot = '${ELGG_SITE_URL:-http://localhost:8480/}';
\$CONFIG->cacheroot = '${ELGG_DATA_ROOT:-/var/www/data/}cache/';
\$CONFIG->assetroot = '${ELGG_DATA_ROOT:-/var/www/data/}assets/';
SETTINGS_VALUES

    php -r "
        require_once 'vendor/autoload.php';

        \$params = [
            'dbuser' => '${ELGG_DB_USER:-elgg}',
            'dbpassword' => '${ELGG_DB_PASS:-elgg}',
            'dbname' => '${ELGG_DB_NAME:-elgg}',
            'dbhost' => '${ELGG_DB_HOST:-db}',
            'dbport' => '3306',
            'dbprefix' => 'elgg_',
            'sitename' => 'Elgg 4.x Plugin Test',
            'siteemail' => '${ELGG_ADMIN_EMAIL:-admin@example.com}',
            'wwwroot' => '${ELGG_SITE_URL:-http://localhost:8480/}',
            'dataroot' => '${ELGG_DATA_ROOT:-/var/www/data/}',
            'displayname' => 'Admin',
            'email' => '${ELGG_ADMIN_EMAIL:-admin@example.com}',
            'username' => 'admin',
            'password' => '${ELGG_ADMIN_PASSWORD:-admin12345}',
        ];

        \$installer = new \ElggInstaller();
        \$installer->batchInstall(\$params);
        echo 'Elgg 6.x installed successfully.' . PHP_EOL;
    " 2>&1 || echo "Install completed (check for errors above)."

    echo "Activating plugins..."
    php -r "
        require_once 'vendor/autoload.php';
        \$app = \Elgg\Application::getInstance();
        \$app->bootCore();
        _elgg_services()->plugins->generateEntities();

        // Resolve dep plugin IDs from the plugin's own metadata.
        // Priority: elgg-plugin.php 'plugin.dependencies' (Elgg 6.x).
        // IDs are lowercased to match mod/ directory names.
        // Deps not present in mod/ are skipped with a warning — this naturally excludes
        // deps that are unsafe to activate (e.g. unmigrated plugins not volume-mounted).
        \$dep_ids = [];
        \$plugin_file = '/var/www/html/mod/${PLUGIN_ID}/elgg-plugin.php';
        if (file_exists(\$plugin_file)) {
            \$manifest = include \$plugin_file;
            foreach (array_keys(\$manifest['plugin']['dependencies'] ?? []) as \$id) {
                \$dep_ids[] = strtolower(\$id);
            }
        }
        foreach (\$dep_ids as \$dep_id) {
            \$dep = elgg_get_plugin_from_id(\$dep_id);
            if (!\$dep) {
                echo 'WARNING: dep plugin ' . \$dep_id . ' not in mod/ — skipping (not mounted).' . PHP_EOL;
                continue;
            }
            if (\$dep->isActive()) {
                echo 'Dep plugin ' . \$dep_id . ' already active.' . PHP_EOL;
                continue;
            }
            try {
                \$dep->activate();
                echo 'Dep plugin ' . \$dep_id . ' activated.' . PHP_EOL;
            } catch (\Throwable \$e) {
                echo 'FAILED to activate dep ' . \$dep_id . ': ' . \$e->getMessage() . PHP_EOL;
                exit(1);
            }
        }

        // Activate the main plugin.
        \$plugin = elgg_get_plugin_from_id('${PLUGIN_ID}');
        if (!\$plugin) {
            echo 'ERROR: plugin ${PLUGIN_ID} not found at /var/www/html/mod/${PLUGIN_ID}' . PHP_EOL;
            exit(1);
        }
        // Move to end of load order so every dep is positioned before it.
        \$plugin->setPriority('last');
        if (\$plugin->isActive()) {
            echo 'Plugin ${PLUGIN_ID} already active.' . PHP_EOL;
        } else {
            try {
                \$plugin->activate();
                echo 'Plugin ${PLUGIN_ID} activated.' . PHP_EOL;
            } catch (\Throwable \$e) {
                echo 'FAILED to activate ${PLUGIN_ID}: ' . \$e->getMessage() . PHP_EOL;
                exit(1);
            }
        }
    " 2>&1 || echo "Plugin activation completed (check for errors above)."

    # Hand the data root over to the Apache user. The installer ran as
    # root (entrypoint context) and left every cache subdirectory
    # root-owned, which makes Phpfastcache throw IOException on the
    # first request and the site renders Elgg's "fatal error" stub.
    chown -R www-data:www-data "${ELGG_DATA_ROOT:-/var/www/data/}"
    chmod -R u+rwX,g+rX,o+rX "${ELGG_DATA_ROOT:-/var/www/data/}"

    touch /var/www/html/.elgg-installed
    echo "Elgg 6.x setup complete."
fi

echo "Starting Apache..."
exec apache2-foreground
