FROM serversideup/php:8.4-fpm-nginx-alpine

WORKDIR /var/www/html

USER root

# Install PHP extensions (including redis for Reverb scaling)
RUN install-php-extensions gd redis

# Copy composer files and install dependencies
COPY --chown=www-data:www-data composer.* /var/www/html
RUN su www-data -s /bin/sh -c "composer install --no-interaction --prefer-dist"

# Copy application code
COPY --chown=www-data:www-data . /var/www/html

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Generate API docs (commented out as this command does not exist in our app)
# RUN su www-data -s /bin/sh -c "php artisan app:docs"

COPY --chown=root:root docker/nginx/reverb-proxy.conf /etc/nginx/server-opts.d/reverb-proxy.conf

# Copy Reverb s6 service definitions
COPY --chmod=755 docker/s6-overlay/reverb/ /etc/s6-overlay/s6-rc.d/reverb/

# Link reverb service to user bundle
RUN mkdir -p /etc/s6-overlay/s6-rc.d/user/contents.d && \
    touch /etc/s6-overlay/s6-rc.d/user/contents.d/reverb

EXPOSE 8080 8443 9001

USER www-data
