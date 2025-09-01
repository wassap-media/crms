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

# Create a comprehensive PHP test to verify configuration loading
cat > /var/www/html/test_config.php << 'EOF'
<?php
// Comprehensive configuration loading test
echo "=== CodeIgniter Configuration Test ===\n";

// Set environment
$_ENV['CI_ENVIRONMENT'] = 'production';
$_SERVER['CI_ENVIRONMENT'] = 'production';
putenv('CI_ENVIRONMENT=production');

echo "Environment: " . ($_ENV['CI_ENVIRONMENT'] ?? 'NOT SET') . "\n";

// Test 1: Check if autoloader exists
if (file_exists('vendor/autoload.php')) {
    echo "✓ Autoloader found\n";
    require_once 'vendor/autoload.php';
} else {
    echo "✗ Autoloader not found\n";
    exit(1);
}

// Test 2: Check if Paths.php exists
if (file_exists('app/Config/Paths.php')) {
    echo "✓ Paths.php found\n";
    require_once 'app/Config/Paths.php';
} else {
    echo "✗ Paths.php not found\n";
    exit(1);
}

// Test 3: Check if Boot.php exists
$paths = new Config\Paths();
if (file_exists($paths->systemDirectory . '/Boot.php')) {
    echo "✓ Boot.php found\n";
    require $paths->systemDirectory . '/Boot.php';
} else {
    echo "✗ Boot.php not found\n";
    exit(1);
}

// Test 4: Check if App.php exists
if (file_exists('app/Config/App.php')) {
    echo "✓ App.php found\n";
} else {
    echo "✗ App.php not found\n";
    echo "Current working directory: " . getcwd() . "\n";
    echo "Checking alternative paths...\n";
    
    // Check alternative paths
    $possible_paths = [
        'app/Config/App.php',
        '/var/www/html/app/Config/App.php',
        './app/Config/App.php',
        '../app/Config/App.php'
    ];
    
    foreach ($possible_paths as $path) {
        if (file_exists($path)) {
            echo "✓ App.php found at: $path\n";
            break;
        } else {
            echo "✗ App.php not found at: $path\n";
        }
    }
    
    // List contents of app/Config directory
    if (is_dir('app/Config')) {
        echo "Contents of app/Config directory:\n";
        $files = scandir('app/Config');
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                echo "  - $file\n";
            }
        }
    } else {
        echo "app/Config directory not found\n";
    }
    
    exit(1);
}

// Test 5: Try to load configuration
try {
    $appConfig = config('App');
    if ($appConfig) {
        echo "✓ Configuration loaded successfully\n";
        echo "  Base URL: " . $appConfig->baseURL . "\n";
        echo "  Index Page: " . $appConfig->indexPage . "\n";
    } else {
        echo "✗ Configuration is null\n";
    }
} catch (Exception $e) {
    echo "✗ Error loading configuration: " . $e->getMessage() . "\n";
}

echo "=== Test Complete ===\n";
EOF

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
