<?php

/**
 * Production Environment Configuration
 * This file is loaded when CI_ENVIRONMENT is set to 'production'
 */

// Ensure environment is set correctly
define('ENVIRONMENT', 'production');
$_ENV['CI_ENVIRONMENT'] = 'production';
$_SERVER['CI_ENVIRONMENT'] = 'production';
putenv('CI_ENVIRONMENT=production');

// Production-specific settings
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);

// Security headers for production
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

// Custom domain configuration
$custom_domain = 'crm.shreyasmedia.net';
if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === $custom_domain) {
    // Force HTTPS for custom domain
    if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
        $redirectURL = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header("Location: $redirectURL", true, 301);
        exit();
    }
}