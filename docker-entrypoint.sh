#!/bin/bash
set -e

APP_DIR="/var/www/html"

# Ensure var directory is writable
mkdir -p "${APP_DIR}/var/cache" "${APP_DIR}/var/log" "${APP_DIR}/var/tailwind"
chmod -R 777 "${APP_DIR}/var"

echo "==> Waiting for database..."
until php -r "
try {
    new PDO('mysql:host=db;dbname=selftrace;charset=utf8mb4', 'selftrace', 'selftrace');
    exit(0);
} catch (Exception \$e) {
    exit(1);
}
" 2>/dev/null; do
    echo "    Database not ready, retrying in 2s..."
    sleep 2
done
echo "==> Database is ready."

# Run migrations
echo "==> Running database migrations..."
php "${APP_DIR}/bin/console" doctrine:migrations:migrate --no-interaction --allow-no-migration

if [ "${APP_ENV}" = "dev" ]; then
    # Dev: build once then watch for changes in the background
    echo "==> Building Tailwind CSS (dev watch mode)..."
    php "${APP_DIR}/bin/console" tailwind:build --no-interaction
    php "${APP_DIR}/bin/console" tailwind:build --watch &
    echo "==> Tailwind watching for changes."
else
    # Prod: full one-shot build + compile + warmup
    echo "==> Building Tailwind CSS..."
    php "${APP_DIR}/bin/console" tailwind:build --no-interaction

    echo "==> Compiling assets..."
    php "${APP_DIR}/bin/console" asset-map:compile --no-interaction

    echo "==> Warming up cache..."
    php "${APP_DIR}/bin/console" cache:warmup --no-interaction

    chmod -R 777 "${APP_DIR}/var"
fi

echo "==> Application ready. Starting PHP-FPM..."
exec "$@"
