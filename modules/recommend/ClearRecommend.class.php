<?php
namespace Snake\Modules\Recommend;

/**
 * author gstan
 * email guoshuaitan@meilishuo.com
 * date 2012-08-13
 * version 1.0
 */
 use Snake\Libs\Cache\Memcache;

 class ClearRecommend extends \Snake\Libs\Controller {
	private $user_id;
	private $num;
	public static $cachekey = 'MEILISHUO_RECOMMEND_';
	private static $cache;
	public function _init(){
		$this->num = isset($this->request->path_args[0]) ? $this->request->path_args[0] : 15;
		$this->user_id = $this->userSession['user_id'];
		if (!is_object(self::$cache)) {
			self::$cache =  Memcache::instance();
		}
	}
	public function run(){
		$this->_init();
		if (empty($this->user_id)) {
			$this->setError(404,400402, "no login in");
			return false;
		}
		$key = self::$cachekey . $this->user_id . "_" . $this->num;
        $status = self::$cache->set($key, 'empty', 3600 * 24);
		$this->view = $status;
	}	 
	 
}
