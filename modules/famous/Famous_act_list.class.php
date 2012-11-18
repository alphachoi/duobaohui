<?php
namespace Snake\Modules\Famous;

/**
 * 达人活动25页海报墙
 * @author yishuliu@meilishuo.com
 */

Use \Snake\Package\Famous\FamousActivity;			
Use \Snake\Libs\Cache\Memcache;
Use \Snake\Package\User\UserRelation;
Use \Snake\Package\User\User;
Use \Snake\Package\Famous\Helper\RedisFamousActivity;

class Famous_act_list extends \Snake\Libs\Controller {
	private $userId = NULL;
	private $cache = FALSE;
	private $tab = NULL;
	private $page = NULL;
	private $total = 0;
	private $types = array(1 => '美妆达人', 2 => '搭配达人', 3 => '扫货达人');

	//是否只显示top5
	private $limit = 0;

	//显示topN
	private $top = 5;

    const maxFrame = 1; //FRAME_SIZE_MAX; //6
    const frameSize = 25; //WIDTH_PAGE_SIZE; //20

	//TODO
	public function run()  {
        if (!$this->_init()) {
            return FALSE;
        }
		if (empty($this->limit)) {
			//只取top5
			$usersInfo = FamousActivity::getInstance()->getTopUserByType($this->top);
			$uids = \Snake\Libs\Base\Utilities::DataToArray($usersInfo, 'user_id');
			$nicknames = $this->fetchUserNick($uids);
			foreach ($usersInfo as $key => $value) {
				$usersInfo[$key]['vote'] = RedisFamousActivity::getVoteCount($value['user_id']);
				if (isset($nicknames[$value['user_id']]['nickname'])) {
					$usersInfo[$key]['nickname'] = $nicknames[$value['user_id']]['nickname'];
				}
			}
            $tabs = array_flip($this->types);
			$num = array();
            foreach ($tabs as $key => $value) {
                $num[] = FamousActivity::getInstance()->getFamousActListTotal($value);
            }
			$this->total = $num;
		}
		else { //取得是25个每页
			$offset = ($this->page * self::maxFrame) * self::frameSize;

			$usersInfo = FamousActivity::getInstance()->getActUserInfoByType($this->tab, array('user_id', 'division', 'img', 'vote', 'group_id'), $offset, self::frameSize);
			$uids = \Snake\Libs\Base\Utilities::DataToArray($usersInfo, 'user_id');
			$nicknames = $this->fetchUserNick($uids);
			foreach ($usersInfo as $key => $value) {
				$usersInfo[$key]['vote'] = RedisFamousActivity::getVoteCount($value['user_id']);
				if (isset($nicknames[$value['user_id']]['nickname'])) {
					$usersInfo[$key]['nickname'] = $nicknames[$value['user_id']]['nickname'];
				}
			}
			$this->total = FamousActivity::getInstance()->getFamousActListTotal($this->tab);
		}
		$FinalResult = array();
        if (!empty($this->userId)) {
			if (empty($usersInfo)) {
				$isJoined = FamousActivity::getInstance()->checkUserParticipate($this->userId, TRUE);
				$isJoined = ($isJoined == TRUE) ? 1 : 0;
                $response = array('group_data' => array(), 'group_name' => array(), 'totalNum' =>$this->total, 'is_joined' => $isJoined);
                $this->view = $response;
                return ;
			}
			foreach ($usersInfo as $key => $value) {
				$usersInfo[$key]['isvote'] = 0;
				$usersInfo[$key]['self'] = 0;
				
				$result = RedisFamousActivity::isVoted($value['user_id'], $this->userId);
				if ($result === TRUE) {
					$usersInfo[$key]['isvote'] = 1;
				}   
				if ($value['user_id'] == $this->userId) {
					$usersInfo[$key]['self'] = 1;
				}
				$usersInfo[$key]['img'] = \Snake\Libs\Base\Utilities::getPictureUrl($usersInfo[$key]['img']);
				$FinalResult[$this->types[$value['division']]][] = $usersInfo[$key];
			}   
        }   
        else {
			if (empty($usersInfo)) {
                $response = array('group_data' => array(), 'group_name' => array(), 'totalNum' =>$this->total, 'is_joined' => 0);
                $this->view = $response;
                return ;
			}
            foreach ($usersInfo as $key => $value) {
                $usersInfo[$key]['isvote'] = 0;
				$usersInfo[$key]['self'] = 0;
				$usersInfo[$key]['img'] = \Snake\Libs\Base\Utilities::getPictureUrl($usersInfo[$key]['img']);
				$FinalResult[$this->types[$value['division']]][] = $usersInfo[$key];
            }   
        }   
		if (empty($this->userId)) {
			$isJoined = 0;
		}
		else {
			$isJoined = FamousActivity::getInstance()->checkUserParticipate($this->userId, TRUE);
			$isJoined = ($isJoined == TRUE) ? 1 : 0;
		}
		if (empty($this->limit)) {
			$response = array('group_data' => $FinalResult, 'group_name' => array_values($this->types), 'totalNum' => $this->total, 'is_joined' => $isJoined);
		}
		else {
			$response = array('group_data' => $FinalResult, 'group_name' => array($this->types[$this->tab]), 'totalNum' => $this->total, 'is_joined' => $isJoined);
		}
		//print_r($response);die;
        $this->view = $response;
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
        if (!$this->setLimit()) {
            return FALSE;
        }
		return TRUE;
	}

	private function setTab() {
        $tab = isset($this->request->REQUEST['tab']) ? $this->request->REQUEST['tab'] : '1';
        if (!array_key_exists($tab, $this->types)) {
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

	private function setLimit() {
		$limit = !empty($this->request->REQUEST['limit']) ? $this->request->REQUEST['limit'] : 0;
        if (!is_numeric($limit)) {
            $this->setError(400, 40105, 'illegal limit paramter');
            return FALSE;
        }   
        $limit = (int) $limit;
        if ($limit < 0) {
            $this->setError(400, 40106, 'out of range');
            return FALSE;
        }   
        $this->limit = $limit;
        return TRUE;
	}

	private function fetchUserNick($userIds) {
        $userHandle = new User();
        $userInfos = $userHandle->getUserInfos($userIds, array('user_id', 'nickname'));
        return $userInfos;
	}
}
