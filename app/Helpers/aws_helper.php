<?php

/**
 * AWS Helper Functions for RISE CRM
 * Provides convenient functions for AWS operations
 */

if (!function_exists('upload_file_to_s3')) {
    /**
     * Upload file to AWS S3
     * @param mixed $file UploadedFile or file path
     * @param string $folder S3 folder
     * @return array|false
     */
    function upload_file_to_s3($file, string $folder = 'general')
    {
        $s3Service = new \App\Services\AWSS3Service();
        
        if (is_string($file)) {
            // Convert file path to UploadedFile-like object
            $uploadedFile = new \CodeIgniter\HTTP\Files\UploadedFile($file, basename($file));
            return $s3Service->uploadFile($uploadedFile, $folder);
        }
        
        return $s3Service->uploadFile($file, $folder);
    }
}

if (!function_exists('get_s3_file_url')) {
    /**
     * Get S3 file URL
     * @param string $key S3 file key
     * @param bool $presigned Generate presigned URL
     * @param int $expires Expiration time for presigned URL
     * @return string
     */
    function get_s3_file_url(string $key, bool $presigned = false, int $expires = 3600): string
    {
        $s3Service = new \App\Services\AWSS3Service();
        
        if ($presigned) {
            return $s3Service->createPresignedUrl($key, $expires) ?: '';
        }
        
        return $s3Service->getFileUrl($key);
    }
}

if (!function_exists('delete_s3_file')) {
    /**
     * Delete file from S3
     * @param string $key S3 file key
     * @return bool
     */
    function delete_s3_file(string $key): bool
    {
        $s3Service = new \App\Services\AWSS3Service();
        return $s3Service->deleteFile($key);
    }
}

if (!function_exists('list_s3_files')) {
    /**
     * List files in S3 folder
     * @param string $folder S3 folder
     * @param int $limit Number of files to return
     * @return array
     */
    function list_s3_files(string $folder = '', int $limit = 100): array
    {
        $s3Service = new \App\Services\AWSS3Service();
        return $s3Service->listFiles($folder, $limit);
    }
}

if (!function_exists('test_aws_connection')) {
    /**
     * Test AWS services connection
     * @return array
     */
    function test_aws_connection(): array
    {
        $results = [
            's3' => false,
            'rds' => false,
            'errors' => []
        ];
        
        try {
            // Test S3
            $s3Service = new \App\Services\AWSS3Service();
            $s3Service->listFiles('', 1);
            $results['s3'] = true;
        } catch (\Exception $e) {
            $results['errors']['s3'] = $e->getMessage();
        }
        
        try {
            // Test RDS
            $rdsService = new \App\Services\AWSRDSService();
            $results['rds'] = $rdsService->testConnection();
        } catch (\Exception $e) {
            $results['errors']['rds'] = $e->getMessage();
        }
        
        return $results;
    }
}

if (!function_exists('get_aws_config')) {
    /**
     * Get AWS configuration
     * @param string|null $key Specific config key
     * @return mixed
     */
    function get_aws_config(?string $key = null)
    {
        $config = config('AWS');
        
        if ($key) {
            return $config->{$key} ?? null;
        }
        
        return $config;
    }
}

if (!function_exists('is_aws_configured')) {
    /**
     * Check if AWS is properly configured
     * @return bool
     */
    function is_aws_configured(): bool
    {
        $config = config('AWS');
        
        return !empty($config->accessKeyId) && 
               !empty($config->secretAccessKey) &&
               !empty($config->s3['bucket']);
    }
}

if (!function_exists('format_file_size')) {
    /**
     * Format file size in human readable format
     * @param int $bytes
     * @return string
     */
    function format_file_size(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}

if (!function_exists('validate_aws_file')) {
    /**
     * Validate file for AWS upload
     * @param mixed $file
     * @return array
     */
    function validate_aws_file($file): array
    {
        $config = config('AWS');
        $result = ['valid' => true, 'errors' => []];
        
        if (!$file || !$file->isValid()) {
            $result['valid'] = false;
            $result['errors'][] = 'Invalid file';
            return $result;
        }
        
        // Check file size
        if ($file->getSize() > $config->s3['maxFileSize']) {
            $result['valid'] = false;
            $result['errors'][] = 'File size exceeds limit of ' . format_file_size($config->s3['maxFileSize']);
        }
        
        // Check MIME type
        if (!in_array($file->getClientMimeType(), $config->s3['allowedMimeTypes'])) {
            $result['valid'] = false;
            $result['errors'][] = 'File type not allowed: ' . $file->getClientMimeType();
        }
        
        return $result;
    }
}
