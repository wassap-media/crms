#!/bin/bash
set -e

# Set default port if PORT environment variable is not set
PORT=${PORT:-80}

echo "Starting Apache with PORT=$PORT"

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
