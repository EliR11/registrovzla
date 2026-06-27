# Usar imagen oficial de PHP con Apache
FROM php:8.2-apache

# Instalar dependencias del sistema para SQLite
RUN apt-get update && apt-get install -y \
    sqlite3 \
    libsqlite3-dev \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensiones PHP (pdo y pdo_sqlite)
RUN docker-php-ext-install pdo
RUN docker-php-ext-install pdo_sqlite

# Habilitar mod_rewrite para URLs amigables
RUN a2enmod rewrite

# Copiar todos los archivos de la aplicación al contenedor
COPY . /var/www/html/

# Crear la base de datos SQLite (si no existe) y dar permisos de escritura
RUN touch /var/www/html/database.sqlite \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod 777 /var/www/html/database.sqlite

# Exponer el puerto 80 para el tráfico web
EXPOSE 80

# Comando para iniciar Apache
CMD ["apache2-foreground"]
