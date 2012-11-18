<?php
namespace Snake\Package\Msg\Helper;

class RedisUserPrivateMsg extends \Snake\Libs\Redis\Redis {
	static $prefix = "PrivateMessage";
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
		
	public static function updateConversationTimeline($from_uid, $to_uid, $timestamp) {
	      $name = "{$from_uid}:conversation_timeline";
		  $prefix = self::getPrefix();
		  $list_name = "{$prefix}:{$name}";
          return self::getRedis($list_name)->zAdd($list_name, $timestamp, $to_uid);
	}
	/**
	 * get a user's private message timeline
	 * @return array user ids
	 */
	public static function getConversationTimeline($uid, $start = 0, $limit = 20, $reverse = TRUE, $with_timestamps = FALSE) {
		$name = "{$uid}:conversation_timeline";
		$prefix = self::getPrefix();
		$list_name = "{$prefix}:{$name}";
		if ($reverse) {
			return self::getRedis($list_name)->zRevRange($list_name, $start, $limit, $with_timestamps);
		}
		return self::getRedis($list_name)->zRange($list_name, $start, $limit, $with_timestamps);
	}
	/**
	 *get user`s private message length
	 */
	public static function countConversationTimeline($uid, $min = NULL, $max = NULL) {
		$name = "{$uid}:conversation_timeline";
		$prefix = self::getPrefix();
		$list_name = "{$prefix}:{$name}";
		is_null($min) && $min = '-inf';
		is_null($max) && $max = '+inf';
		return self::getRedis($list_name)->zCount($list_name, $min, $max);
	}
}
