<?php
/**
 * Simple AWS Integration Test
 * Run this file to test AWS services without CodeIgniter
 */

// Set basic environment
$_ENV['AWS_ACCESS_KEY_ID'] = 'your_aws_access_key_here';
$_ENV['AWS_SECRET_ACCESS_KEY'] = 'your_aws_secret_key_here';
$_ENV['AWS_REGION'] = 'us-east-1';
$_ENV['S3_BUCKET'] = 'rise-crm-storage';

echo "=== AWS Integration Test ===\n\n";

// Test 1: Check if AWS SDK is installed
echo "1. Testing AWS SDK Installation...\n";
if (file_exists('vendor/aws/aws-sdk-php/src/Sdk.php')) {
    echo "✅ AWS SDK for PHP is installed\n";
} else {
    echo "❌ AWS SDK for PHP is NOT installed\n";
}

// Test 2: Check configuration files
echo "\n2. Testing Configuration Files...\n";
$configFiles = [
    'app/Config/AWS.php' => 'AWS Configuration',
    'app/Services/AWSS3Service.php' => 'S3 Service',
    'app/Services/AWSRDSService.php' => 'RDS Service',
    'app/Controllers/AWSController.php' => 'AWS Controller',
    'app/Helpers/aws_helper.php' => 'AWS Helper',
    'app/Config/rds-ca-2019-root.pem' => 'RDS SSL Certificate'
];

foreach ($configFiles as $file => $description) {
    if (file_exists($file)) {
        echo "✅ $description exists\n";
    } else {
        echo "❌ $description is missing\n";
    }
}

// Test 3: Check environment files
echo "\n3. Testing Environment Configuration...\n";
if (file_exists('.env')) {
    echo "✅ .env file exists\n";
} else {
    echo "❌ .env file is missing (copy from env.example)\n";
}

if (file_exists('env.example')) {
    echo "✅ env.example file exists\n";
} else {
    echo "❌ env.example file is missing\n";
}

// Test 4: Check if AWS credentials are set
echo "\n4. Testing AWS Credentials...\n";
if (!empty($_ENV['AWS_ACCESS_KEY_ID']) && $_ENV['AWS_ACCESS_KEY_ID'] !== 'your_aws_access_key_here') {
    echo "✅ AWS Access Key ID is set\n";
} else {
    echo "⚠️  AWS Access Key ID needs to be configured in .env\n";
}

if (!empty($_ENV['AWS_SECRET_ACCESS_KEY']) && $_ENV['AWS_SECRET_ACCESS_KEY'] !== 'your_aws_secret_key_here') {
    echo "✅ AWS Secret Access Key is set\n";
} else {
    echo "⚠️  AWS Secret Access Key needs to be configured in .env\n";
}

// Test 5: Check composer autoload
echo "\n5. Testing Composer Autoload...\n";
if (file_exists('vendor/autoload.php')) {
    echo "✅ Composer autoload exists\n";
    require_once 'vendor/autoload.php';
    
    // Try to load AWS SDK
    try {
        $sdk = new Aws\Sdk([
            'region' => $_ENV['AWS_REGION'],
            'version' => 'latest'
        ]);
        echo "✅ AWS SDK can be instantiated\n";
    } catch (Exception $e) {
        echo "❌ AWS SDK instantiation failed: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ Composer autoload is missing\n";
}

echo "\n=== Test Summary ===\n";
echo "✅ = Working correctly\n";
echo "⚠️  = Needs configuration\n";
echo "❌ = Missing or broken\n";

echo "\n=== Next Steps ===\n";
echo "1. Configure your AWS credentials in .env file\n";
echo "2. Create AWS resources (S3 bucket, RDS instance, etc.)\n";
echo "3. Test the integration through the web interface\n";
echo "4. Access AWS endpoints: /awscontroller/testS3, /awscontroller/testRDS\n";

echo "\n=== AWS Integration Setup Complete! ===\n";
