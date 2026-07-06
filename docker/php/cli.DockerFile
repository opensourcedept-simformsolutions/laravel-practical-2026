FROM serversideup/php:8.4-cli-alpine

WORKDIR /var/www/html

USER root

RUN apk add --no-cache mysql-client \
    && install-php-extensions gd \
    && mkdir -p /etc/my.cnf.d \
    && echo -e "[client]\nssl=0" > /etc/my.cnf.d/ssl.cnf

USER www-data

COPY --chown=www-data:www-data . /var/www/html
