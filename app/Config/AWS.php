<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * AWS Configuration for RISE CRM
 * Configure AWS services like S3, RDS, CloudFront, etc.
 */
class AWS extends BaseConfig
{
    /**
     * AWS Region
     * @var string
     */
    public $region = 'us-east-1';

    /**
     * AWS Access Key ID
     * @var string
     */
    public $accessKeyId = '';

    /**
     * AWS Secret Access Key
     * @var string
     */
    public $secretAccessKey = '';

    /**
     * AWS Session Token (for temporary credentials)
     * @var string
     */
    public $sessionToken = '';

    public function __construct()
    {
        // Load from environment variables
        $this->accessKeyId = $_ENV['AWS_ACCESS_KEY_ID'] ?? '';
        $this->secretAccessKey = $_ENV['AWS_SECRET_ACCESS_KEY'] ?? '';
        $this->sessionToken = $_ENV['AWS_SESSION_TOKEN'] ?? '';
        $this->region = $_ENV['AWS_REGION'] ?? 'us-east-1';
        
        // Update S3 configuration
        $this->s3['bucket'] = $_ENV['S3_BUCKET'] ?? 'rise-crm-storage';
        $this->s3['region'] = $_ENV['S3_REGION'] ?? 'us-east-1';
        $this->s3['maxFileSize'] = (int)($_ENV['S3_MAX_FILE_SIZE'] ?? 10485760);
        
        // Update RDS configuration
        $this->rds['endpoint'] = $_ENV['RDS_ENDPOINT'] ?? '';
        $this->rds['username'] = $_ENV['RDS_USERNAME'] ?? '';
        $this->rds['password'] = $_ENV['RDS_PASSWORD'] ?? '';
        $this->rds['database'] = $_ENV['RDS_DATABASE'] ?? 'rise_crm';
        $this->rds['port'] = (int)($_ENV['RDS_PORT'] ?? 3306);
        
        // Update CloudFront configuration
        $this->cloudFront['domain'] = $_ENV['CLOUDFRONT_DOMAIN'] ?? '';
        $this->cloudFront['distributionId'] = $_ENV['CLOUDFRONT_DISTRIBUTION_ID'] ?? '';
        
        // Update SES configuration
        $this->ses['fromEmail'] = $_ENV['SES_FROM_EMAIL'] ?? 'noreply@yourdomain.com';
        $this->ses['replyToEmail'] = $_ENV['SES_REPLY_TO_EMAIL'] ?? 'support@yourdomain.com';
        
        // Update SNS configuration
        $this->sns['platformApplicationArn'] = $_ENV['SNS_PLATFORM_APPLICATION_ARN'] ?? '';
        
        // Update CloudWatch configuration
        $this->cloudWatch['logGroup'] = $_ENV['CLOUDWATCH_LOG_GROUP'] ?? 'rise-crm-logs';
        $this->cloudWatch['logStream'] = $_ENV['CLOUDWATCH_LOG_STREAM'] ?? 'application';
    }

    /**
     * S3 Configuration
     */
    public $s3 = [
        'bucket' => 'rise-crm-storage',
        'region' => 'us-east-1',
        'version' => 'latest',
        'usePathStyleEndpoint' => false,
        'ACL' => 'private',
        'defaultVisibility' => 'private',
        'maxFileSize' => 10485760, // 10MB
        'allowedMimeTypes' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
            'text/csv'
        ]
    ];

    /**
     * RDS Configuration
     */
    public $rds = [
        'endpoint' => '',
        'port' => 3306,
        'database' => 'rise_crm',
        'username' => '',
        'password' => '',
        'ssl' => true
    ];

    /**
     * CloudFront Configuration
     */
    public $cloudFront = [
        'domain' => '',
        'distributionId' => '',
        'sslProtocol' => 'TLSv1.2'
    ];

    /**
     * SES Configuration (for emails)
     */
    public $ses = [
        'region' => 'us-east-1',
        'fromEmail' => 'noreply@yourdomain.com',
        'replyToEmail' => 'support@yourdomain.com'
    ];

    /**
     * SNS Configuration (for notifications)
     */
    public $sns = [
        'region' => 'us-east-1',
        'platformApplicationArn' => ''
    ];

    /**
     * CloudWatch Configuration (for logging)
     */
    public $cloudWatch = [
        'region' => 'us-east-1',
        'logGroup' => 'rise-crm-logs',
        'logStream' => 'application'
    ];
}
