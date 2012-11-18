<?php
namespace Snake\Libs\Redis;

class RedisException extends \RedisExcetion {

	public function getExceptionMessage() {
		return self::$message;
	}
}
