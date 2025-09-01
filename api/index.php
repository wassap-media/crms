<?php
/**
 * Main entry point for Vercel deployment
 * Routes all requests through CodeIgniter
 */

// Set the correct paths for Vercel
define('FCPATH', __DIR__ . '/../');
define('SYSTEMPATH', FCPATH . 'system/');
define('APPPATH', FCPATH . 'app/');
define('WRITEPATH', FCPATH . 'writable/');

// Check if the system folder exists
if (!is_dir(SYSTEMPATH)) {
    header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
    echo 'Your system folder path does not appear to be set correctly.';
    exit(3);
}

// Set environment for Vercel
$_SERVER['CI_ENVIRONMENT'] = $_ENV['CI_ENVIRONMENT'] ?? 'production';

// Bootstrap CodeIgniter
require_once SYSTEMPATH . 'bootstrap.php';

// Create the application
$app = \Config\Services::codeigniter();
$app->initialize();

// Get the request URI and clean it
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$uri = parse_url($uri, PHP_URL_PATH);

// Handle the request
try {
    $response = $app->run();
    $response->send();
} catch (Exception $e) {
    // Handle errors gracefully
    header('HTTP/1.1 500 Internal Server Error');
    if ($_ENV['CI_ENVIRONMENT'] === 'development') {
        echo "Error: " . $e->getMessage();
    } else {
        echo "Internal Server Error";
    }
}

$app->cleanup();
?>
