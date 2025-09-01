<?php
// Debug script to test configuration loading
echo "Testing CodeIgniter Configuration Loading\n";
echo "==========================================\n\n";

// Set environment
$_ENV['CI_ENVIRONMENT'] = 'production';
$_SERVER['CI_ENVIRONMENT'] = 'production';
putenv('CI_ENVIRONMENT=production');

echo "Environment set to: " . ($_ENV['CI_ENVIRONMENT'] ?? 'NOT SET') . "\n";

// Load the framework
require_once 'app/Config/Paths.php';
$paths = new Config\Paths();

// Load the framework bootstrap
require $paths->systemDirectory . '/Boot.php';

// Test configuration loading
try {
    $appConfig = config('App');
    if ($appConfig) {
        echo "✓ App configuration loaded successfully\n";
        echo "  Base URL: " . $appConfig->baseURL . "\n";
        echo "  Index Page: " . $appConfig->indexPage . "\n";
    } else {
        echo "✗ App configuration is null\n";
    }
} catch (Exception $e) {
    echo "✗ Error loading App configuration: " . $e->getMessage() . "\n";
}

echo "\nDone.\n";
