<?php
namespace Snake\Modules\Qzone;

use \Snake\Package\Qzone\Special\TheBestOfToday;
use \Snake\Package\Qzone\Special\DBSpecialOfferWear;
use \Snake\Package\Qzone\Helper\DBQzoneActivityHelper;
use \Snake\Package\Qzone\SpecialOfferWearEveryDay;


class Today_list extends \Snake\Libs\Controller {

    public function run() {
        $helper = new SpecialOfferWearEveryDay();

		$infos = $helper->getTodayData();
		$this->view = $infos;
        return TRUE;
    }   
}
