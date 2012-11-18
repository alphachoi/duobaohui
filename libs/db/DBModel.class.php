<?php
namespace Snake\Libs\DB;

abstract class DBModel {
    protected static $instances = array();

    public static function getConn() {
        $class = get_called_class();
		$database = $class::_DATABASE_;

        if (is_null($instances[$class])) {
            $instances[$database] = \Snake\Libs\DB\Database::getConn($database);
        }

        return $instances[$database];
    }

}
