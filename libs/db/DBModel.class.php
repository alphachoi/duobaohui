<?php
namespace Snake\Libs\DB;
abstract class DBModel {
    protected static $instances = array();
    public static function getConn() {
        $class = get_called_class();
		$database = $class::_DATABASE_;
        if (empty(self::$instances)) {
			self::$instances[$database] = \Snake\Libs\DB\Database::getConn($database);
        }

        return self::$instances[$database];
    }

}
