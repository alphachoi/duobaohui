<?php
namespace Snake\Modules\Famous;

/**
 * 达人页面 普通达人海报墙twitter数目
 * @author yishuliu@meilishuo.com
 */

Use \Snake\Package\Famous\FamousUser;			
Use \Snake\Libs\Cache\Memcache;
Use \Snake\Package\Picture\Picture;

class Main_view_total extends \Snake\Libs\Controller {
	private $userId = NULL;
	private $cache = FALSE;
	private $tab = NULL;
	private $page = NULL;
	private $total = 0;
    const maxFrame = FRAME_SIZE_MAX; //6
    const frameSize = WIDTH_PAGE_SIZE; //20
	
	private $tabTypes = array('all' => 0, 'jiepai' => 1, 'cosmetic' => 2, 'fashion' => 3, 'editor' => 4);

	//TODO
	public function run()  {
        if (!$this->_init()) {
            return FALSE;
        }
		if ($this->tab == 'all') {
			$user_type = 0;
		}
		else {
			$user_type = $this->tabTypes[$this->tab];
		}

		$this->total = FamousUser::getInstance()->getCmsListTotal($user_type);

		$response = array('totalNum' =>$this->total);
        $this->view = $response;
		//print_r($response);die;
	}
	
	private function _init() {
		//current login userId
		$this->userId = $this->userSession['user_id'];
		if (!$this->setTab()) {
			return FALSE;
		}
		return TRUE;
	}

	private function setTab() {
        $tab = isset($this->request->REQUEST['tab']) ? $this->request->REQUEST['tab'] : 'all';
        if (!array_key_exists($tab, $this->tabTypes)) {
			$this->setError(400, 45221, 'ilegal tab type input');
            return FALSE;
        }   
        $this->tab = $tab;
        return TRUE;    
    }   
}
