#!/bin/bash
set -e

# Set default port if PORT environment variable is not set
PORT=${PORT:-80}

# Set CodeIgniter environment for production
export CI_ENVIRONMENT=${CI_ENVIRONMENT:-production}

# Ensure environment variables are available to PHP
echo "CI_ENVIRONMENT=$CI_ENVIRONMENT" >> /etc/environment

# Set environment variable for Apache/PHP
echo "SetEnv CI_ENVIRONMENT $CI_ENVIRONMENT" >> /etc/apache2/conf-available/env.conf
a2enconf env

# Clear any cached configurations
rm -rf /var/www/html/writable/cache/*

# Ensure proper permissions for writable directory
chmod -R 777 /var/www/html/writable

# Set proper environment for PHP
echo "export CI_ENVIRONMENT=$CI_ENVIRONMENT" >> /etc/profile
echo "export CI_ENVIRONMENT=$CI_ENVIRONMENT" >> /etc/bash.bashrc

# Ensure composer autoloader is working
if [ -f "/var/www/html/vendor/autoload.php" ]; then
    echo "✓ Composer autoloader found"
else
    echo "✗ Composer autoloader not found"
fi

# Set environment variables for Apache
echo "SetEnv CI_ENVIRONMENT $CI_ENVIRONMENT" >> /etc/apache2/conf-available/env.conf
echo "SetEnv app.baseURL ''" >> /etc/apache2/conf-available/env.conf
a2enconf env

# Diagnostic script removed - proceeding with Apache startup

# Configuration test disabled for now
echo "Configuration test disabled - proceeding with Apache startup..."

# Ensure proper file permissions
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html
chmod -R 777 /var/www/html/writable

echo "Starting Apache with PORT=$PORT"
echo "CodeIgniter Environment: $CI_ENVIRONMENT"

# Backup original configurations if they don't exist
if [ ! -f "/etc/apache2/ports.conf.backup" ]; then
    cp /etc/apache2/ports.conf /etc/apache2/ports.conf.backup
fi

if [ ! -f "/etc/apache2/sites-available/000-default.conf.backup" ]; then
    cp /etc/apache2/sites-available/000-default.conf /etc/apache2/sites-available/000-default.conf.backup
fi

# Restore original configurations and apply port changes
cp /etc/apache2/ports.conf.backup /etc/apache2/ports.conf
cp /etc/apache2/sites-available/000-default.conf.backup /etc/apache2/sites-available/000-default.conf

# Update Apache configuration with the actual port
sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:$PORT>/" /etc/apache2/sites-available/000-default.conf

# Verify configuration
echo "Checking Apache configuration..."
apache2ctl configtest

echo "Apache configuration updated successfully. Starting server..."

# Start Apache in foreground
exec apache2-foreground
