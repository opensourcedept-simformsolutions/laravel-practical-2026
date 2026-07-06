FROM serversideup/php:8.4-cli-alpine

WORKDIR /var/www/html

USER root

RUN install-php-extensions gd

USER www-data

COPY --chown=www-data:www-data . /var/www/html
