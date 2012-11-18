<?php
namespace Snake\Modules\Famous;

/**
 * 达人页面 普通达人海报墙
 * @author yishuliu@meilishuo.com
 */

Use \Snake\Package\Famous\FamousUser;			
Use \Snake\Package\User\User;
Use \Snake\Package\Famous\FamousActivity;			
Use \Snake\Libs\Cache\Memcache;
Use \Snake\Package\Famous\Helper\RedisFamousActivity;

class Vote_famous_activity extends \Snake\Libs\Controller {
	private $userId = NULL;
	private $voteId = NULL;
	private $cache = FALSE;
	private $types = array(1 => '美妆达人', 2 => '搭配达人', 3 => '扫货达人');
	

	public function run()  {
        if (!$this->_init()) {
            return FALSE;
        }
		if (empty($this->userId)) {
			return FALSE;
		}
		$result = RedisFamousActivity::isVoted($this->voteId, $this->userId);
		if ($result === FALSE) {
			RedisFamousActivity::addVote($this->voteId, $this->userId);
			$num = RedisFamousActivity::getVoteCount($this->voteId);
			FamousActivity::getInstance()->updateActUserVoteNum($this->voteId, $num);
			$logHandle = new \Snake\Libs\Base\SnakeLog('daren_vote', 'normal');
			$str = "投票用户id:" . $this->userId . "\t" . "被投票用户id:" . $this->voteId;
			$logHandle->w_log(print_r($str, true));
			//转发分享消息
			$userHelper = new User();
			$userInfo = $userHelper->getUserInfo($this->voteId);
			FamousActivity::getInstance()->shareAssemble($this->userId, $userInfo, $this->tab, $this->limit);
		}
		$this->view = array('status' => 1);
	}
	
	private function _init() {
		//current login userId
		$this->userId = $this->userSession['user_id'];
		if (!$this->getVoteUserId()) {
			return FALSE;
		}
        if (!$this->setTab()) {
            return FALSE;
        }   
        if (!$this->setLimit()) {
            return FALSE;
        }  
		return TRUE;
	}

	private function getVoteUserId() {
        $voteId = !empty($this->request->REQUEST['user_id']) ? $this->request->REQUEST['user_id'] : 0;
        if (!is_numeric($voteId)) {
            $this->setError(400, 40107, 'illegal user_id');
            return FALSE;
        }   
        if ($voteId < 0 || $voteId == 0)  {
            $this->setError(400, 40108, 'user_id is nagetive');
            return FALSE;
        }   
        $this->voteId = $voteId;
        return TRUE;
	}

    private function setTab() {
        $tab = isset($this->request->REQUEST['tab']) ? $this->request->REQUEST['tab'] : '1';
		$tab = (int) $tab; 
        if (!array_key_exists($tab, $this->types)) {
            $this->setError(400, 45221, 'ilegal tab type input');
            return FALSE;
        }   
        $this->tab = $tab;
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
}
