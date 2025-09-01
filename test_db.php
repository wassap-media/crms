<?php
// Database connection test script
echo "=== Database Connection Test ===\n";

// Check environment variables
echo "Environment Variables:\n";
echo "RDS_ENDPOINT: " . (getenv('RDS_ENDPOINT') ?: 'NOT SET') . "\n";
echo "RDS_USERNAME: " . (getenv('RDS_USERNAME') ?: 'NOT SET') . "\n";
echo "RDS_DATABASE: " . (getenv('RDS_DATABASE') ?: 'NOT SET') . "\n";
echo "RDS_PORT: " . (getenv('RDS_PORT') ?: 'NOT SET') . "\n";
echo "RDS_PASSWORD: " . (getenv('RDS_PASSWORD') ? 'SET' : 'NOT SET') . "\n";

// Test direct MySQL connection
if (getenv('RDS_ENDPOINT') && getenv('RDS_USERNAME') && getenv('RDS_PASSWORD')) {
    echo "\nTesting direct MySQL connection...\n";
    
    $host = getenv('RDS_ENDPOINT');
    $username = getenv('RDS_USERNAME');
    $password = getenv('RDS_PASSWORD');
    $database = getenv('RDS_DATABASE') ?: 'rise_crm';
    $port = getenv('RDS_PORT') ?: 3306;
    
    echo "Connecting to: $host:$port\n";
    echo "Database: $database\n";
    echo "Username: $username\n";
    
    try {
        $mysqli = new mysqli($host, $username, $password, $database, $port);
        
        if ($mysqli->connect_error) {
            echo "✗ Connection failed: " . $mysqli->connect_error . "\n";
        } else {
            echo "✓ Connection successful!\n";
            echo "Server info: " . $mysqli->server_info . "\n";
            $mysqli->close();
        }
    } catch (Exception $e) {
        echo "✗ Exception: " . $e->getMessage() . "\n";
    }
} else {
    echo "\n✗ Missing required environment variables for database connection\n";
}

echo "\n=== Test Complete ===\n";
?>
