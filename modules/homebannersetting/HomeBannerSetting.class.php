<?php
namespace Snake\Modules\Homebannersetting;

use \Snake\libs\Cache\Memcache AS Memcache;
use \Snake\Package\Banner\TopBanner AS TopBanner;

class HomeBannerSetting extends \Snake\Libs\Controller {

	
	public function run() {
		$this->main();
	}

	public function main() {
		$mem = Memcache::instance();
		$cacheKey = "HomepageTopBannerForSnake";
		$result = $mem->get($cacheKey);
		$currentTime = $_SERVER['REQUEST_TIME'];
		$deadline = $_SERVER['REQUEST_TIME'] + 10 * 60;
		if (!empty($result)) {
			$this->view = $result;
		}
		else {
			$tb = new TopBanner();
            $topBannerInfo = $tb->getTopBannerInfobyTime($currentTime, $deadline);
            if (empty($topBannerInfo)) {
				$this->view = NULL;
                //使用默认模版
                return ;
            }   
            elseif (is_array($topBannerInfo)) {
                /*foreach($topBannerInfo as $key => &$value) {
                    //banner链接
                    //$value['banner_linkurl'] = ImageLogic::getPictureUrl($value['banner_linkurl']);
                    //背景图片链接
                    //$value['background_pic'] = ImageLogic::getPictureUrl($value['background_pic']);
                    $value['lifeTime'] = $value['end_date'] - $value['start_date'];
                }*/  
                $topBannerInfo = array_shift($topBannerInfo);
                $topBannerInfo['lifeTime'] = strtotime($topBannerInfo['end_date']) - strtotime($topBannerInfo['start_date']);
                $mem->set($cacheKey, $topBannerInfo, $topBannerInfo['lifeTime']);
				$this->view = $topBannerInfo;
            }
 		}  	
		return ;
	}
}
