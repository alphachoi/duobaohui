<?php
namespace Snake\Modules\Welcome;

use \Snake\Package\Welcome\Attrsection;
use \Snake\libs\Cache\Memcache;

class Attr_section extends \Snake\Libs\Controller {
	
	public function run() {
		$cache = Memcache::instance();
		$cacheKey = MEILISHUO_URL . '/wlcattrsection';
		$sections = $cache->get($cacheKey);
		if (empty($sections)) {
			$AttrSection = new Attrsection();
			$sections = $AttrSection->getSections();
			if (!empty($sections[0]['attrs'][0]['mix_pic'])) {
				$cache->set($cacheKey, $sections, 86400);
			}
		}
		$this->view = $sections;
	}

}
