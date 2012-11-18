<?php
namespace Snake\Modules\Qzone;

use \Snake\Package\Qzone\Special\TheBestOfToday;
use \Snake\Package\Qzone\Special\DBSpecialOfferWear;
use \Snake\Package\Qzone\Helper\DBQzoneActivityHelper;
use \Snake\Package\Qzone\SpecialOfferWearEveryDay;


class Specialoffer_list extends \Snake\Libs\Controller {

    private $offset = 0;
    private $limit = 33;
    public function run() {
		$this->_init();
        $helper = new SpecialOfferWearEveryDay();
        $infos = $helper->getSpecialOfferData($this->offset, $this->limit);
        $this->view = $infos;
        return TRUE;
    }

    private function _init() {
        if (isset($this->request->REQUEST['frame']) && !empty($this->request->REQUEST['frame'])) {
            $this->offset = $this->limit * $this->request->REQUEST['frame'];
        }
        return TRUE;
    }
}
