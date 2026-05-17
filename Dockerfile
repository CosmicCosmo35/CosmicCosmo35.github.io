FROM php:8.2-apache

RUN apt-get update && apt-get install -y libsqlite3-dev && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_sqlite

RUN a2enmod rewrite

COPY . /var/www/html/

RUN echo '<Directory /var/www/html/>\n    AllowOverride All\n</Directory>' > /etc/apache2/conf-available/allow-override.conf && \
    a2enconf allow-override

RUN mkdir -p /var/www/html/forum/uploads/avatars /var/www/html/forum/uploads/projects && \
    chown -R www-data:www-data /var/www/html/forum && \
    chmod -R 755 /var/www/html/forum && \
    chmod 775 /var/www/html/forum/uploads
