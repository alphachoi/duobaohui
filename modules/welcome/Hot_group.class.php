<?php
namespace Snake\Modules\Welcome;

use \Snake\Package\Welcome\Hotgroup;
use \Snake\libs\Cache\Memcache;

class Hot_group extends \Snake\Libs\Controller {
	
	public function run() {
		$cache = Memcache::instance();
		$cacheKey = MEILISHUO_URL . '/wlchotgroup';
		$sections = $cache->get($cacheKey);
		if (empty($sections)) {
			$hotGroup = new Hotgroup();
			$sections = $hotGroup->getHotGroup();
			$cache->set($cacheKey, $sections, 3600);
		}

		$this->view = $sections;
	}

}
