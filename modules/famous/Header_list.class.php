<?php
namespace Snake\Modules\Famous;

/**
 * 达人页面 顶部置顶达人
 * @author yishuliu@meilishuo.com
 */

Use \Snake\Package\Famous\FamousUser;			
Use \Snake\Package\User\UserRelation;
Use \Snake\Libs\Cache\Memcache;

class Header_list extends \Snake\Libs\Controller {
	private $userId = NULL;
	private $type = NULL;

	public function run()  {
		$this->_init();
		
		$topDatas = FamousUser::getInstance()->getHeaderRecommendList($this->type);
		//print_r($topDatas);die;
		if (empty($topDatas)) {
			$this->view = array();
			return;
		}
		$this->view = $topDatas;
		return TRUE;
	}
	
	private function _init() {
        if (!$this->setLimit()) {
            return FALSE;
        }   
		//current login userId
	}

    private function setLimit() {
        $this->type = !empty($this->request->REQUEST['limit']) ? $this->request->REQUEST['limit'] : 4; 
        if (empty($this->type) || !is_numeric($this->type) || $this->type <= 0) {
            $this->setError(400, 40301, 'limit num is illeage');
            return FALSE;
        }   
        return TRUE;
    }   
}
