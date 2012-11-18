<?php

namespace Snake\Package\Qzone\SpecialOfferWear;

use \Snake\Package\Qzone\DBQzoneActivity;


class TimeLimiedSpecialOffer {
    
    const pageSize = 10000;

    public function __construct() {

    }   

    public function getTwitterIds() {
        $popularObj = new Popular();
        $popularObj->setData('hot', 0, self::pageSize);
        $popularObj->setTids();
        $popularObj->setTotalNum();
        $tids = $popularObj->getTids();
        return $tids;
        //$totalNum = $popularObj->getTotalNum();
    }   




}
