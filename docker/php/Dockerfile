FROM php:8.1-fpm-alpine

RUN apk update \
    && apk add nginx autoconf build-base

RUN set -ex \
  && apk --no-cache add \
    postgresql-dev libexecinfo

RUN pecl install pcov \
    && docker-php-ext-enable pcov

RUN docker-php-ext-install pdo pdo_pgsql pgsql

ENV DATE_TIMEZONE UTC

# Copy PHP content into the container
COPY . /var/www/html/

COPY docker/nginx/app.conf /etc/nginx/http.d/default.conf
COPY docker/php/entrypoint.sh /etc/entrypoint.sh

RUN curl -LO https://github.com/DataDog/dd-trace-php/releases/latest/download/datadog-setup.php

RUN php datadog-setup.php --php-bin=all --enable-appsec --enable-profiling

RUN chmod o+r /tmp/ddapp*

RUN \
    # Forward request and error logs to docker log collector
    ln -sf /dev/stdout /var/log/nginx/access.log \
    && ln -sf /dev/stderr /var/log/nginx/error.log

WORKDIR /var/www/html

COPY composer.json /var/www/html
RUN curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer && \
    composer install

RUN chmod 755 /var/www && \
    chown -R www-data:www-data /var/www/

EXPOSE 80
ENTRYPOINT ["sh", "/etc/entrypoint.sh"]
