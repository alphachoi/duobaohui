<?php
namespace Snake\Package\User;

USE \Snake\Package\User\User AS User;
USE \Snake\Package\User\UserStatistic AS UserStatistic;
USE \Snake\Libs\Cache\Memcache AS Memcache;
USE \Snake\Package\User\UserRelation AS UserRelation;
USE \Snake\Libs\Base\Utilities AS Utilities;

class UserCommonList {

	public function __construct() {

	}

    public function getSearchCommonInfo($uids, $userId) {
        if (empty($uids)) {
            return array();
        }   
		!is_array($uids) && $uids = array($uids);
        $user = new User();
        $statistic = new UserStatistic();
		$userFields = array('user_id', 'nickname', 'verify_msg', 'avatar_c', 'avatar_a', 'avatar_b', 'avatar_d', 'avatar_e', 'is_taobao_seller', 'is_taobao_buyer', 'verify_icons');
		$userInfos = $user->getUserInfos($uids, $userFields, TRUE);
		
        $userStatistic = $statistic->getUserStatistics($uids);
        $poster = $this->mergePoster($userInfos, $userStatistic, $userId);    
        return $poster;
    }

	private function mergePoster($userInfo, $userStatistic, $userId) {
        if (empty($userInfo)) {
            return FALSE;
        }
        $cache = Memcache::instance();
        $poster = array();
		$userObj = new User();
        foreach ($userInfo as $uid => $uInfo) {
            //在线状态
            $status = $cache->get('user:login_status:' . $uInfo['user_id']);
            $onlineStatus = empty($status) ? 0 : 1;
            //关注状态
            $relation = NULL;
            !empty($userId) && $relation = UserRelation::getInstance()->getTwoUserRelation($userId, $uInfo['user_id']);
			$nickname = $this->convertNickname($uInfo['nickname']);
            $poster[$uid] = array(
                'user_id' => $uInfo['user_id'],
                'nickname' => $nickname,
                'following_num' => empty($userStatistic[$uid]['following_num']) ? 0 : (int) $userStatistic[$uid]['following_num'],
                'follower_num' => empty($userStatistic[$uid]['follower_num']) ? 0 : (int) $userStatistic[$uid]['follower_num'],
                'heart_num' => empty($userStatistic[$uid]['heart_num']) ? 0 : (int) $userStatistic[$uid]['heart_num'],
                'online_status' => $onlineStatus,
				'avatar_a' => $uInfo['avatar_a'],
				'avatar_b' => $uInfo['avatar_b'],
				'avatar_c' => $uInfo['avatar_c'],
				'avatar_d' => $uInfo['avatar_d'],
				'avatar_e' => $uInfo['avatar_e'],
                'followed' => $relation,
				'is_tarbao_seller' => $uInfo['is_taobao_seller'],
				'is_taobao_buyer' => $uInfo['is_taobao_buyer'],
				'verify_icons' => $uInfo['verify_icons'],
				'verify_msg' => $uInfo['verify_msg'],
				'identity' => $uInfo['identity'],
            );
        }
        return $poster;
    }

	private function convertNickname($nickname) {
		if (empty($nickname)) {
			return '';
		}
		$result = strpos($nickname, '#');
		if ($result !== FALSE && $result !== 0) {
			$nickname = substr($nickname, 0, $result);	
		}
		return $nickname;
	}
}
