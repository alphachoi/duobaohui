<?php
namespace Snake\Modules\Home;

Use \Snake\Package\Home\Home					 AS Home;
Use \Snake\Package\Recommend\Recommend			 AS Recommend;
use \Snake\libs\Cache\Memcache                   AS Memcache;
Use \Snake\Package\Home\HomeFactory				 AS HomeFactory;
Use \Snake\Package\User\User                     AS User;
use \Snake\Package\Group\GroupSquare			 AS GroupSquare;
use \Snake\Package\Group\Groups			 		 AS Groups;

class Home_recommends extends \Snake\Libs\Controller {

    private $userId = NULL;
    
    public function run() {
		if (!$this->setUserId()) {
			$this->errorMessage(400, 'empty user_id!');
			return FALSE;
		}  

		$cacheHelper = Memcache::instance();
        $cacheKeyForRecom = 'CacheKey:Home_recommends_groupInfo:' . $this->userId;
        $responseRecomData = $cacheHelper->get($cacheKeyForRecom);
		
		if (!empty($responseRecomData)) {
			$this->view = $responseRecomData;
			return TRUE;
		}
		else {
			//$this->userId = 765;
			$gIds = array();
			$recomObj = new Recommend();
			$gIds = $recomObj->getReGroupByUid($this->userId);
			//$gIds = array( 4, 3);
			if (empty($gIds)) {
				$key = 0;
				$groupInfo = array($key => array(
					'group_id' => $key,
					'name' => FALSE,
					'count_member' => FALSE,
					'header_path' => FALSE,
					'num' => FALSE,
					'is_follower' => FALSE,
					//'picture_url' => array(0 => FALSE),
					'mixpic' => FALSE
				));
				self::setError(400, 40022, 'empty group_ids');
				$this->view = $groupInfo;
				return TRUE;
			}
			//$gids = $recomObj->getReGroupByUid(765);

			//根据group ids得到9图合一的杂志社posters
			$groupHelper = new Groups();
			$groupInfo = $groupHelper->getGroupSquareInfo($gIds, $this->userId);
			$cacheHelper->set($cacheKeyForRecom, $groupInfo, 3600);

			$this->view = $groupInfo;
			return TRUE;
		}
    }   

    private function setUserId() {
        $this->userId = $this->userSession['user_id'];
        return TRUE;
    }    
    
    private function getUserId() {
        return $userId;
	}

	private function errorMessage($code, $message) {
		self::setError(400, $code, $message);
        return TRUE;
    }   
}
