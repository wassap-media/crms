<?php

namespace Config;

use CodeIgniter\Database\Config;

/**
 * Database Configuration
 */
class Database extends Config
{
	/**
	 * The directory that holds the Migrations
	 * and Seeds directories.
	 *
	 * @var string
	 */
	public $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;

	/**
	 * Lets you choose which connection group to
	 * use if no other is specified.
	 *
	 * @var string
	 */
	public $defaultGroup = 'default';

	/**
	 * The default database connection.
	 *
	 * @var array
	 */
	public $default = [
		'DSN'      => '',
		'hostname' => '',
		'username' => '',
		'password' => '',
		'database' => WRITEPATH . 'database.db',
		'DBDriver' => 'SQLite3',
		'DBPrefix' => '',
		'pConnect' => false,
		'DBDebug'  => (ENVIRONMENT !== 'production'),
		'charset'  => 'utf8',
		'DBCollat' => 'utf8_general_ci',
		'swapPre'  => '',
		'encrypt'  => false,
		'compress' => false,
		'strictOn' => false,
		'failover' => [],
		'port'     => 3306,
	];

	/**
	 * This database connection is used when
	 * running PHPUnit database tests.
	 *
	 * @var array
	 */
	public $tests = [
		'DSN'      => '',
		'hostname' => '127.0.0.1',
		'username' => '',
		'password' => '',
		'database' => ':memory:',
		'DBDriver' => 'SQLite3',
		'DBPrefix' => 'db_',  // Needed to ensure we're working correctly with prefixes live. DO NOT REMOVE FOR CI DEVS
		'pConnect' => false,
		'DBDebug'  => (ENVIRONMENT !== 'production'),
		'charset'  => 'utf8',
		'DBCollat' => 'utf8_general_ci',
		'swapPre'  => '',
		'encrypt'  => false,
		'compress' => false,
		'strictOn' => false,
		'failover' => [],
		'port'     => 3306,
	];

	//--------------------------------------------------------------------

	public function __construct()
	{
		parent::__construct();

		// For development, use SQLite by default
		if (ENVIRONMENT === 'development') {
			// Ensure the writable directory exists
			if (!is_dir(WRITEPATH)) {
				mkdir(WRITEPATH, 0755, true);
			}
		}

		// Override database configuration from environment variables (Render/AWS)
		$envHostname = getenv('RDS_ENDPOINT') ?: getenv('database.default.hostname');
		$envUsername = getenv('RDS_USERNAME') ?: getenv('database.default.username');
		$envPassword = getenv('RDS_PASSWORD') ?: getenv('database.default.password');
		$envDatabase = getenv('RDS_DATABASE') ?: getenv('database.default.database');
		$envPort     = getenv('RDS_PORT') ?: getenv('database.default.port');

		// Only use MySQL/RDS if environment variables are set
		if ($envHostname && $envUsername && $envDatabase) {
			$this->default['DBDriver'] = 'MySQLi';
			$this->default['hostname'] = $envHostname;
			$this->default['username'] = $envUsername;
			$this->default['password'] = $envPassword ?: '';
			$this->default['database'] = $envDatabase;
			$this->default['port'] = $envPort ? (int) $envPort : 3306;
		}

		// Ensure that we always set the database group to 'tests' if
		// we are currently running an automated test suite, so that
		// we don't overwrite live data on accident.
		if (ENVIRONMENT === 'testing')
		{
			$this->defaultGroup = 'tests';
		}
	}

	//--------------------------------------------------------------------

}
