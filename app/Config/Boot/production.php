<?php

/*
 |--------------------------------------------------------------------------
 | ERROR DISPLAY
 |--------------------------------------------------------------------------
 | Don't show ANY in production environments. Instead, let the system catch
 | it and display a generic error message.
 |
 | If you set 'display_errors' to '1', CI4's detailed error report will show.
 */
error_reporting(E_ALL & ~E_DEPRECATED);
// If you want to suppress more types of errors.
// error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
ini_set('display_errors', '0');

/*
 |--------------------------------------------------------------------------
 | DEBUG MODE
 |--------------------------------------------------------------------------
 | Debug mode is an experimental flag that can allow changes throughout
 | the system. It's not widely used currently, and may not survive
 | release of the framework.
 */
defined('CI_DEBUG') || define('CI_DEBUG', false);

/*
 |--------------------------------------------------------------------------
 | ENVIRONMENT CONFIGURATION
 |--------------------------------------------------------------------------
 | Ensure the environment is properly set for production
 */
if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', 'production');
}

// Ensure CI_ENVIRONMENT is set in $_ENV and $_SERVER
$_ENV['CI_ENVIRONMENT'] = 'production';
$_SERVER['CI_ENVIRONMENT'] = 'production';
putenv('CI_ENVIRONMENT=production');
