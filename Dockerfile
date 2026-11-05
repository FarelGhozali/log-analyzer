FROM docker.io/library/composer:2.8 AS composer-dependencies

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --prefer-dist \
    --ignore-platform-req=ext-intl \
    --optimize-autoloader

FROM docker.io/library/node:22-alpine AS frontend

WORKDIR /var/www/html

COPY package.json package-lock.json ./
RUN npm ci --ignore-scripts

COPY --from=composer-dependencies /var/www/html/vendor ./vendor
COPY resources ./resources
COPY public ./public
COPY vite.config.js ./
RUN npm run build

FROM docker.io/library/php:8.4-fpm-alpine AS application

RUN apk add --no-cache \
        icu-libs \
        libpng \
        libzip \
        sqlite-libs \
        nginx \
    && apk add --no-cache --virtual .build-dependencies \
        $PHPIZE_DEPS \
        icu-dev \
        libpng-dev \
        libzip-dev \
        oniguruma-dev \
        sqlite-dev \
    && docker-php-ext-install \
        bcmath \
        intl \
        mbstring \
        opcache \
        pdo_sqlite \
    && apk del .build-dependencies \
    && rm -f /etc/nginx/http.d/default.conf

WORKDIR /var/www/html

COPY --from=composer-dependencies /var/www/html/vendor ./vendor
COPY . .
COPY --from=frontend /var/www/html/public/build ./public/build
COPY entrypoint.sh /usr/local/bin/entrypoint

RUN addgroup -g 1000 -S app \
    && adduser -u 1000 -S -D -G app app \
    && mkdir -p storage bootstrap/cache \
    && chown -R app:app public storage bootstrap/cache \
    && chmod +x /usr/local/bin/entrypoint \
    && printf '%s\n' \
        'server {' \
        '    listen 8080;' \
        '    server_name _;' \
        '    root /var/www/html/public;' \
        '    index index.php;' \
        '    client_max_body_size 10m;' \
        '    location / {' \
        '        try_files $uri $uri/ /index.php?$query_string;' \
        '    }' \
        '    location ~ \.php$ {' \
        '        try_files $uri =404;' \
        '        include fastcgi_params;' \
        '        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;' \
        '        fastcgi_param DOCUMENT_ROOT $document_root;' \
        '        fastcgi_pass app:9000;' \
        '    }' \
        '    location ~ /\.(?!well-known).* {' \
        '        deny all;' \
        '    }' \
        '}' > /etc/nginx/http.d/default.conf \
    && sed -i 's#^pid .*#pid /tmp/nginx.pid;#' /etc/nginx/nginx.conf \
    && mkdir -p /var/lib/nginx/logs /var/lib/nginx/tmp /run/nginx /var/log/nginx \
    && chown -R app:app /var/lib/nginx /var/log/nginx /run/nginx /tmp \
    && sed -i 's/^user = .*/user = app/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/^group = .*/group = app/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/^listen.owner = .*/;listen.owner = app/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/^listen.group = .*/;listen.group = app/' /usr/local/etc/php-fpm.d/www.conf

USER app

ENTRYPOINT ["/usr/local/bin/entrypoint"]
CMD ["php-fpm", "-F"]
