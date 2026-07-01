#!/bin/sh
set -e

php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Waiting for MySQL at ${DB_HOST}:${DB_PORT}..."
until php -r '
    $host = getenv("DB_HOST");
    $port = getenv("DB_PORT") ?: "3306";
    $user = getenv("DB_USERNAME");
    $pass = getenv("DB_PASSWORD");
    try {
        new PDO("mysql:host={$host};port={$port}", $user, $pass);
    } catch (Throwable $e) {
        exit(1);
    }
' 2>/dev/null; do
    echo "MySQL not ready, retrying in 3s..."
    sleep 3
done

php artisan migrate --force

exec php artisan octane:frankenphp \
    --host=0.0.0.0 \
    --port=8000 \
    --workers="${OCTANE_WORKERS:-auto}" \
    --max-requests="${OCTANE_MAX_REQUESTS:-500}"
