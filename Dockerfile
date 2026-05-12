FROM php:8.2-apache

# Installiamo mysqli
RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable mysqli

# Abilitiamo mod_rewrite
RUN a2enmod rewrite

# Configuriamo DocumentRoot per puntare a /var/www/html/public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Permessi corretti
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html