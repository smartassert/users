FROM composer:2 AS composer_build
WORKDIR /app
COPY composer.json .
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist
COPY . .
RUN composer dump-autoload --optimize --classmap-authoritative

FROM php:8.5-fpm-bookworm

WORKDIR /app
COPY --from=composer_build /app /app

ARG APP_ENV=prod
ARG DATABASE_URL=postgresql://database_user:database_password@0.0.0.0:5432/database_name?serverVersion=12&charset=utf8
ARG JWT_SECRET_KEY="%kernel.project_dir%/config/jwt/private.pem"
ARG JWT_PUBLIC_KEY="%kernel.project_dir%/config/jwt/public.pem"
ARG JWT_PASSPHRASE=jwt_passphrase
ARG JWT_TOKEN_TTL=3600
ARG PRIMARY_ADMIN_TOKEN=primary-admin-token
ARG SECONDARY_ADMIN_TOKEN=secondary-admin-token
ARG IS_READY=0

ENV APP_ENV=$APP_ENV
ENV DATABASE_URL=$DATABASE_URL
ENV JWT_SECRET_KEY=$JWT_SECRET_KEY
ENV JWT_PUBLIC_KEY=$JWT_PUBLIC_KEY
ENV JWT_PASSPHRASE=$JWT_PASSPHRASE
ENV JWT_TOKEN_TTL=$JWT_TOKEN_TTL
ENV PRIMARY_ADMIN_TOKEN=$PRIMARY_ADMIN_TOKEN
ENV SECONDARY_ADMIN_TOKEN=$SECONDARY_ADMIN_TOKEN
ENV IS_READY=$READY

RUN apt-get -qq update && apt-get -qq -y install  \
  git \
  libicu-dev \
  libpq-dev \
  libzip-dev \
  zip \
  && docker-php-ext-install \
  intl \
  pdo_pgsql \
  zip \
  && apt-get autoremove -y \
  && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN mkdir -p /app/var/log \
  && chown -R www-data:www-data /app/var/log \
  && echo "APP_SECRET=$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 32 | head -n 1)" > .env \
  && php bin/console cache:clear
