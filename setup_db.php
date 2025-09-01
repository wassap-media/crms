<?php
// Database setup script
echo "=== RISE CRM Database Setup ===\n";

// Check environment variables
$host = getenv('RDS_ENDPOINT');
$username = getenv('RDS_USERNAME');
$password = getenv('RDS_PASSWORD');
$database = getenv('RDS_DATABASE') ?: 'rise_crm';
$port = getenv('RDS_PORT') ?: 3306;

echo "Connecting to database: $host:$port\n";
echo "Database: $database\n";
echo "Username: $username\n";

if (!$host || !$username || !$password) {
    echo "✗ Missing required environment variables\n";
    exit(1);
}

try {
    // Connect to database
    $mysqli = new mysqli($host, $username, $password, $database, $port);
    
    if ($mysqli->connect_error) {
        echo "✗ Connection failed: " . $mysqli->connect_error . "\n";
        exit(1);
    }
    
    echo "✓ Connected to database successfully\n";
    
    // Read the SQL file
    $sqlFile = __DIR__ . '/install/database.sql';
    if (!file_exists($sqlFile)) {
        // Try alternative paths
        $alternativePaths = [
            __DIR__ . '/install/database.sql',
            '/var/www/html/install/database.sql',
            './install/database.sql',
            '../install/database.sql'
        ];
        
        foreach ($alternativePaths as $path) {
            if (file_exists($path)) {
                $sqlFile = $path;
                break;
            }
        }
    }
    if (!file_exists($sqlFile)) {
        echo "✗ SQL file not found: $sqlFile\n";
        echo "Current directory: " . __DIR__ . "\n";
        echo "Checking available files:\n";
        
        // List files in current directory
        $files = scandir(__DIR__);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                echo "  - $file\n";
            }
        }
        
        // List files in install directory if it exists
        if (is_dir(__DIR__ . '/install')) {
            echo "Files in install directory:\n";
            $installFiles = scandir(__DIR__ . '/install');
            foreach ($installFiles as $file) {
                if ($file != '.' && $file != '..') {
                    echo "  - install/$file\n";
                }
            }
        }
        
        exit(1);
    }
    
    echo "Reading SQL file: $sqlFile\n";
    $sql = file_get_contents($sqlFile);
    
    if (!$sql) {
        echo "✗ Could not read SQL file\n";
        exit(1);
    }
    
    echo "SQL file size: " . strlen($sql) . " bytes\n";
    
    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    echo "Found " . count($statements) . " SQL statements\n";
    
    // Execute each statement
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($statements as $statement) {
        if (empty($statement)) continue;
        
        try {
            $result = $mysqli->query($statement);
            if ($result) {
                $successCount++;
                echo "✓ Executed: " . substr($statement, 0, 50) . "...\n";
            } else {
                $errorCount++;
                echo "✗ Error: " . $mysqli->error . "\n";
                echo "Statement: " . substr($statement, 0, 100) . "...\n";
            }
        } catch (Exception $e) {
            $errorCount++;
            echo "✗ Exception: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n=== Setup Complete ===\n";
    echo "Successful statements: $successCount\n";
    echo "Failed statements: $errorCount\n";
    
    if ($errorCount > 0) {
        echo "⚠ Some statements failed, but database may still be usable\n";
    } else {
        echo "✓ Database setup completed successfully!\n";
    }
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "✗ Exception: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== Database Setup Complete ===\n";
?>
