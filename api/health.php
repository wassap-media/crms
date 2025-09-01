<?php
/**
 * Health check endpoint for Vercel
 */

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

$health = [
    'status' => 'healthy',
    'timestamp' => date('c'),
    'service' => 'RISE CRM',
    'version' => '1.0.0',
    'platform' => 'vercel',
    'php_version' => PHP_VERSION,
    'environment' => $_ENV['CI_ENVIRONMENT'] ?? 'production',
    'checks' => []
];

// Check if main application files exist
if (file_exists(__DIR__ . '/../index.php')) {
    $health['checks']['app_files'] = 'ok';
} else {
    $health['checks']['app_files'] = 'error';
}

// Check if system directory exists
if (is_dir(__DIR__ . '/../system')) {
    $health['checks']['system_dir'] = 'ok';
} else {
    $health['checks']['system_dir'] = 'error';
}

// Check database connection if environment variables are set
if (!empty($_ENV['RDS_ENDPOINT'])) {
    try {
        $pdo = new PDO(
            "mysql:host={$_ENV['RDS_ENDPOINT']};port=" . ($_ENV['RDS_PORT'] ?? 3306) . ";dbname={$_ENV['RDS_DATABASE']}",
            $_ENV['RDS_USERNAME'],
            $_ENV['RDS_PASSWORD'],
            [PDO::ATTR_TIMEOUT => 5]
        );
        $health['checks']['database'] = 'ok';
        $health['aws']['rds_endpoint'] = $_ENV['RDS_ENDPOINT'];
    } catch (PDOException $e) {
        $health['checks']['database'] = 'error';
        $health['errors']['database'] = $e->getMessage();
    }
}

// Check AWS S3 configuration
if (!empty($_ENV['S3_BUCKET'])) {
    $health['aws']['s3_bucket'] = $_ENV['S3_BUCKET'];
    $health['aws']['aws_region'] = $_ENV['AWS_REGION'] ?? 'us-west-2';
    $health['checks']['aws_config'] = 'ok';
}

// Overall health status
$hasErrors = false;
foreach ($health['checks'] as $check => $status) {
    if ($status === 'error') {
        $hasErrors = true;
        break;
    }
}

if ($hasErrors) {
    http_response_code(503);
    $health['status'] = 'unhealthy';
} else {
    http_response_code(200);
}

echo json_encode($health, JSON_PRETTY_PRINT);
?>
