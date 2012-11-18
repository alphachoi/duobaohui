<?php
namespace Snake\Package\Group\Helper;

class RedisSnakeLog extends \Snake\Libs\Redis\Redis {
	static $prefix = "RedisLogQueue";
	static $config = TRUE;
}
 
