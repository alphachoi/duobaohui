<?php
namespace Snake\Modules\Famous;

/**
 * 达人页面 侧边栏本周小红心榜 本周勋章之星
 * @author yishuliu@meilishuo.com
 */

Use \Snake\Package\Famous\FamousUser;			
Use \Snake\Package\Medal\Medal;			
Use \Snake\Package\User\TopMm;			
Use \Snake\Libs\Cache\Memcache;

class Side_view extends \Snake\Libs\Controller {
	private $userId = NULL;
	private $cache = FALSE;

	//TODO
	public function run()  {
		$this->_init();

        $cacheHelper = Memcache::instance();
        $cacheKey = "Famous:Side_view:";
        $response = $cacheHelper->get($cacheKey);
        if ($this->cache && !empty($response)) {
            $this->view = $response;
        }   
		else {
			$result = array();
			$topMms = TopMm::getInstance()->getCurrentTopMm('month', 0, 10);
			$fabao = array();
			$fabao['user_id'] = $topMms[0]['user_id'];
			$fabao['nickname'] = $topMms[0]['nickname'];
			$fabao['avatar_c'] = $topMms[0]['avatar'];
			$fabao['icon'] = \Snake\Libs\Base\Utilities::convertPicture('css/images/medal/icons/small_w3.png');
			$fabao['medal_id'] = 42; 
			$fabao['wording'] = '每周发宝之星';

			$medalHelper = new Medal();
			$zhongcaoMm = $medalHelper->getMedalGotMms(41, 0, 1); 
			$zhongcaoMm = array_values($zhongcaoMm);
			$zhongcao = array();
			$zhongcao['user_id'] = $zhongcaoMm[0]['user_id'];
			$zhongcao['nickname'] = $zhongcaoMm[0]['nickname'];
			$zhongcao['avatar_c'] = $zhongcaoMm[0]['avatar_c'];
			$zhongcao['icon'] = \Snake\Libs\Base\Utilities::convertPicture('css/images/medal/icons/small_w2.png');
			$zhongcao['medal_id'] = 41; 
			$zhongcao['wording'] = '每周种草女王';

			$haoqiMm = $medalHelper->getMedalGotMms(40, 0, 1); 
			$haoqiMm = array_values($haoqiMm);
			$haoqi = array();
			$haoqi['user_id'] = $haoqiMm[0]['user_id'];
			$haoqi['nickname'] = $haoqiMm[0]['nickname'];
			$haoqi['avatar_c'] = $haoqiMm[0]['avatar_c'];
			$haoqi['icon'] = \Snake\Libs\Base\Utilities::convertPicture('css/images/medal/icons/small_w1.png');
			$haoqi['medal_id'] = 40; 
			$haoqi['wording'] = '好奇宝宝';

			$haoxinMm = $medalHelper->getMedalGotMms(43, 0, 1); 
			$haoxinMm = array_values($haoxinMm);
			$haoxin = array();
			$haoxin['user_id'] = $haoxinMm[0]['user_id'];
			$haoxin['nickname'] = $haoxinMm[0]['nickname'];
			$haoxin['avatar_c'] = $haoxinMm[0]['avatar_c'];
			$haoxin['icon'] = \Snake\Libs\Base\Utilities::convertPicture('css/images/medal/icons/small_w4.png');
			$haoxin['medal_id'] = 43; 
			$haoxin['wording'] = '热血好姐妹';

			$zhidaoMm = $medalHelper->getMedalGotMms(44, 0, 1); 
			$zhidaoMm = array_values($zhidaoMm);
			$zhidao = array();
			$zhidao['user_id'] = $zhidaoMm[0]['user_id'];
			$zhidao['nickname'] = $zhidaoMm[0]['nickname'];
			$zhidao['avatar_c'] = $zhidaoMm[0]['avatar_c'];
			$zhidao['icon'] = \Snake\Libs\Base\Utilities::convertPicture('css/images/medal/icons/small_w5.png');
			$zhidao['medal_id'] = 44; 
			$zhidao['wording'] = '美丽我知道之星';

			$result = array();
			$result = array($fabao, $zhongcao, $haoqi, $haoxin, $zhidao);
			$cacheHelper->set($cacheKey, $result, 3600);
			//print_r($result);die;
			//对每个topModelUids,设置一个值判断是否follow,另外加一个值判断是否登录
			//对于未登录用户加cache,以四个人uid md5来作为memkey
			$this->view = $result;
		}
	}
	
	private function _init() {
		//current login userId
		$this->userId = $this->userSession['user_id'];
	}
	
	/**
	 *	cache handle
	 */
	private function _getFamousUserCache($user) {
		$cacheKey = 'person:label_' . $this->visitedUserId;
		$cacheHelper = Memcache::instance();
		$userInfo = $cacheHelper->get($cacheKey);
		if ($this->cache && !empty($userInfo)) {
			return $userInfo;
		}
		$userLabel = $user->getUserLabel($this->visitedUserId);
		if (empty($userLabel)) {
			//$this->setError(400, 40103, 'user is not exist or user label is empty');
			return FALSE;
		}
		$cacheHelper->set($cacheKey, $userLabel, 3600);
		return $userLabel;
	}
}
