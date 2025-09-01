<?php

namespace App\Controllers;

use App\Services\AWSS3Service;
use App\Services\AWSRDSService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\AWS as AWSConfig;
use CodeIgniter\Controller;

/**
 * AWS Controller for RISE CRM
 * Demonstrates integration with AWS services
 */
class AWSController extends Controller
{
    protected $s3Service;
    protected $rdsService;
    protected $awsConfig;

    public function __construct()
    {
        $this->s3Service = new AWSS3Service();
        $this->rdsService = new AWSRDSService();
        $this->awsConfig = config('AWS');
    }

    /**
     * Test AWS S3 connection and list buckets
     */
    public function testS3()
    {
        try {
            // Test S3 connection by listing files
            $files = $this->s3Service->listFiles('', 10);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'S3 connection successful',
                'bucket' => $this->awsConfig->s3['bucket'] ?? 'rise-crm-storage',
                'files_count' => count($files),
                'files' => $files
            ]);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'S3 connection failed: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Test AWS RDS connection
     */
    public function testRDS()
    {
        try {
            $isConnected = $this->rdsService->testConnection();
            
            if ($isConnected) {
                $status = $this->rdsService->getDatabaseStatus();
                $variables = $this->rdsService->getDatabaseVariables();
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'RDS connection successful',
                    'endpoint' => $this->awsConfig->rds['endpoint'] ?? '',
                    'database' => $this->awsConfig->rds['database'] ?? 'rise_crm',
                    'status' => $status,
                    'variables' => $variables
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'RDS connection failed'
                ])->setStatusCode(500);
            }
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'RDS connection error: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Upload file to S3
     */
    public function uploadFile()
    {
        $file = $this->request->getFile('file');
        
        if (!$file || !$file->isValid()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No valid file uploaded'
            ])->setStatusCode(400);
        }

        try {
            $folder = $this->request->getPost('folder') ?? 'general';
            $result = $this->s3Service->uploadFile($file, $folder);
            
            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'File uploaded successfully',
                    'data' => $result
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'File upload failed'
                ])->setStatusCode(500);
            }
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Upload error: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Get file from S3
     */
    public function getFile($key = null)
    {
        if (!$key) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'File key is required'
            ])->setStatusCode(400);
        }

        try {
            $url = $this->s3Service->getFileUrl($key);
            
            return $this->response->setJSON([
                'success' => true,
                'key' => $key,
                'url' => $url,
                'presigned_url' => $this->s3Service->createPresignedUrl($key, 3600)
            ]);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error getting file: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Delete file from S3
     */
    public function deleteFile($key = null)
    {
        if (!$key) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'File key is required'
            ])->setStatusCode(400);
        }

        try {
            $deleted = $this->s3Service->deleteFile($key);
            
            if ($deleted) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'File deleted successfully',
                    'key' => $key
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'File deletion failed'
                ])->setStatusCode(500);
            }
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Deletion error: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Get AWS service status
     */
    public function getStatus()
    {
        $status = [
            'aws_region' => $this->awsConfig->region ?? 'us-east-1',
            's3_bucket' => $this->awsConfig->s3['bucket'] ?? 'rise-crm-storage',
            'rds_endpoint' => $this->awsConfig->rds['endpoint'] ?? '',
            'cloudfront_domain' => $this->awsConfig->cloudFront['domain'] ?? '',
            'ses_from_email' => $this->awsConfig->ses['fromEmail'] ?? 'noreply@yourdomain.com',
            'sns_arn' => $this->awsConfig->sns['platformApplicationArn'] ?? '',
            'cloudwatch_log_group' => $this->awsConfig->cloudWatch['logGroup'] ?? 'rise-crm-logs'
        ];

        return $this->response->setJSON([
            'success' => true,
            'aws_status' => $status
        ]);
    }
}
