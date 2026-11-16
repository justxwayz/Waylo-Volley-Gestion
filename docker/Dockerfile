FROM php:8.3-apache

RUN docker-php-ext-install pdo_mysql

ENV TZ=Europe/Paris
RUN printf "[Date]\ndate.timezone = Europe/Paris\n" > /usr/local/etc/php/conf.d/timezone.ini

# Le point d'entrée web est public/ ; le reste du code (Shared, Features) n'est pas exposé
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
