<?php
namespace Snake\Modules\Famous;

/**
 * 达人页面 侧边栏本周小红心榜
 * @author yishuliu@meilishuo.com
 */

Use \Snake\Package\Famous\FamousUser;			
Use \Snake\Package\Medal\Medal;			
Use \Snake\Package\User\TopMm;			
Use \Snake\Libs\Cache\Memcache;

class Heart_list extends \Snake\Libs\Controller {
	private $userId = NULL;
	private $cache = TRUE;

	public function run()  {
		$this->_init();

		$result = array();
		$topMms = TopMm::getInstance()->getCurrentTopMm('month', 0, 10);

		if (empty($topMms)) {
			$this->view = array();
			return;
		}

		$uids = \Snake\Libs\Base\Utilities::DataToArray($topMms, 'user_id');
		$cacheHelper = Memcache::instance();
		$uidsCache = md5(implode(',', $uids));
		$cacheKey = "Famous:Heart_list:{$uidsCache}";
		$response = $cacheHelper->get($cacheKey);
		
		if ($this->cache && !empty($response)) {
			$this->view = $response;
		}
		else {
			$result = array();
			foreach ($topMms as $key => $value) {
				$result[$key]['user_id'] = $topMms[$key]['user_id'];
				$result[$key]['nickname'] = $topMms[$key]['nickname'];
				$result[$key]['avatar_c'] = $topMms[$key]['avatar'];
				$result[$key]['share_number'] = $topMms[$key]['share_number'];
				$result[$key]['heart_number'] = $topMms[$key]['heart_number'];
				$result[$key]['up'] = $topMms[$key]['up'];
				$result[$key]['key'] = $topMms[$key]['key'];
				$result[$key]['top_three'] = !empty($topMms[$key]['top_three']) ? $topMms[$key]['top_three'] : 0;
			}
			$cacheHelper->set($cacheKey, $result, 3600);
			//print_r($result);die;
			$this->view = $result;
		}
	}
	
	private function _init() {
		//current login userId
		$this->userId = $this->userSession['user_id'];
	}
}
