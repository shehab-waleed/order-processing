FROM dunglas/frankenphp:php8.4

RUN install-php-extensions \
    pcntl \
    pdo_mysql \
    opcache \
    zip \
    intl \
    bcmath \
    gd \
    @composer

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --no-interaction

COPY . .

RUN composer dump-autoload --optimize --no-dev --no-interaction

RUN cp docker/entrypoint.sh /usr/local/bin/entrypoint.sh \
    && chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["entrypoint.sh"]
