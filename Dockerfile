FROM php:8.2-apache

RUN docker-php-ext-install pdo_sqlite sqlite3

RUN a2enmod rewrite

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html/forum && chmod 755 /var/www/html/forum
