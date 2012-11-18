<?php
namespace Snake\Package\Spam;

USE \Snake\Libs\Cache\Memcache AS Memcache;

class SpamRegister {
	
	private $cache = NULL;
	private $clientIp = 0;

	
	const KEY_NO_REGISTER = 'no_register'; //确定spam前缀
	const KEY_SPAM_REGISTER_COUNT = 'spam_register_counter'; //注册次数前缀
	const COUNT_MAX_REGISTER = 4; //尝试次数-1
	const TIME_MAX_REGISTER = 300; //尝试间隔
	const TIME_REGISTER_BAN = 1800; //ip禁止注册时间

	public function __construct() {
		$this->cache = Memcache::instance();
	}

	public function setClientIp($ip) {
		$this->clientIp = $ip;
	}

	public function getClientIp() {
		return $this->clientIp;
	}

	/**
	 * 注册
	 */
	public function registerSpam() {
		$noRegister = $this->cache->get(self::KEY_NO_REGISTER . $this->getClientIp());	
		if (!empty($noRegister)) {
			$log = new \Snake\Libs\Base\SnakeLog('spamip', 'normal');
			$log->w_log(print_r(array('ip' => $this->getClientIp()), true));
			//TODO
			return FALSE;
		}
		$registerCount = $this->cache->increment(self::KEY_SPAM_REGISTER_COUNT  . $this->getClientIp());
		if (empty($registerCount)) {
			$this->cache->set(self::KEY_SPAM_REGISTER_COUNT  . $this->getClientIp(), 1, self::TIME_MAX_REGISTER);
		}
		elseif ($registerCount > self::COUNT_MAX_REGISTER) {
			$this->cache->delete(self::KEY_SPAM_REGISTER_COUNT  . $this->getClientIp());	
			$this->cache->set(self::KEY_NO_REGISTER . $this->getClientIp(), 1, self::TIME_REGISTER_BAN);
		}
		return TRUE;
	}
}
