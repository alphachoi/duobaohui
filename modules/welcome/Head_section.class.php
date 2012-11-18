<?php
namespace Snake\Modules\Welcome;

use \Snake\Package\Welcome\Headsection;
use \Snake\libs\Cache\Memcache;

class Head_section extends \Snake\Libs\Controller {
	
	public function run() {
		$cache = Memcache::instance();
		$cacheKey = MEILISHUO_URL . '/wlcheadsection';
		$sections = $cache->get($cacheKey);
		if (empty($sections)) {
			$HeadSection = new Headsection();
			$sections = $HeadSection->getHeadSection();
			$cache->set($cacheKey, $sections, 3600);
		}

		$magic = rand(0, 1);
		foreach ($sections as $key => $picture) {
			if (($key % 2) == $magic) {
				unset($sections[$key]);
			}
		}
		$sections = array_values($sections);
		shuffle($sections);
		$this->view = $sections;
	}

}
