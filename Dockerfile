FROM php:8.2-apache

# Habilitar mod_rewrite y extensiones necesarias
RUN docker-php-ext-install pdo pdo_mysql

# Instalar SQLite3 para compatibilidad cloud
RUN apt-get update && apt-get install -y libsqlite3-dev \
    && docker-php-ext-install pdo_sqlite

# Copiar proyecto
COPY . /var/www/html/

# Ajustar permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html

EXPOSE 80
CMD ["apache2-foreground"]
