<?php
namespace Snake\Libs\DB;

class PDO extends \PDO {
	public function __construct($host, $db, $user, $pass, $port = 3306) {
		$dsn ="mysql:dbname={$db};host={$host};port={$port};";

		$options = array(
			PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => TRUE,
		);

		if (!isset($_SERVER['PATH']) || !preg_match('/windows/i', $_SERVER['PATH'])) {
			$options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES 'utf8';";
		}
		
		parent::__construct($dsn, $user, $pass, $options);
	}
}

