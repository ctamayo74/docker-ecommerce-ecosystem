# syntax=docker/dockerfile:1.7
ARG PHP_VERSION=8.2

# ---------- 1) vendor: Composer solo existe en esta etapa ----------
FROM composer:2 AS vendor
ENV COMPOSER_ALLOW_SUPERUSER=1
WORKDIR /app
# Capa cacheada: solo se invalida si cambian composer.json/lock
COPY composer.json composer.lock ./
RUN --mount=type=cache,target=/tmp/cache \
    composer install --no-dev --no-interaction --no-progress --prefer-dist \
      --no-scripts --no-autoloader --ignore-platform-reqs
# El autoloader necesita el código para generar el classmap optimizado
COPY . .
RUN composer dump-autoload --no-dev --optimize --classmap-authoritative --no-scripts

# ---------- 2) runtime: PHP-FPM + Nginx ----------
FROM php:${PHP_VERSION}-fpm-alpine AS runtime

# mbstring, openssl, tokenizer, xml, ctype y json ya vienen compilados en la imagen oficial.
# Solo falta pdo_mysql (+ opcache para rendimiento). Verifica con: docker run --rm <img> php -m
RUN apk add --no-cache nginx supervisor su-exec \
 && docker-php-ext-install -j"$(nproc)" pdo_mysql opcache \
 && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY docker/php/opcache.ini   $PHP_INI_DIR/conf.d/zz-opcache.ini
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf   /etc/supervisord.conf
COPY docker/entrypoint.sh      /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

WORKDIR /var/www/html

# vendor/ viene de la etapa de build (no hay Composer en la imagen final)
COPY --from=vendor --chown=www-data:www-data /app/vendor ./vendor
# Código de la app (el .dockerignore excluye vendor, .env, tests, etc.)
COPY --chown=www-data:www-data . .

RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views \
             storage/logs bootstrap/cache \
 && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80
HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=3 \
  CMD wget -qO- http://127.0.0.1/up >/dev/null 2>&1 || exit 1

ENTRYPOINT ["entrypoint"]
CMD ["supervisord", "-c", "/etc/supervisord.conf"]
