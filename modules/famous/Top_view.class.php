<?php
namespace Snake\Modules\Famous;

/**
 * 达人页面 顶部置顶达人
 * 2012-10-21后停止使用
 * @author yishuliu@meilishuo.com
 */

Use \Snake\Package\Famous\FamousUser;			
Use \Snake\Package\User\UserRelation;
Use \Snake\Libs\Cache\Memcache;

class Top_view extends \Snake\Libs\Controller {
	private $userId = NULL;
	private $cache = FALSE;

	public function run()  {
		$this->_init();

		$params = array('data_type' => 1, 'page_type' => 201);
		$col = 'data_id, imgurl';
		$topModels = array();
		$topModels = FamousUser::getInstance()->getCmsShowList($params, $col);
		$topModelUids = \Snake\Libs\Base\Utilities::DataToArray($topModels, 'data_id');

        $cacheHelper = Memcache::instance();
        $uidsCache = md5(implode(',', $topModelUids));
        $cacheKey = "Famous:Top_view:{$uidsCache}";
        $response = $cacheHelper->get($cacheKey);
		if ($this->cache && empty($this->userId) && !empty($response)) {
			$this->view = $response;
			return;
		}	
		else {
			$followIds = array();
			if (!empty($this->userId)) {
				foreach ($topModels as $key => $value) {
					$topModels[$key]['followbyme'] = 0;
					//解决互相关注点击取消后变成已关注问题
					$result = UserRelation::getInstance()->checkUsersIfFollow($this->userId, $value['data_id'], TRUE);
					if ($result == 1) {
						$topModels[$key]['followbyme'] = 1;
					}   
					elseif ($result == 2) {
						$topModels[$key]['followbyme'] = 2;
					}
					$topModels[$key]['imgurl'] = \Snake\Libs\Base\Utilities::getPictureUrl($topModels[$key]['imgurl'], $type = "_o");
					//$topModels[$key]['imgurl'] = $imageStg->getWebsiteImageUrl($topModels[$key]['imgurl']);
				}
			}
			else {
				foreach ($topModels as $key => $value) {
					$topModels[$key]['followbyme'] = 0;
					$topModels[$key]['imgurl'] = \Snake\Libs\Base\Utilities::getPictureUrl($topModels[$key]['imgurl'], $type = "_o");
					//$topModels[$key]['imgurl'] = $imageStg->getWebsiteImageUrl($topModels[$key]['imgurl']);
				}
				$cacheHelper->set($cacheKey, $topModels, 3600);
			}
			//print_r($topModels);die;
			$this->view = $topModels;
		}
	}
	
	private function _init() {
		//current login userId
		$this->userId = $this->userSession['user_id'];
	}
}
