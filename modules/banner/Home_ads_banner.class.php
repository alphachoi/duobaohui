<?php
namespace Snake\Modules\Banner;

use \Snake\libs\Cache\Memcache AS Memcache;
use \Snake\Package\Banner\TopBanner AS TopBanner;

class Home_ads_banner extends \Snake\Libs\Controller {

	public function run() {
		$this->main();
	}

	public function main() {
		$mem = Memcache::instance();
		$cacheKey = "HomeTopBanner";

		//memcache保存banner 一天
    	//$topBannerInfo = $mem->get($cacheKey);    
        if (empty($topBannerInfo)) {
			$tb = new TopBanner();
            $topBannerInfo = $tb->getTopbannerTimeInterval(time(), 12);
            foreach($topBannerInfo as $key => &$value) {
                $value['imgurl'] = $this->_convertAvaterUrl($value['imgurl']);
                $value['linkurl'] = $value['link'];
            }   
            //$cache->set($cacheKey, $topBannerInfo, 12 * 3600);
        }   
		$this->view = $topBannerInfo;
		return TRUE;
	}

	/** 
     * 转换头像地址为URL
     * @param string $picPath
     * @return string $avatarUrl
     **/
    private function _convertAvaterUrl($key) {
        $key = trim($key);
        if(empty($key)){
            return  AVATAR_URL . '/css/images/0.gif';
        }   

        if($key[0] == '/'){
            return AVATAR_URL . $key;
        }   
        return  AVATAR_URL . '/' . $key;
    }   
}
