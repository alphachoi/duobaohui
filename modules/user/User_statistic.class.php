<?php
namespace Snake\Modules\User;

/**
 * @author yishuliu@meilishuo.com
 * 得到用户个人信息
 *
 **/

Use Snake\Package\User\User					     AS User;
Use Snake\Package\User\UserObject				 AS UserObject;
Use Snake\Package\User\UserRelation				 AS UserRelation;
Use Snake\Package\User\UserStatistic			 AS UserStatistic;
Use Snake\Libs\Cache\Memcache;
Use \Snake\Package\Twitter\Twitter;

class User_statistic extends \Snake\Libs\Controller {
	private $userId = NULL;

	public function run()  {
		$this->userId = $this->userSession['user_id'];
		$userId = isset($this->request->REQUEST['user_id']) && is_numeric($this->request->REQUEST['user_id']) ? $this->request->REQUEST['user_id'] : 0;
		if (empty($userId)) {
			$this->header = 400;
			$this->view = array(
				'code' => 400,
				'message' => 'empty user_id'
			);
			return FALSE;
		}
		//$this->userId = 574; //7578993; //21935368; //765; //3855122;
		//$userId = 574;//7578993; //21935368; //765; //7580571; //1713840; //1155095;
		$user = new User();
		$userObj = new UserObject();

		$cacheHelper = Memcache::instance();
		$cacheKey = 'CacheKey:User_statistic:' . $userId;
		$cacheData = $cacheHelper->get($cacheKey);

		if (!empty($cacheData)) {
			$userInfoTmp = $cacheData;
		}
		else {
			$userInfoTmp = $user->getUserInfo($userId, array('nickname', 'avatar_a', 'avatar_c', 'avatar_b', 'is_taobao_buyer', 'verify_msg', 'verify_icons'));
			$userLabel = $user->getUserLabel($userId);
			$userProvince = $user->getUserProvince($userId);
			$userCity = $user->getUserCity($userId);
			$userInfoTmp['label'] = $userLabel;
			$userInfoTmp['province'] = $userProvince['S_PROVNAME'];
			$userInfoTmp['city'] = $userCity['S_CITYNAME'];
			$cacheHelper->set($cacheKey, $userInfoTmp, 600);
		}

		$userInfoTmp['statistic'] = $user->getUserStatistic($userId);
		$userInfoTmp['online'] = $user->getUserOnline($userId);

		$userRelation = new UserRelation();
		$tObj = new Twitter();
		$twitter_num = $tObj->getNumOfTwitterByUid($userId);
		$userObj->followed = $userRelation->checkUsersIfFollow($this->userId, $userId);
		$userObj->user_id = $userInfoTmp['user_id'];
		$userObj->nickname = $userInfoTmp['nickname'];
		$userObj->is_taobao_buyer = $userInfoTmp['is_taobao_buyer'];

		if (empty($userInfoTmp['avatar_b']) || strpos($userInfoTmp['avatar_b'], '/css/images/0.gif') !== FALSE) {
			$userObj->avatar_c = !empty($userInfoTmp['avatar_a']) ? $userInfoTmp['avatar_a'] : 'http://imgtest.meiliworks.com/css/images/0.gif';
		}
		else {
			$userObj->avatar_c = $userInfoTmp['avatar_b']; 
		}
		$userObj->online_status	= $userInfoTmp['online'];

		$userObj->heart_num	= (!empty($userInfoTmp['statistic']['heart_num']) && $userInfoTmp['statistic']['heart_num'] > 0) ? $userInfoTmp['statistic']['heart_num'] : 0;
		$userObj->follower_num = (!empty($userInfoTmp['statistic']['follower_num']) && $userInfoTmp['statistic']['follower_num'] > 0) ? $userInfoTmp['statistic']['follower_num'] : 0;
		$userObj->twitter_num = !empty($twitter_num) ? $twitter_num : 0;//(!empty($userInfoTmp['statistic']['twitter_num']) && $userInfoTmp['statistic']['twitter_num'] > 0) ? $userInfoTmp['statistic']['twitter_num'] : 0;
		$userObj->label = $userInfoTmp['label'];
		$userObj->province = !empty($userInfoTmp['province']) ? $userInfoTmp['province'] : '';
		$userObj->city = !empty($userInfoTmp['city']) ? $userInfoTmp['city'] : '';

		$result = $user->assembleMsg($userInfoTmp, $userId);
		foreach ($result as $key => $value) {
			$userObj->$key = $value;
		}

		//print_r($userObj->getUser());exit;
		$this->view = $userObj->getUser();
	}
}

