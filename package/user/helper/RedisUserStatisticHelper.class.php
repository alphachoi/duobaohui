<?php
namespace Snake\Package\User\Helper;

class RedisUserStatisticHelper extends \Snake\Libs\Redis\Redis {
	protected static $prefix = 'UserStatistic';
	protected $id;
    protected $key;
 	protected $fields;

	public function __construct($id, $fields = array()) {
        $this->id = $id;
        $this->fields = $fields;
    }   
	
	public static function getUserStatistic($userId) {
		if (empty($userId)) {
			return FALSE;
		}
		$statistic = self::hGetAll($userId);
		return $statistic;
	}

	public static function update($userId, $field, $value) {
		if (empty($field) || empty($value) || empty($userId)) {
			return FALSE;
		}

		$result = self::hSet($userId, $field, $value);
        return $result;
	}

	public static function incr($userId, $field, $value = 1) {
		if (empty($field) || empty($value) || empty($userId)) {
			return FALSE;
		}
		return self::hIncrBy($userId, $field, $value);
	}

	public static function decr($userId, $field, $value = 1) {
		if (empty($field) || empty($value) || empty($userId)) {
			return FALSE;
		}
		return self::hIncrBy($userId, $field, 0 - $value);
	}

}
