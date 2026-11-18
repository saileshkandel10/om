FROM dunglas/frankenphp:1-php8.4-bookworm AS base
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" && install-php-extensions intl zip pdo_sqlite
WORKDIR /app

FROM base AS dependencies
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --no-scripts --optimize-autoloader --prefer-dist
COPY . .
RUN mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache database \
    && touch database/database.sqlite \
    && composer dump-autoload --no-dev --classmap-authoritative \
    && APP_KEY=base64:8xK9mN2pQ5rT8vW1yZ4bC7eF0hJ3kL6nP9sU2wX5zA8= DB_CONNECTION=sqlite DB_DATABASE=/app/database/database.sqlite php artisan migrate --force \
    && APP_KEY=base64:8xK9mN2pQ5rT8vW1yZ4bC7eF0hJ3kL6nP9sU2wX5zA8= DB_CONNECTION=sqlite DB_DATABASE=/app/database/database.sqlite php artisan db:seed --force

FROM base AS runtime
COPY --from=dependencies --chown=www-data:www-data /app /app
COPY --chown=www-data:www-data Caddyfile /etc/frankenphp/Caddyfile
RUN setcap CAP_NET_BIND_SERVICE=+eip /usr/local/bin/frankenphp && chown -R www-data:www-data /config/caddy /data/caddy /app/storage /app/bootstrap/cache /app/database
ENV PORT=80 \
    APP_NAME="Om Megashree Pharmaceuticals" \
    APP_ENV=production \
    APP_DEBUG=true \
    APP_KEY=base64:8xK9mN2pQ5rT8vW1yZ4bC7eF0hJ3kL6nP9sU2wX5zA8= \
    DB_CONNECTION=sqlite \
    DB_DATABASE=/app/database/database.sqlite \
    LOG_CHANNEL=stderr \
    CACHE_STORE=array \
    SESSION_DRIVER=cookie \
    QUEUE_CONNECTION=sync
USER www-data
CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]
