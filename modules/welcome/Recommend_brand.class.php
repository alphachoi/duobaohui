<?php
namespace Snake\Modules\Welcome;

use \Snake\Package\Welcome\Recommendbrand;
use \Snake\libs\Cache\Memcache;

class Recommend_brand extends \Snake\Libs\Controller {
	
	public function run() {
		$cache = Memcache::instance();
		$cacheKey = MEILISHUO_URL . '/wlcrecommendbrand';
		$sections = $cache->get($cacheKey);
		if (empty($sections)) {
			$recommendBrand = new Recommendbrand();
			$sections = $recommendBrand->getBrands();
			$cache->set($cacheKey, $sections, 3600);
		}
		$this->view = $sections;
	}

}
