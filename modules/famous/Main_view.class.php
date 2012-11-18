<?php
namespace Snake\Modules\Famous;

/**
 * 达人页面 普通达人海报墙
 * @author yishuliu@meilishuo.com
 */

Use \Snake\Package\Famous\FamousUser;			
Use \Snake\Libs\Cache\Memcache;
Use \Snake\Package\User\UserRelation;

class Main_view extends \Snake\Libs\Controller {
	private $userId = NULL;
	private $cache = FALSE;
	private $tab = NULL;
	private $page = NULL;
	private $total = 0;
    const maxFrame = FRAME_SIZE_MAX; //6
    const frameSize = WIDTH_PAGE_SIZE; //20
	
	private $tabTypes = array('all' => 0, 'jiepai' => 1, 'cosmetic' => 2, 'fashion' => 3, 'editor' => 4, 'prof' => 5);

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
		$offset = ($this->frame + $this->page * self::maxFrame) * self::frameSize;

		$usersInfo = FamousUser::getInstance()->getSuperUserByType($user_type, $offset, self::frameSize);

		$Uids = \Snake\Libs\Base\Utilities::DataToArray($usersInfo, 'data_id');

		$this->total = FamousUser::getInstance()->getCmsListTotal($user_type);

        $followIds = array();
        if (!empty($this->userId)) {
			if (empty($usersInfo)) {
                $response = array('tInfo' => array(), 'totalNum' =>$this->total);
                $this->view = $response;
                return ;
			}
			foreach ($usersInfo as $key => $value) {
				$usersInfo[$key]['followbyme'] = 0;
				$usersInfo[$key]['self'] = 0;
				//解决互相关注点击取消后变成已关注问题
				$result = UserRelation::getInstance()->checkUsersIfFollow($this->userId, $value['data_id'], TRUE);
				if ($result == 1) {
					$usersInfo[$key]['followbyme'] = 1;
				}   
				elseif ($result == 2) {
					$usersInfo[$key]['followbyme'] = 2;
				}
				if ($value['data_id'] == $this->userId) {
					$usersInfo[$key]['self'] = 1;
				}
				$usersInfo[$key]['imgurl'] = \Snake\Libs\Base\Utilities::getPictureUrl($usersInfo[$key]['imgurl'], $type = "_o");
			}   
        }   
        else {
			if (empty($usersInfo)) {
                $response = array('tInfo' => array(), 'totalNum' =>$this->total);
                $this->view = $response;
                return ;
			}
            foreach ($usersInfo as $key => $value) {
                $usersInfo[$key]['followbyme'] = 0;
				$usersInfo[$key]['self'] = 0;
				$usersInfo[$key]['imgurl'] = \Snake\Libs\Base\Utilities::getPictureUrl($usersInfo[$key]['imgurl'], $type = "_o");
            }   
        }   
		$response = array('tInfo' => $usersInfo, 'totalNum' =>$this->total);
        $this->view = $response;
		//print_r($response);die;
		//对每个topModelUids,设置一个值判断是否follow,另外加一个值判断是否登录
		//对于未登录用户加cache,以四个人uid md5来作为memkey
	}
	
	private function _init() {
		//current login userId
		$this->userId = $this->userSession['user_id'];
		if (!$this->setTab()) {
			return FALSE;
		}
        if (!$this->setPage()) {
            return FALSE;
        }
        if (!$this->setFrame()) {
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

    private function setPage() {
        $page = !empty($this->request->REQUEST['page']) ? $this->request->REQUEST['page'] : 0;
        if (!is_numeric($page)) {
            $this->setError(400, 40107, 'bad page');
            return FALSE;
        }   
        if ($page < 0)  {
            $this->setError(400, 40108, 'page is nagetive');
            return FALSE;
        }   
        $this->page = $page;
        return TRUE;
    }

    private function setFrame() {
        $frame = !empty($this->request->REQUEST['frame']) ? $this->request->REQUEST['frame'] : 0;
        if (!is_numeric($frame)) {
            $this->setError(400, 40105, 'bad frame');
            return FALSE;
        }   
        $frame = (int) $frame;
        if ($frame < 0) {
            $this->setError(400, 40106, 'out of range');
            return FALSE;
        }   
        $this->frame = $frame;
        return TRUE;
    }   
}
