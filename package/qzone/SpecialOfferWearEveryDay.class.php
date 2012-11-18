<?php

namespace Snake\Package\Qzone;

use \Snake\Package\Qzone\Special\TheBestOfToday;
use \Snake\Package\Qzone\Special\SpecailOffer;
use \Snake\Package\Qzone\Special\User;

class SpecialOfferWearEveryDay {
	
	public function getTodayData() {
		$cacheHelper = \Snake\Libs\Cache\Memcache::instance();
		$cacheKey = "1698845:SpecialOfferQzone:";
		$infos = $cacheHelper->get($cacheKey);
		if (!empty($infos)) {
			return $infos;
		}
		$helper = new TheBestOfToday();
		$helper->goodsInfoF();
		$infos = $helper->getPerfectInfos();
		return $infos;
	}

    public function getSpecialOfferData($offset = 0, $limit = 33) {
		$helper = new SpecailOffer();
		$helper->goodsInfoF($offset, $limit);
		$infos = $helper->getInfos();
		return $infos;
    }

	public function getUser($params, $time) {
		$userHelper = new User($parmas, $time);
		$userHelper->getUser();
	} 

	public function sgetUser() {

	}

}
