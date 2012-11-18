<?php
namespace Snake\Modules\Welcome;

use \Snake\Package\Welcome\Hotactivity;
use \Snake\libs\Cache\Memcache;

class Hot_activity extends \Snake\Libs\Controller {
	
	public function run() {
		$cache = Memcache::instance();
		$cacheKey = MEILISHUO_URL . '/wlchotactivity';
		$sections = $cache->get($cacheKey);
		if (empty($sections)) {
			$hotActivity = new Hotactivity();
			$sections = $hotActivity->getActivity();
			$cache->set($cacheKey, $sections, 3600);
		}

		$this->view = $sections;
	}

}
