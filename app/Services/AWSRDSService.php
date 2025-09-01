<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use Config\AWS as AWSConfig;

/**
 * AWS RDS Service for RISE CRM
 * Handles database connections and operations
 */
class AWSRDSService
{
    private $config;
    private $connection;

    public function __construct()
    {
        $this->config = config('AWS');
    }

    /**
     * Get RDS connection parameters
     * @return array
     */
    public function getConnectionParams(): array
    {
        return [
            'hostname' => $this->config->rds['endpoint'],
            'username' => $this->config->rds['username'],
            'password' => $this->config->rds['password'],
            'database' => $this->config->rds['database'],
            'port' => $this->config->rds['port'],
            'DBDriver' => 'MySQLi',
            'DBPrefix' => 'rise_',
            'pConnect' => false,
            'DBDebug' => (ENVIRONMENT !== 'production'),
            'charset' => 'utf8',
            'DBCollat' => 'utf8_general_ci',
            'swapPre' => '',
            'encrypt' => $this->config->rds['ssl'],
            'compress' => false,
            'strictOn' => false,
            'failover' => [],
            'ssl_ca' => $this->getSSLCA(),
            'ssl_verify' => true
        ];
    }

    /**
     * Test RDS connection
     * @return bool
     */
    public function testConnection(): bool
    {
        try {
            $params = $this->getConnectionParams();
            $db = \Config\Database::connect($params);
            
            if ($db->connect(false)) {
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            log_message('error', 'AWS RDS Connection Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get SSL CA certificate path
     * @return string|null
     */
    private function getSSLCA(): ?string
    {
        if ($this->config->rds['ssl']) {
            // Path to AWS RDS SSL certificate
            return APPPATH . 'Config/rds-ca-2019-root.pem';
        }
        
        return null;
    }

    /**
     * Get database status
     * @return array
     */
    public function getDatabaseStatus(): array
    {
        try {
            $params = $this->getConnectionParams();
            $db = \Config\Database::connect($params);
            
            $result = $db->query('SHOW STATUS');
            $status = [];
            
            foreach ($result->getResultArray() as $row) {
                $status[$row['Variable_name']] = $row['Value'];
            }
            
            return $status;
            
        } catch (\Exception $e) {
            log_message('error', 'AWS RDS Status Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get database variables
     * @return array
     */
    public function getDatabaseVariables(): array
    {
        try {
            $params = $this->getConnectionParams();
            $db = \Config\Database::connect($params);
            
            $result = $db->query('SHOW VARIABLES');
            $variables = [];
            
            foreach ($result->getResultArray() as $row) {
                $variables[$row['Variable_name']] = $row['Value'];
            }
            
            return $variables;
            
        } catch (\Exception $e) {
            log_message('error', 'AWS RDS Variables Error: ' . $e->getMessage());
            return [];
        }
    }
}
