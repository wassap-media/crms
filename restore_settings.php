<?php
// Restore settings from backup to ensure they persist after deployment
// This script should be run after each deployment

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

// Check if backup file exists
if (!file_exists('settings_backup.json')) {
    echo "No backup file found. Creating default settings...\n";
    
    // Create essential settings if they don't exist
    $essential_settings = [
        'app_title' => 'Shreyas Media CRM',
        'site_logo' => 'a:1:{s:9:"file_name";s:32:"_file68b688126ba04-site-logo.png";}',
        'favicon' => 'a:1:{s:9:"file_name";s:30:"_file68b68d25291a2-favicon.png";}',
        'show_background_image_in_signin_page' => 'yes',
        'show_logo_in_signin_page' => 'yes',
        'default_theme_color' => 'F2F2F2',
        'system_file_path' => 'files/system/',
        'temp_file_path' => 'files/temp/',
        'currency_symbol' => '₹',
        'default_currency' => 'INR',
        'date_format' => 'd-m-Y',
        'timezone' => 'Asia/Kolkata',
        'language' => 'english'
    ];
    
    foreach ($essential_settings as $name => $value) {
        $stmt = $pdo->prepare("INSERT INTO settings (setting_name, setting_value, type, deleted) VALUES (?, ?, 'app', 0) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$name, $value, $value]);
        echo "✓ Restored: $name\n";
    }
    
    echo "\nDefault settings restored successfully!\n";
    exit;
}

// Load backup data
$backup_data = json_decode(file_get_contents('settings_backup.json'), true);

if (!$backup_data) {
    die("Failed to load backup data.\n");
}

echo "=== Restoring Settings from Backup ===\n";

$restored_count = 0;
foreach ($backup_data as $setting) {
    $stmt = $pdo->prepare("INSERT INTO settings (setting_name, setting_value, type, deleted) VALUES (?, ?, ?, 0) ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt->execute([
        $setting['setting_name'], 
        $setting['setting_value'], 
        $setting['type'],
        $setting['setting_value']
    ]);
    $restored_count++;
}

echo "✓ Restored $restored_count settings from backup\n";

// Verify key settings
echo "\n=== Verifying Key Settings ===\n";
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

echo "\nSettings restoration completed successfully!\n";
?>
