<?php
namespace Snake\Package\User;

/**
 * @author yishuliu@meilishuo.com
 * 得到用户关注列表信息和粉丝列表信息
 *
 */

Use \Snake\Package\User\User as User;
Use \Snake\Package\User\UserStatistic;
Use \Snake\Package\Twitter\Twitter;
Use \Snake\Package\User\Helper\RedisUserFollow;

/**
 * @author yishuliu@meilishuo.com
 * @since 2012-07-25
 * @version 1.0
 *
 */

class UserFollowList {
    private static $instance = NULL;
	const isSelf = 2;

    /** 
     * @return Object
     */
    public static function getInstance(){
        if (empty(self::$instance)) {
            self::$instance = new self(); 
        }   
        return self::$instance;
    }   
   
    private function __construct() {
    }   

	public function createFollowList($followInfo, $userId, $visiterID, $pageType, $followNum) {
		$userHelper = new User();
		$userInfo = $userHelper->getUserInfo($visiterID, array('nickname'));
        if (!isset($userInfo['user_id'])) {
            return FALSE;
        }
		unset($userInfo);
        //if ($visiterID == $userId) {
            //$this->smarty->assign('self', TRUE);
        //}
        if (empty($followInfo)) {
            $followInfo = array();
        }

        $userIdArr = \Snake\Libs\Base\Utilities::DataToArray($followInfo, $pageType);
        //array_push($userIdArr, $visiterID);
        $userInfo = $userHelper->getUserInfos($userIdArr, array('nickname', 'about_me', 'avatar_b', 'is_taobao_buyer', 'verify_icons'));
		//print_r($userInfo);die;

		foreach ($userInfo as $key => $value) {
			$userProvince = $userHelper->getUserProvince($key);
			$userCity = $userHelper->getUserCity($key);
			$userInfo[$key]['cityname'] = $userCity['S_CITYNAME'];
			$userInfo[$key]['province'] = $userProvince['S_PROVNAME'];
		}

        if ($visiterID == $userId AND $pageType == 'follower_id') {
            $title = '我关注的人';
            $type = 'me';
        } 
		elseif ($visiterID == $userId AND $pageType == 'user_id') {
            $title = '关注我的人';
            $type = 'me';
        } 
		else {
            $type = 'other';
        }
		
		if (!empty($userId)) {
			$friendIDArr = RedisUserFollow::getFollow($userId);
			foreach ($followInfo as $follows) { 
				$FriendUid = $follows[$pageType];
				if ($type == 'me') {
					//为1表示互联关注
					$userInfo[$FriendUid]['friend_show'] = $follows['friend_show'];

					if (in_array($FriendUid, $friendIDArr)) {
						//显示已关注或者加关注
						$userInfo[$FriendUid]['is_followed'] = 1;
					}
					else {
						$userInfo[$FriendUid]['is_followed'] = 0;
					}
				}
				else {
					if ($FriendUid != $userId) {
						if (in_array($FriendUid, $friendIDArr)) {
							//显示已关注或者加关注
							$userInfo[$FriendUid]['is_followed'] = 1;
						}
						else {
							$userInfo[$FriendUid]['is_followed'] = 0;
						}
					}
					else {
						//此参数为2表示为自己
						$userInfo[$FriendUid]['is_followed'] = self::isSelf;
					}
					$userInfo[$FriendUid]['friend_show'] = 0;
				}
			}
		}
        $userStatics = array();
        $followUids = \Snake\Libs\Base\Utilities::DataToArray($followInfo, $pageType);
        $userStatics = UserStatistic::getInstance()->getUserStatistics($followUids);

        $tObj = new Twitter();
        foreach ($followUids as $key => $value) {
        	$userInfo[$value]['twitter_num'] = (int) $tObj->getNumOfTwitterByUid($followUids[$key]);
			$userInfo[$value]['heart_num'] = (!empty($userStatics[$value]['heart_num']) && $userStatics[$value]['heart_num'] > 0) ? $userStatics[$value]['heart_num'] : 0;
			$userInfo[$value]['follower_num'] = (!empty($userStatics[$value]['follower_num']) && $userStatics[$value]['follower_num'] > 0) ? $userStatics[$value]['follower_num'] : 0;
			$userInfo[$value]['following_num'] = (!empty($userStatics[$value]['following_num']) && $userStatics[$value]['following_num'] > 0) ? $userStatics[$value]['following_num'] : 0;
        }
		return $userInfo;
	}
}
