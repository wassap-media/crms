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
