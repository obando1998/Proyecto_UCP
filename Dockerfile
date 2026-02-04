# Usa la imagen oficial de PHP con Apache
FROM php:8.2-apache

# Habilita extensiones necesarias
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Copia todos los archivos del proyecto al contenedor
COPY . /var/www/html/

# Ajusta permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expone el puerto del servidor Apache
EXPOSE 80
