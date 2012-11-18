<?php
namespace Snake\Libs\Base;

use \Snake\Libs\DB\SQLMonitor;
use \Snake\Libs\Redis\RedisMonitor;

class Scripts {

	protected $args = array();

	public function __construct($args = array()) {
		$this->args = $args;
		$sql_monitor = SQLMonitor::getMonitor();
		$sql_monitor->shutDownMonitor();
		$redis_monitor = RedisMonitor::getMonitor();
		$redis_monitor->shutDownMonitor();
	}
}
