<?php
// Update settings backup after settings are saved
// This ensures the backup is always current

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
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage() . "\n");
}

// Get current settings
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

// Update backup files
file_put_contents('settings_backup.json', json_encode($backup_data, JSON_PRETTY_PRINT));

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

echo "Settings backup updated successfully!\n";
?>
