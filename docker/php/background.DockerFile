FROM serversideup/php:8.4-cli-alpine

# Install s6-overlay (cli image does NOT include it by default, but serversideup entrypoint expects /init)
ARG S6_OVERLAY_VERSION=3.1.6.2
USER root
RUN apk add --no-cache curl tar xz \
    && curl -L https://github.com/just-containers/s6-overlay/releases/download/v${S6_OVERLAY_VERSION}/s6-overlay-noarch.tar.xz | tar -C / -Jxpf - \
    && curl -L https://github.com/just-containers/s6-overlay/releases/download/v${S6_OVERLAY_VERSION}/s6-overlay-x86_64.tar.xz | tar -C / -Jxpf -

WORKDIR /var/www/html

# Install PHP extensions (requires root)
RUN install-php-extensions gd redis

# Copy s6 service definitions for cron and queue workers (requires root)
COPY --chmod=755 docker/s6-overlay/cron/ /etc/s6-overlay/s6-rc.d/cron/
COPY --chmod=755 docker/s6-overlay/queue-default-1/ /etc/s6-overlay/s6-rc.d/queue-default-1/
COPY --chmod=755 docker/s6-overlay/queue-default-2/ /etc/s6-overlay/s6-rc.d/queue-default-2/

# Link services to user bundle so they start automatically (requires root)
RUN mkdir -p /etc/s6-overlay/s6-rc.d/user/contents.d && \
    touch /etc/s6-overlay/s6-rc.d/user/contents.d/cron && \
#    touch /etc/s6-overlay/s6-rc.d/user/contents.d/queue-email && \
    touch /etc/s6-overlay/s6-rc.d/user/contents.d/queue-default-1 && \
    touch /etc/s6-overlay/s6-rc.d/user/contents.d/queue-default-2

# Switch to www-data user for application files
USER www-data

# Copy composer files and install dependencies
COPY --chown=www-data:www-data composer.* /var/www/html/
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Copy application code
COPY --chown=www-data:www-data . /var/www/html

# s6-overlay provides /init now; serversideup entrypoint will exec /init
ENTRYPOINT ["/usr/local/bin/docker-php-serversideup-entrypoint"]
CMD ["/init"]
