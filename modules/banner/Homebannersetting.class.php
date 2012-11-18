<?php
namespace Snake\Modules\Banner;

use \Snake\libs\Cache\Memcache AS Memcache;
use \Snake\Package\Banner\TopBanner AS TopBanner;

class HomeBannerSetting extends \Snake\Libs\Controller {

	public function run() {
		$this->main();
	}

	public function main() {
		$mem = Memcache::instance();
		$cacheKey = 'HomepageTopBannerEdit';
		$result = $mem->get($cacheKey);
		$currentTime = $_SERVER['REQUEST_TIME'];
		$deadline = $_SERVER['REQUEST_TIME'] + 10 * 60;
		if (!empty($result['info'])) {
			$this->view = $result['info'];
		}
		else {
			$this->view = 0;
		}
		return ;
	}
}
