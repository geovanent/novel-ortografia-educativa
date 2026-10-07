# PHP 8.4: validado no ambiente isolado antes da publicação.
FROM php:8.4-apache

RUN docker-php-ext-install mysqli \
 && a2enmod rewrite

COPY docker/apache/novel.conf /etc/apache2/conf-available/novel.conf
RUN a2enconf novel

COPY docker/php/novel.ini /usr/local/etc/php/conf.d/novel.ini
RUN mkdir -p /var/lib/php/sessions \
 && chown www-data:www-data /var/lib/php/sessions

WORKDIR /var/www/html
COPY . /var/www/html

RUN mkdir -p /var/www/html/assets/img/uploads \
 && chown -R www-data:www-data /var/www/html/assets/img/uploads
