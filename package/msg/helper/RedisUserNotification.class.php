<?php
namespace Snake\Package\Msg\Helper;

class RedisUserNotification extends \Snake\Libs\Redis\Redis {
	static $prefix = "UserNotification";
	protected $id;
    protected $key;
    protected $fields;

    public function __construct($fields = array()) {
        $this->fields = $fields;
    }

    public function __set($field, $value) {
        $this->fields[$field] = $value;
    }

    public function __get($field) {
        if (isset($this->fields[$field])) {
            return $this->fields[$field];
        }
        return NULL;
    }

    public function getFields() {
        return $this->fields;
    }

    public function setFields($fields) {
        $this->fields = $fields;
    }

	public static function incr($id, $field, $value = 1) {
        if (empty($field) || empty($value)) {
            return FALSE;
        }
        return self::hIncrBy($id, $field, $value);
    }

	//TBC huazhulin 
    public static function getById($id) {
        $prefix = self::getPrefix();
        $class = get_called_class();
        $key = "{$prefix}:{$id}";
        $data = self::getRedis($key)->hGetAll($key);
        $object = new $class($data);
        return $object;
    }  
	
	/**
	 * check if data of specified id already exists
	 */
	public static function hasId($id) {
		$prefix = self::getPrefix();
		$key = "$prefix:$id";
		return self::getRedis($key)->exists($key);
	}

	public static function update($id, $field, $value) {
		if (empty($field) || empty($value)) {
			return FALSE;
		}	
		return self::hSet($id, $field, $value);
	}
}
