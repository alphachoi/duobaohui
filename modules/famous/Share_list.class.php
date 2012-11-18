<?php
namespace Snake\Modules\Famous;

/**
 * 达人页面 侧边栏达人分享之星，主编分享之星
 * @author yishuliu@meilishuo.com
 */

Use \Snake\Package\Famous\FamousUser;			
Use \Snake\Package\User\TopMm;			
Use \Snake\Libs\Cache\Memcache;
Use \Snake\Package\User\UserRelation;

class Share_list extends \Snake\Libs\Controller {
	private $userId = NULL;
	private $cache = FALSE;
	private $typeOptions = array('pinkV', 'editor');

	public function run()  {
		$this->_init();

		$topMms = TopMm::getInstance()->getCurrentTopFamousByType($this->type);
		$topMms = array_values($topMms);

        $cacheHelper = Memcache::instance();
        $cacheKey = "Famous:Sharelist:{$this->type}";
        $response = $cacheHelper->get($cacheKey);
        if ($this->cache && empty($this->userId) && !empty($response)) {
            $this->view = $response;
            return;
        }
		else {
			if (!empty($this->userId)) {
				foreach ($topMms as $key => $value) {
					$topMms[$key]['followbyme'] = 0;
					$topMms[$key]['self'] = 0;
					//解决互相关注点击取消后变成已关注问题
					$result = UserRelation::getInstance()->checkUsersIfFollow($this->userId, $value['user_id'], TRUE);
					if ($result == 1) {
						$topMms[$key]['followbyme'] = 1;
					}   
					elseif ($result == 2) {
						$topMms[$key]['followbyme'] = 2;
					}   
					if ($value['user_id'] == $this->userId) {
						$topMms[$key]['self'] = 1;
					}
					$topMms[$key]['key'] = $key + 1;
					$topMms[$key]['top_three'] = ($key < 3) ? 1 : 0;
				}
			}
			else {
				foreach ($topMms as $key => $value) {
					$topMms[$key]['followbyme'] = 0;
					$topMms[$key]['self'] = 0;
					$topMms[$key]['key'] = $key + 1;
					$topMms[$key]['top_three'] = ($key < 3) ? 1 : 0;
				}
			}
		}
		//print_r($topMms);die;
		$this->view = $topMms;
		return TRUE;
	}
	
	private function _init() {
        if (!$this->setType()) {
            return FALSE;
        }
		//current login userId
		$this->userId = $this->userSession['user_id'];
	}

    private function setType() {
        $type = !empty($this->request->REQUEST['type']) ? $this->request->REQUEST['type'] : ''; 
        if (!in_array($type, $this->typeOptions)) {
            $this->setError(400, 40112, 'type is illegal');
            return FALSE;
        }   
        $this->type = $type;
        return TRUE;
    }   
}
