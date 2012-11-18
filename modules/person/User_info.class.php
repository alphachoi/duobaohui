<?php
namespace Snake\Modules\Person;

/**
 * 个人页面：用户组信息区块
 * @author Chen Hailong
 * @modify yishuliu@meilishuo.com
 **/

Use \Snake\Package\User\User;			
Use \Snake\Package\User\UserRelation;	
Use \Snake\Package\User\UserStatistic;
Use \Snake\Libs\Cache\Memcache;
Use \Snake\Package\Mall\Mall;

class User_info extends \Snake\Libs\Controller {
	private $userId = NULL;
	private $cache = TRUE;
	private $avatar = NULL;

	public function run()  {
		$userId = $this->_init();
		if (empty($userId)) {
			return FALSE;
		}
		$redirectUrl = $this->_checkIfMall($userId);
		if ($redirectUrl['noredirect'] == 1) {
			$user = new User();
			$userInfo = $this->_getUserinfo($userId, $user);
			$userInfo['avatar_a'] = !empty($this->avatar) ? $this->avatar : $userInfo['avatar_a'];
			if (empty($userInfo['user_id'])) {
				//$this->view = array();
				return FALSE;
			}
			$userInfo['isBan'] = 0;
			if ($userInfo['is_actived'] == -1 || $userInfo['is_actived'] == -2) {
				$userInfo['isBan'] = 1;
			}
			$userStatistic				= $user->getUserStatistic($userId);
			$userInfo['online_status']	= $user->getUserOnline($userId);
			$userInfo['heart_num']		= !empty($userStatistic['heart_num']) ? $userStatistic['heart_num'] : 0; 
			$userInfo['follower_num']	= !empty($userStatistic['follower_num']) ? $userStatistic['follower_num'] : 0; 
			$userInfo['following_num']	= !empty($userStatistic['following_num']) ? $userStatistic['following_num'] : 0; 
			$userInfo['followed']		= 0;
			$userInfo['following']      = 0;
			//logged in user visit other user need check followed or not
			if ($this->userId != $userId && $this->userId > 0) {
				$userRelation = new UserRelation();
				$userInfo['followed'] = $userRelation->checkUsersIfFollow($this->userId, $userId);
				//被查看用户是否关注登陆用户
				$userInfo['following'] = $userRelation->checkUsersIfFollow($userId, $this->userId, FALSE);
			}
			//根据用户verify_icons数据返回对应浮窗悬浮值
			$result = $user->assembleMsg($userInfo, $userId);
			foreach($result as $key => $value) {
				$userInfo[$key] = $value;
			}
			
			if (!empty($userInfo['verify_icons'])) {
				$result = array();
				$result = is_array($userInfo['verify_icons']) ? $userInfo['verify_icons'] : explode(',' , $userInfo['verify_icons']);
				if (!in_array('s', $result)) {
					unset($userInfo['weibo_url']);
				}
				unset($userInfo['verify_icons']);
				if (isset($userInfo['verify_msg'])) {
					unset($userInfo['verify_msg']);
				}
			}
			if (empty($userInfo['verify_icons'])) {
				$userInfo['verify_icons'] = '';
				unset($userInfo['weibo_url']);
			}
			//print_r($userInfo);exit;
			$this->view = $userInfo;
		}
		elseif (!empty($redirectUrl['redirectUrl']) && !isset($redirectUrl['noredirect'])) {
			$this->view = $redirectUrl;
			return;
		}
	}
	
	private function _init() {
		//current login userId
		$this->userId = $this->userSession['user_id'];

		$userId = isset($this->request->REQUEST['user_id']) && is_numeric($this->request->REQUEST['user_id']) ? $this->request->REQUEST['user_id'] : 0;
		if (empty($userId)) {
			$this->setError(400, 40101, 'userId is empty');
			return FALSE;
		}

		$cacheKey = "users_temp_avatar_" . $userId;
		$cacheHelper = Memcache::instance();
		$this->avatar = $cacheHelper->get($cacheKey);

		return $userId;
	}
	
	/**
	 *	cache handle
	 **/
	private function _getUserinfo($userId, $user) {
		$userInfo = $user->getUserInfo($userId, array('nickname', 'avatar_a', 'is_taobao_buyer', 'verify_msg', 'verify_icons', 'about_me', 'weibo_url', 'is_actived'));
		if (empty($userInfo)) {
			$this->setError(400, 40102, 'This user is not exist');
			return FALSE;
		}
		return $userInfo;
	}

	private function _checkIfMall($userId) {
		$mallHelper = new Mall();
        //$isMall = $mallHelper->isMall($userId);
		$mallInfo = $mallHelper->getMallInfoById($userId);
        if (!empty($mallInfo)) {
            $mall_url = $mallInfo[0]['mall_url'];
            if (empty($mall_url)) {
                if ($userId == $this->userId) {
					$redirectUrl = "mall";
					$result['redirectUrl'] = $redirectUrl;
                    return $result;
                }    
				else {
					$result['noredirect'] = 1;
                    return $result;
				}
            }    
            else {
				$redirectUrl = "minisite/$mall_url";
				$result['redirectUrl'] = $redirectUrl;
                return $result;
            }    
        }    
		else {
			$result['noredirect'] = 1;
            return $result;
		}
	}
}

