<?php
namespace Snake\Modules\Welcome;

use \Snake\Package\Welcome\AttrWordsSection;
use \Snake\libs\Cache\Memcache;

class Attr_words extends \Snake\Libs\Controller {
	
	public function run() {
		$cache = Memcache::instance();
		$cacheKey = MEILISHUO_URL . '/wlcattrwords';
		$sections = $cache->get($cacheKey);
		if (empty($sections)) {
			$AttrSection = new AttrWordsSection();
			$sections = $AttrSection->getSections();
			$cache->set($cacheKey, $sections, 86400);
		}
		$this->view = $sections;
	}

}
