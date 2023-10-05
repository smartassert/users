FROM php:8.2-fpm-buster

WORKDIR /app

ARG APP_ENV=prod
ARG DATABASE_URL=postgresql://database_user:database_password@0.0.0.0:5432/database_name?serverVersion=12&charset=utf8
ARG JWT_SECRET_KEY="%kernel.project_dir%/config/jwt/private.pem"
ARG JWT_PUBLIC_KEY="%kernel.project_dir%/config/jwt/public.pem"
ARG JWT_PASSPHRASE=jwt_passphrase
ARG PRIMARY_ADMIN_TOKEN=primary-admin-token
ARG SECONDARY_ADMIN_TOKEN=secondary-admin-token
ARG IS_READY=0

ENV APP_ENV=$APP_ENV
ENV DATABASE_URL=$DATABASE_URL
ENV JWT_SECRET_KEY=$JWT_SECRET_KEY
ENV JWT_PUBLIC_KEY=$JWT_PUBLIC_KEY
ENV JWT_PASSPHRASE=$JWT_PASSPHRASE
ENV PRIMARY_ADMIN_TOKEN=$PRIMARY_ADMIN_TOKEN
ENV SECONDARY_ADMIN_TOKEN=$SECONDARY_ADMIN_TOKEN
ENV IS_READY=$READY

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

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

COPY composer.json /app/
COPY bin/console /app/bin/console
COPY public/index.php public/
COPY src /app/src
COPY config/bundles.php config/services.yaml /app/config/
COPY config/packages/*.yaml /app/config/packages/
COPY config/packages/prod /app/config/packages/prod
COPY config/routes.yaml /app/config/
COPY migrations /app/migrations

RUN mkdir -p /app/var/log \
  && chown -R www-data:www-data /app/var/log \
  && echo "APP_SECRET=$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 32 | head -n 1)" > .env \
  && composer install --no-dev --no-scripts \
  && rm composer.lock \
  && php bin/console cache:clear
