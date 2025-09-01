############################################
# Base Image
############################################

FROM serversideup/php:8.4-fpm-nginx-alpine AS base

USER root
RUN install-php-extensions intl

# Todo: Other extensions

############################################
# Production Build Image
############################################

FROM base AS build

# Install build dependencies (node, npm, composer)

RUN apk update && apk upgrade && apk add --no-cache nodejs npm

WORKDIR /var/www/html

COPY --chown=www-data:www-data .. .

# Ensure the cache directory is writable

RUN mkdir -p ./bootstrap/cache && chmod -R 775 ./bootstrap/cache

# Ensure the sqlite database directory is writable

RUN mkdir -p ./database/sqlite && chmod -R 775 ./database/sqlite

# Install PHP dependencies

RUN composer install --no-dev --optimize-autoloader --prefer-dist

# Build assets

RUN npm ci && npm run build

############################################
# Production Image
############################################

# Production Stage
FROM base AS production

WORKDIR /var/www/html

# Copy project files and built assets from the build stage

COPY --chown=www-data:www-data --from=build /var/www/html .

# Always switch back to an unprivilidged user

USER www-data
