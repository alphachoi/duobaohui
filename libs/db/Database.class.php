<?php
namespace Snake\Libs\DB;

use \Snake\Libs\DB\SQLMonitor;

class Database {

	const MASTER  = 0;
	const SLAVE   = 1;

	/**
	 * Singleton.
	 */
	public static function getConn($database) {
		static $singletons = array();
		!isset($singletons[$database]) && $singletons[$database] = new Database($database);
		return $singletons[$database];
	}

	/**
	 * Write data into database.
	 *
	 * @param string $sql
	 * @param array $params
	 * @return unknown_type
	 */
	public function write($sql, $params = array()) {
		$sth = $this->prepare($sql, $params, self::MASTER);
		$success = $this->catchError($sth, $sql, $params);
		$this->last_sth = $sth;

		if ($success === FALSE) {
			return FALSE;
		}

		return $this->getAffectedRows();
	}

	/**
	 * Read data from database. 
	 *
	 * @param string $sql SQL query statement
	 * @param array $params bind variable
	 * @param bool $from_master TRUE to query from master and FALSE to query from slave
	 * @param string $hash_key key the result by the specified field
	 */
	public function read($sql, $params = array(), $from_master = FALSE, $hash_key = NULL) {
		$type = $from_master ? self::MASTER : self::SLAVE;
		$sth = $this->prepare($sql, $params, $type);
		$success = $this->catchError($sth, $sql, $params);
		$result = array();
		while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			if (isset($hash_key) && !empty($hash_key)) {
				$result[$row[$hash_key]]= $row;
			}
			else {
				$result[]= $row;
			}
		}
		$sth->closeCursor();

		return $result;
	}

	/**
	 * Get number of affected rows in previous MySQL operation.
	 */
	public function getAffectedRows() {
		return $this->last_sth->rowCount();
	}

	public function getInsertId() {
		$connection = $this->getConnection(self::MASTER);
		return $connection->lastInsertId();
	}

	//////////////
	// privates //
	//////////////

	private $config; 
	private $in_transaction;
	private $last_sth; //@TODO: sort of an ugly hack for retrieving affected_rows
	protected $db_error_log; 
	private $db_conf = array(); //the selected database conf

	/**
	 * Store PDO connections for reusing. This is an array holds different
	 * types (MASTER or SLAVE) of connections.
	 *
	 * @var array
	 */
	private $connection = array();

	/**
	 * Private constructor. Load database config.
	 */
	private function __construct($database) {
		$this->config = \Snake\Libs\Base\Config::load('MySQL')->$database;
		$this->in_transaction = FALSE;
		$this->last_sth = NULL;
		$this->db_error_log = new \Snake\Libs\Base\SnakeLog('db_error', 'normal');
	}

	/**
	 * Get a connection for writing data.
	 *
	 * @param int $type Database::MASTER or Database::SLAVE
	 */
	private function getConnection($type) {
		if (isset($this->connection[$type])) {
			return $this->connection[$type];
		}

		switch ($type) {
			case self::MASTER:
				$conf = $this->config['MASTER'];
				$this->db_conf[self::MASTER] = $conf;
				break;

			case self::SLAVE:
			default:
				$ran = array_rand($this->config['SLAVES']);
				$conf = $this->config['SLAVES'][$ran];
				$this->db_conf[self::SLAVE] = $conf;
				break;
		}
		
		try {
			$this->connection[$type] = new PDO($conf['HOST'], $conf['DB'], $conf['USER'], $conf['PASS'], $conf['PORT']);
			$this->connection[$type]->exec("SET NAMES utf8");
		}
		catch (\PDOException $e) {
			$error = 'first:' . $type . json_encode($conf) . $e->getMessage();
			$this->db_error_log->w_log($error);
			if ($type == self::MASTER) {
				return FALSE;
			}

			$count = count($this->config['SLAVES']);
			$start = rand(0, $count - 1);
			for($i = $start; $i < $count + $start; $i++) {
				$slave = ($i >= $count) ? ($i-$count) : $i;
				$slaveConf = $this->config['SLAVES'][$slave];
				if ($slaveConf['HOST'] == $conf['HOST'] && $slaveConf['PORT'] == $conf['PORT']) {
					continue;
				}
				$this->db_conf[self::SLAVE] = $slaveConf;
				try {
					$this->connection[$type] = new PDO($slaveConf['HOST'], $slaveConf['DB'], $slaveConf['USER'], $slaveConf['PASS'], $slaveConf['PORT']);
					$this->connection[$type]->exec("SET NAMES utf8");
				}
				catch (\PDOException $e) {
					$error = 'foreach:' . $type . json_encode($conf) . $e->getMessage();
					$this->db_error_log->w_log($error);
					continue;
				}
				break;
			}
		}

		return isset($this->connection[$type]) ?  $this->connection[$type] : null;
	}

	/**
	 * Prepares a statement for execution and returns a statement object.
	 *
	 * @param string $sql sql statement
	 * @param array $params parameters used in sql statement
	 * @param int $op database operation type
	 */
	private function prepare($sql, $params, $type) {
		$connection = $this->getConnection($type);

		$sql_monitor = SQLMonitor::getMonitor();
		$sql_monitor->start($sql, $params);

		$sth = $connection->prepare($sql, array(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => FALSE));
		if (!empty($params)) {
			foreach ($params AS $key => $value) {
				if (strpos($key, '_') === 0) {
					$sth->bindValue(":{$key}", $value, PDO::PARAM_INT);
				}
				else {
					$sth->bindValue(":{$key}", $value, PDO::PARAM_STR);
				}
			}
		}
		$sth->execute();

		switch ($type) {
			case self::MASTER:
				$conf = $this->db_conf[self::MASTER];
				break;

			case self::SLAVE:
			default:
				$conf = $this->db_conf[self::SLAVE];
				break;
		}

		$sql_monitor->finish($sth, $conf);

		return $sth;
	}


	/**
	 * Catch database error.
	 * http://www.php.net/manual/en/pdostatement.errorinfo.php
	 *
	 * @param PDOStatement $sth
	 */
	private function catchError($sth, $sql = '', $params = '') {
		list($sql_state, $error_code, $error_message) = $sth->errorInfo();

		if ($sql_state == '00000') {
			return TRUE;
		}

		// rollback if in a transaction
		$this->rollback();
	}

	private function rollback() {
		if ($this->in_transaction) {
			$connection = $this->getConnection(self::MASTER);
			$connection->rollback();
			$this->in_transaction = FALSE;
		}
	}

}
