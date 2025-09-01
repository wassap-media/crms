FROM php:8.2-apache

# Install PHP extensions required by CodeIgniter and MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql \
    && a2enmod rewrite

# Configure Apache to allow .htaccess overrides
RUN printf "<Directory /var/www/html>\n\	AllowOverride All\n\	Require all granted\n</Directory>\n" > /etc/apache2/conf-available/ci.conf \
    && a2enconf ci

# Set recommended PHP settings for production
RUN { \
  echo "upload_max_filesize=20M"; \
  echo "post_max_size=20M"; \
  echo "memory_limit=256M"; \
  echo "max_execution_time=120"; \
} > /usr/local/etc/php/conf.d/custom.ini

WORKDIR /var/www/html

# Copy application
COPY . /var/www/html

# Ensure writable directories have correct permissions
RUN chown -R www-data:www-data /var/www/html/writable /var/www/html/files || true \
    && find /var/www/html/writable -type d -exec chmod 775 {} + || true \
    && find /var/www/html/writable -type f -exec chmod 664 {} + || true

EXPOSE 80

# Apache runs in foreground by default
CMD ["apache2-foreground"]


