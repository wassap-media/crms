<?php

namespace App\Services;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use CodeIgniter\HTTP\Files\UploadedFile;
use Config\AWS as AWSConfig;

/**
 * AWS S3 Service for RISE CRM
 * Handles file uploads, storage, and management
 */
class AWSS3Service
{
    private $s3Client;
    private $config;
    private $bucket;

    public function __construct()
    {
        $this->config = config('AWS');
        $this->bucket = $this->config->s3['bucket'];
        
        $this->s3Client = new S3Client([
            'version' => $this->config->s3['version'],
            'region'  => $this->config->s3['region'],
            'credentials' => [
                'key'    => $this->config->accessKeyId,
                'secret' => $this->config->secretAccessKey,
            ],
            'use_path_style_endpoint' => $this->config->s3['usePathStyleEndpoint']
        ]);
    }

    /**
     * Upload file to S3
     * @param UploadedFile $file
     * @param string $folder
     * @return array|false
     */
    public function uploadFile(UploadedFile $file, string $folder = 'general')
    {
        try {
            // Validate file
            if (!$this->validateFile($file)) {
                return false;
            }

            // Generate unique filename
            $filename = $this->generateUniqueFilename($file);
            $key = $folder . '/' . $filename;

            // Upload to S3
            $result = $this->s3Client->putObject([
                'Bucket' => $this->bucket,
                'Key'    => $key,
                'Body'   => fopen($file->getTempName(), 'r'),
                'ACL'    => $this->config->s3['ACL'],
                'ContentType' => $file->getClientMimeType(),
                'Metadata' => [
                    'original_name' => $file->getClientName(),
                    'uploaded_by' => 'rise_crm',
                    'upload_date' => date('Y-m-d H:i:s')
                ]
            ]);

            return [
                'success' => true,
                'url' => $result['ObjectURL'],
                'key' => $key,
                'filename' => $filename,
                'size' => $file->getSize(),
                'mime_type' => $file->getClientMimeType()
            ];

        } catch (AwsException $e) {
            log_message('error', 'AWS S3 Upload Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Download file from S3
     * @param string $key
     * @return string|false
     */
    public function downloadFile(string $key)
    {
        try {
            $result = $this->s3Client->getObject([
                'Bucket' => $this->bucket,
                'Key'    => $key
            ]);

            return $result['Body']->getContents();

        } catch (AwsException $e) {
            log_message('error', 'AWS S3 Download Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete file from S3
     * @param string $key
     * @return bool
     */
    public function deleteFile(string $key): bool
    {
        try {
            $this->s3Client->deleteObject([
                'Bucket' => $this->bucket,
                'Key'    => $key
            ]);

            return true;

        } catch (AwsException $e) {
            log_message('error', 'AWS S3 Delete Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get file URL (with CloudFront if configured)
     * @param string $key
     * @return string
     */
    public function getFileUrl(string $key): string
    {
        if (!empty($this->config->cloudFront['domain'])) {
            return 'https://' . $this->config->cloudFront['domain'] . '/' . $key;
        }

        return $this->s3Client->getObjectUrl($this->bucket, $key);
    }

    /**
     * List files in a folder
     * @param string $folder
     * @param int $limit
     * @return array
     */
    public function listFiles(string $folder = '', int $limit = 100): array
    {
        try {
            $params = [
                'Bucket' => $this->bucket,
                'MaxKeys' => $limit
            ];

            if (!empty($folder)) {
                $params['Prefix'] = $folder . '/';
            }

            $result = $this->s3Client->listObjectsV2($params);
            $files = [];

            if (isset($result['Contents'])) {
                foreach ($result['Contents'] as $object) {
                    $files[] = [
                        'key' => $object['Key'],
                        'size' => $object['Size'],
                        'last_modified' => $object['LastModified']->format('Y-m-d H:i:s'),
                        'url' => $this->getFileUrl($object['Key'])
                    ];
                }
            }

            return $files;

        } catch (AwsException $e) {
            log_message('error', 'AWS S3 List Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Validate uploaded file
     * @param UploadedFile $file
     * @return bool
     */
    private function validateFile(UploadedFile $file): bool
    {
        // Check file size
        if ($file->getSize() > $this->config->s3['maxFileSize']) {
            return false;
        }

        // Check MIME type
        if (!in_array($file->getClientMimeType(), $this->config->s3['allowedMimeTypes'])) {
            return false;
        }

        return true;
    }

    /**
     * Generate unique filename
     * @param UploadedFile $file
     * @return string
     */
    private function generateUniqueFilename(UploadedFile $file): string
    {
        $extension = $file->getClientExtension();
        $timestamp = time();
        $random = bin2hex(random_bytes(8));
        
        return $timestamp . '_' . $random . '.' . $extension;
    }

    /**
     * Create presigned URL for secure file access
     * @param string $key
     * @param int $expires
     * @return string|false
     */
    public function createPresignedUrl(string $key, int $expires = 3600)
    {
        try {
            $cmd = $this->s3Client->getCommand('GetObject', [
                'Bucket' => $this->bucket,
                'Key'    => $key
            ]);

            $request = $this->s3Client->createPresignedRequest($cmd, "+{$expires} seconds");
            return (string) $request->getUri();

        } catch (AwsException $e) {
            log_message('error', 'AWS S3 Presigned URL Error: ' . $e->getMessage());
            return false;
        }
    }
}
