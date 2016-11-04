FROM daavar/core

# Maintainer Pooya Parsa <Pooya Parsa>

EXPOSE 80

# Supedvisord
COPY conf/supervisord.conf /etc/supervisord.conf

# PHP-FPM
RUN mkdir -p /run/php
COPY conf/www.conf /etc/php/7.0/fpm/pool.d/www.conf
COPY conf/php.ini /etc/php/7.0/fpm/php.ini

# Nginx
COPY conf/nginx.conf /etc/nginx/nginx.conf
COPY conf/default.conf /etc/nginx/conf.d/default.conf

COPY entrypoint /
