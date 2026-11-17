FROM php:8.2-apache

# Install ekstensi yang dibutuhkan: SQLite, MySQL, Zip
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    sqlite3 \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_sqlite pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# Aktifkan modul Apache mod_rewrite
RUN a2enmod rewrite headers

# Salin seluruh kode aplikasi ke web root
COPY . /var/www/html/

# Atur kepemilikan dan permission folder data dan uploads
WORKDIR /var/www/html
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/data \
    && chmod -R 775 /var/www/html/uploads

# Sesuaikan port Apache untuk Cloud Run (default port 8080)
ENV PORT=8080
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

EXPOSE 8080

CMD ["apache2-foreground"]

