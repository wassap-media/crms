# Use PHP 8.2 with Apache - optimized for Render
FROM php:8.2-apache

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    curl \
    && docker-php-ext-install \
    mysqli \
    pdo \
    pdo_mysql \
    zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Configure Apache for Render
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Configure Apache to allow .htaccess overrides and listen on PORT
RUN printf "<Directory /var/www/html>\n\tAllowOverride All\n\tRequire all granted\n</Directory>\n" > /etc/apache2/conf-available/override.conf \
    && a2enconf override

# Set PHP configuration for production
RUN { \
    echo "upload_max_filesize=20M"; \
    echo "post_max_size=20M"; \
    echo "memory_limit=256M"; \
    echo "max_execution_time=120"; \
    echo "display_errors=Off"; \
    echo "log_errors=On"; \
    echo "error_log=/var/log/apache2/php_errors.log"; \
} > /usr/local/etc/php/conf.d/production.ini

# Set environment variables for production
ENV CI_ENVIRONMENT=production
ENV app.baseURL=''

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Install Composer dependencies if composer.json exists
RUN if [ -f "composer.json" ]; then \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-dev --optimize-autoloader --no-interaction; \
    fi

# Set proper permissions for writable directories
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && if [ -d "writable" ]; then chmod -R 777 writable; fi \
    && if [ -d "files" ]; then chmod -R 777 files; fi

# Copy and setup startup script
COPY start-apache.sh /usr/local/bin/start-apache.sh
RUN chmod +x /usr/local/bin/start-apache.sh \
    && cp /etc/apache2/ports.conf /etc/apache2/ports.conf.backup \
    && cp /etc/apache2/sites-available/000-default.conf /etc/apache2/sites-available/000-default.conf.backup

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://localhost:${PORT:-80}/health.php || exit 1

# Expose port (Render will override this)
EXPOSE 80

# Start Apache using our custom script
CMD ["/usr/local/bin/start-apache.sh"]


