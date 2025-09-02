<?php
// Backup current settings to ensure they persist after deployment
// Direct database connection using environment variables

// Load environment variables
$env_file = '.env';
if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Database connection parameters
$host = $_ENV['RDS_ENDPOINT'] ?? 'rise-crm-db.chuwwkauk8ky.us-west-2.rds.amazonaws.com';
$dbname = $_ENV['RDS_DATABASE'] ?? 'rise_crm';
$username = $_ENV['RDS_USERNAME'] ?? 'admin';
$password = $_ENV['RDS_PASSWORD'] ?? 'CrmPassword123!';
$port = $_ENV['RDS_PORT'] ?? '3306';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Database connected successfully!\n\n";
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage() . "\n");
}

// Create backup of current settings
echo "=== Creating Settings Backup ===\n";

$stmt = $pdo->query("SELECT setting_name, setting_value, type FROM settings WHERE deleted = 0 ORDER BY setting_name");
$results = $stmt->fetchAll(PDO::FETCH_OBJ);

$backup_data = [];
foreach ($results as $row) {
    $backup_data[] = [
        'setting_name' => $row->setting_name,
        'setting_value' => $row->setting_value,
        'type' => $row->type
    ];
}

// Save backup to file
file_put_contents('settings_backup.json', json_encode($backup_data, JSON_PRETTY_PRINT));
echo "Settings backup saved to settings_backup.json\n";

// Also create SQL backup
$sql_backup = "-- Settings Backup SQL\n";
$sql_backup .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n\n";

foreach ($results as $row) {
    $setting_name = $pdo->quote($row->setting_name);
    $setting_value = $pdo->quote($row->setting_value);
    $type = $pdo->quote($row->type);
    
    $sql_backup .= "INSERT INTO settings (setting_name, setting_value, type, deleted) VALUES ($setting_name, $setting_value, $type, 0) ON DUPLICATE KEY UPDATE setting_value = $setting_value;\n";
}

file_put_contents('settings_backup.sql', $sql_backup);
echo "SQL backup saved to settings_backup.sql\n";

echo "\n=== Key Settings Summary ===\n";
$key_settings = ['app_title', 'site_logo', 'favicon', 'show_background_image_in_signin_page', 'show_logo_in_signin_page', 'default_theme_color', 'system_file_path', 'temp_file_path'];

foreach ($key_settings as $setting) {
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_name = ? AND deleted = 0");
    $stmt->execute([$setting]);
    $result = $stmt->fetch(PDO::FETCH_OBJ);
    if ($result) {
        echo "✓ {$setting}: {$result->setting_value}\n";
    } else {
        echo "✗ {$setting}: NOT FOUND\n";
    }
}

echo "\nBackup completed successfully!\n";
?>
