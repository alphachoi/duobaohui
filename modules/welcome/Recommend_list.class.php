<?php
namespace Snake\Modules\Welcome;

use \Snake\Package\Welcome\Recommendlist;
use \Snake\libs\Cache\Memcache;

class Recommend_list extends \Snake\Libs\Controller {
	
	public function run() {
		$cache = Memcache::instance();
		$cacheKey = MEILISHUO_URL . '/wlcrecommendlist';
		$sections = $cache->get($cacheKey);
		if (empty($sections)) {
			$HeadSection = new Recommendlist();
			$sections = $HeadSection->getSection();
			$cache->set($cacheKey, $sections, 3600);
		}

		$this->view = $sections;
	}

}
