# Usar imagen oficial de PHP con Apache
FROM php:8.2-apache

# Instalar extensiones para SQLite (y opcionalmente para MySQL)
RUN docker-php-ext-install pdo pdo_sqlite

# Habilitar mod_rewrite para URLs amigables
RUN a2enmod rewrite

# Copiar todos los archivos de la aplicación al contenedor
COPY . /var/www/html/

# Crear la base de datos SQLite y dar permisos de escritura
RUN touch /var/www/html/database.sqlite \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod 777 /var/www/html/database.sqlite

# Exponer el puerto 80 para el tráfico web
EXPOSE 80
