FROM php:8.1-apache

# Instalar extensiones necesarias
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copiar el contenido del proyecto al servidor web
COPY . /var/www/html/

# Dar permisos
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
