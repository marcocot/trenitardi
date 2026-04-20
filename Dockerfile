# syntax = docker/dockerfile:1.6

ARG PHP_VERSION=8.4
ARG NODE_VERSION=22

FROM ubuntu:22.04 AS base

ARG PHP_VERSION
ENV DEBIAN_FRONTEND=noninteractive \
    COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/composer \
    COMPOSER_MAX_PARALLEL_HTTP=24 \
    PHP_PM_MAX_CHILDREN=10 \
    PHP_PM_START_SERVERS=3 \
    PHP_MIN_SPARE_SERVERS=2 \
    PHP_MAX_SPARE_SERVERS=4 \
    PHP_DATE_TIMEZONE=UTC \
    PHP_DISPLAY_ERRORS=Off \
    PHP_ERROR_REPORTING=22527 \
    PHP_MEMORY_LIMIT=256M \
    PHP_MAX_EXECUTION_TIME=90 \
    PHP_POST_MAX_SIZE=100M \
    PHP_UPLOAD_MAX_FILE_SIZE=100M \
    PHP_ALLOW_URL_FOPEN=Off

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/php/ondrej_ubuntu_php.gpg /etc/apt/trusted.gpg.d/ondrej_ubuntu_php.gpg
ADD docker/php/packages/${PHP_VERSION}.txt /tmp/php-packages.txt

RUN apt-get update \
    && apt-get install -y --no-install-recommends gnupg2 ca-certificates git-core curl zip unzip \
                                                  rsync vim-tiny htop sqlite3 nginx supervisor cron \
    && ln -sf /usr/bin/vim.tiny /etc/alternatives/vim \
    && ln -sf /etc/alternatives/vim /usr/bin/vim \
    && echo "deb http://ppa.launchpad.net/ondrej/php/ubuntu jammy main" > /etc/apt/sources.list.d/ondrej-ubuntu-php-focal.list \
    && apt-get update \
    && apt-get -y --no-install-recommends install $(cat /tmp/php-packages.txt) \
    && ln -sf /usr/sbin/php-fpm${PHP_VERSION} /usr/sbin/php-fpm \
    && mkdir -p /var/www/html/public && echo "index" > /var/www/html/public/index.php \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

COPY docker/nginx/ /etc/nginx/
COPY docker/fpm/ /etc/php/${PHP_VERSION}/fpm/
COPY docker/supervisor/ /etc/supervisor/
COPY docker/entrypoint.sh /entrypoint
COPY docker/start-nginx.sh /usr/local/bin/start-nginx
RUN chmod +x /entrypoint /usr/local/bin/start-nginx

COPY . /var/www/html
WORKDIR /var/www/html

RUN composer install --optimize-autoloader --no-dev \
    && mkdir -p storage/logs \
    && CACHE_STORE=file php artisan optimize:clear \
    && chown -R www-data:www-data /var/www/html \
    && printf 'MAILTO=""\n* * * * * www-data /usr/bin/php /var/www/html/artisan schedule:run\n' > /etc/cron.d/laravel


FROM node:${NODE_VERSION} AS assets

WORKDIR /app
COPY . .
COPY --from=base /var/www/html/vendor /app/vendor

RUN if [ -f "package-lock.json" ]; then \
        npm ci --no-audit && npm run build; \
    elif [ -f "yarn.lock" ]; then \
        yarn install --frozen-lockfile && yarn build; \
    elif [ -f "pnpm-lock.yaml" ]; then \
        corepack enable && corepack prepare pnpm@latest-8 --activate \
        && pnpm install --frozen-lockfile && pnpm run build; \
    else \
        npm install && npm run build; \
    fi


FROM base

COPY --from=assets /app/public /var/www/html/public-npm
RUN rsync -ar /var/www/html/public-npm/ /var/www/html/public/ \
    && rm -rf /var/www/html/public-npm \
    && chown -R www-data:www-data /var/www/html

EXPOSE 8080

ENTRYPOINT ["/entrypoint"]
