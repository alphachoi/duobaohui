<?php
namespace Snake\Package\Timeline;

/**
 * 有关Timeline(首页海报墙数据源)的操作,类中方法都为静态。
 * 引用方法Timeline::functionName()
 * 或者使用把类声明为可观察，在onChange()下添加观察操作。
 * 
 * 目前包括观察操作：
 * 1 Follow 关注人
 * 2 CancelFollow 取消关注人
 * 3 RegisterFollow 注册关注杂志社
 *
 * @author ChaoGuo
 * @email  chaoguo@meilishuo.com
 */

USE \Snake\Package\Timeline\Helper\RedisGroupPosterOutbox AS GroupPosterOutbox;
USE \Snake\Package\Timeline\Helper\RedisUserPosterOutbox AS UserPosterOutbox;
USE \Snake\Package\Group\GroupFactory AS GroupFactory;
USE \Snake\Package\Group\Helper\RedisUserGroupUnFollower AS UserGroupUnFollower;
USE \Snake\Package\Group\Helper\RedisUserGroupFollower AS UserGroupFollower;
USE \Snake\Package\Timeline\Helper\RedisUserHomePosterTimeline AS UserHomePosterTimeline;
USE \Snake\Package\Timeline\Helper\DBTwitterHelper AS DBTwitterHelper;
USE \Snake\Package\User\Helper\RedisUserFollow AS RedisUserFollow;
USE \Snake\Package\User\Helper\RedisUserFans AS RedisUserFans;
USE \Snake\Package\Group\Helper\RedisUserGroup AS RedisUserGroup;
USE \Snake\Package\Group\GroupUser AS GroupUser;
USE \Snake\Package\Group\GroupTwitters;
USE \Snake\Libs\Base\ZooClient AS ZooClient;
USE \Snake\Libs\Base\Utilities AS Utilities;
USE \Snake\Package\Timeline\Helper\RedisUserLike as UserLike;
USE \Snake\Package\Twitter\Twitter AS Twitter;
USE \Snake\Package\Group\Groups AS Groups;

class Timeline implements \Snake\Libs\Interfaces\Iobserver {

    const HOME_POSTER_TIMELINE_SIZE = 3600;
	const HOME_POSTER_TIMELINE_ASYNC_THRESHOLD = 10000;

	public function __construct() {

	}

	public function onChanged($sender, $args) {
		switch ($sender) {
			case 'Follow' :
				self::newFollowing($args['user_id'], $args['other_id']);
				break;	
			case 'CancelFollow' :
				self::cancelFollowing($args['user_id'], $args['other_id']);
				break;
			case 'RegisterFollow' :
				self::groupFollowing($args['user_id'], $args['group_ids']);
				break;
			default :
				break;
		}
	}

	public static function newPosterTwitter($twitter) {
        $uid = $twitter['twitter_author_uid'];
        $tid = $twitter['twitter_id'];
        $gid = $twitter['group_id'];
		$showType = $twitter['twitter_show_type'];

        if (empty($uid) || empty($tid) || (empty($gid) && $showType != 9)) {
            return FALSE;
        }

		$blackList = array(2, 5, 6, 7, 8, 10, 11, 12, 13, 14, 15, 16, 17, 18, 12028978, 11296, 31881, 48988, 54567, 54579, 68166);
		if (in_array($gid, $blackList)) {
			return FALSE;
		}

        //放入作者的timeline
        UserHomePosterTimeline::pushTwitter($uid, $tid);
        UserHomePosterTimeline::trimTimeline($uid);
        //放入作者的outbox
        UserPosterOutbox::pushTwitter($uid, $tid);    
        UserPosterOutbox::trimOutbox($uid);
        //放入杂志社的outbox
        GroupPosterOutbox::pushTwitter($gid, $tid);
        GroupPosterOutbox::trimOutbox($gid);

		$ioClient = \Snake\Libs\Base\ZooClient::getClient();
		$ioClient->push_group_twitter($gid);
		
		$size = RedisUserFans::getFansNumber($uid);

		//多主编情况
		$groupUserHelper = new GroupUser();
		$pGid = array($gid);
		$pField = array('user_id');
		$pRole = 1;
		$adminUids = $groupUserHelper->getGroupUsersByRole($pGid, $pField, $pRole);
		$adminUids = Utilities::DataToArray($adminUids[$gid], 'user_id');

		$adminSize = 0;
		foreach ($adminUids as $adminUid) {
			if ($adminUid == $uid) {
				continue;
			}
			$adminSize += RedisUserFans::getFansNumber($adminUid);
		}
		$size += $adminSize;

		$groupHelper = new Groups();	
		if ($size <= self::HOME_POSTER_TIMELINE_ASYNC_THRESHOLD) {
			$countMember = $groupHelper->getGroupInfo(array($gid), array('count_member'));	
			$size += $countMember[$gid]['count_member'];
		}


		//粉丝大于阈值
		if ($size > self::HOME_POSTER_TIMELINE_ASYNC_THRESHOLD) {
			//TODO 传入尽可能多的参数 ps adminUids
			$params = array();
			$params['file'] = 'timeline/update_user_home_poster_timeline.php';
			$params['data'] = json_encode(array($uid, $tid));
			$ioClient->user_timeline($params);
			return ;
		}
		else {
			$groupUserHelper = new GroupUser();
			$editorFollower = $groupFollower = array();
			$isGroupAdmin = FALSE;
			if (!empty($gid)) {
				if (in_array($uid, $adminUids)) {
					$isGroupAdmin = TRUE;
				}

				//权限为0,1的杂志社粉丝
				$pRole = array(0, 1);
				$editorFollower = $groupUserHelper->getGroupUsersByRole(array($gid), array('user_id'), $pRole, 0, 500);
				$editorFollower = array_keys($editorFollower[$gid]);
				//将自已从列表去除
				$editorFollower = array_diff($editorFollower, array($uid));

				//杂志社的粉丝(逻辑 = 主编粉丝 - UserGroupUnFollower + t_whale_topic_group_user(role = 4 + 5))
				$groupFollower = $groupUserHelper->getGroupFollower($gid);	
				$groupFollower = array_diff($groupFollower, array($uid));
			}

			//编辑更新自已的粉丝
			$ownFollower = array();
			if ($isGroupAdmin === FALSE) {
				$ownFollower = RedisUserFans::getFans($uid);
				$ownFollower = array_diff($ownFollower, UserGroupUnFollower::getUnFollower($gid));
			}

			//需要更新timeline的用户
			$pushGroupFollower = array_flip(array_flip(array_merge($editorFollower, $groupFollower, $ownFollower)));
			//$pushGroupFollower = array_unique(array_merge($editorFollower, $groupFollower, $ownFollower));

			UserHomePosterTimeline::updateMultiTimeline($pushGroupFollower, $tid);
		}
		
		if (rand(0, 100) == 1) {
			UserHomePosterTimeline::trimMultiTimeline($pushGroupFollower, $tid);
		}
		unset($pushGroupFollower);
    }

	/**
	 * 重建用户首页海报墙
	 * @param user_id integer 用户编号
	 * @param last_tid integer timeline数据库中最新的tid
	 * @param last_update_time integer 最后更新的unix时间戳
	 * @param db_tids array timeline数据库中的tids
	 */
	public static function rebuildUserHomePosterTimelineNew($user_id, $last_tid, $last_update_time = 0, $db_tids = array()) {
		$groupUserHelper = new GroupUser();
		$myGroups = array();
		$myGroups = $groupUserHelper->getUserGroupsByRole(array($user_id), array(0, 1, 8), array('group_id', 'role'));
		//主编&编辑杂志社
		$ownGroups = array();
		$blockGroups = array();
		if (!empty($myGroups)) {
			foreach ($myGroups as $f_gInfo) {
				if ($f_gInfo['role'] == 1 || $f_gInfo['role'] == 0) {
					$ownGroups[] = $f_gInfo['group_id'];	
				}
				elseif ($f_gInfo['role'] == 8) {
					$blockGroups[] = $f_gInfo['group_id'];
				}
			}
		}
		//修复用户关注杂志社
		self::repairFollowGroup($user_id, $last_update_time, $ownGroups, $blockGroups);
		$tids = array();
		$full = FALSE;
		$size = 0;
		$followGroups = UserGroupFollower::getFollowGroups($user_id);
		$fGroups = array_merge($ownGroups, $followGroups);
		foreach ($fGroups as $gid) {
			if (!empty($gid)) {
				//确定一个大致位置
				$length = GroupPosterOutbox::getSize($gid);
				$avg = GroupPosterOutbox::SIZE / 3;
				$start = 0;
				while ($start < $length) {
					$compare = GroupPosterOutbox::getRangePoster($gid, $start, 1);
					if (empty($compare) || $last_tid >= $compare[0]) {
						break;
					}
					$start += $avg;
				}
				//找到确切的tid在列表中的位置
				if ($start > 0) {
					$index = 0;
					$insert = array();
					$start -= $avg;
					$include = GroupPosterOutbox::getRangePoster($gid, $start, $avg);
					foreach ($include as $tid) {
						if ($last_tid >= $tid) {
							break;
						}
						++$index;
					}
					$insert = GroupPosterOutbox::getRangePoster($gid, 0, $start + $index);
					$tids = array_merge($tids, $insert);
				}
			}
			$size = count($tids);
			if ($size >= UserHomePosterTimeline::SIZE) {
				$full = TRUE;
				break;
			}
		}
		$likeTids = $likes = array();
		if ($full === FALSE) {
			$following = RedisUserFollow::getFollow($user_id);
			$options = array('limit' => array(0, 20));	
			//获取从删除用户timline的那个时间点到目前时间期间内关注人的最近20条喜欢tid(生成的新推)
			foreach ($following as $key => $uid) {
				$likes = UserLike::getLikes($uid, $last_update_time, time(), $options);
				empty($likes) && $likes = array();
				$likeTids = array_merge($likeTids, $likes);
				if (count($likeTids) >= UserHomePosterTimeline::SIZE - $size) {
					$full = TRUE;
					break;	
				}
			}
		}
		$allTids = array();
		if ($full === FALSE) {
			//$otherTids = UserHomePosterTimeline::getTimelineByUid($user_id, 0, 50);
			$otherTids = UserPosterOutbox::getTwitter($user_id, 0, 50);
			$allTids = array_flip(array_flip(array_merge($likeTids, $tids, $db_tids, $otherTids)));
			$showFull = "NO";
		}
		else {
			$allTids = array_flip(array_flip(array_merge($likeTids, $tids)));
			$showFull = "YES";
		}
		sort($allTids);
		$pushTids = array_slice($allTids, -UserHomePosterTimeline::SIZE, UserHomePosterTimeline::SIZE);

		$log = new \Snake\Libs\Base\SnakeLog('timeline_rebuild_function', 'normal');
		$log->w_log(print_r(array('user_id' => $user_id, 'full' => $showFull, 'likeTids' => $likeTids, 'pushTids' => $pushTids), true));

		UserHomePosterTimeline::del($user_id);
		UserHomePosterTimeline::pushTwitters($user_id, $pushTids);
	}

	/**
	 * 修复用户关注杂志社(old)
	 * @param $user_id int 用户编号
	 * @param $last_update_time 删除timeline的时间点
	 * @param $ownGroups array 主编＆编辑的杂志社
	 * @param $owngroups array 黑名单杂志社
	 */
	public static function repairFollowGroup($user_id, $last_update_time, $ownGroups = array(), $blockGroups = array()) {
		$groupUserHelper = new GroupUser();
		if (empty($ownGroups)) {
			$myGroups = array();
			$myGroups = $groupUserHelper->getUserGroupsByRole(array($user_id), array(0, 1, 8), array('group_id', 'role'));
			if (!empty($myGroups)) {
				foreach ($myGroups as $f_gInfo) {
					if ($f_gInfo['role'] == 1 || $f_gInfo['role'] == 0) {
						$ownGroups[] = $f_gInfo['group_id'];	
					}
					elseif ($f_gInfo['role'] == 8) {
						$blockGroups[] = $f_gInfo['group_id'];
					}
				}
			}
		}
        empty($last_update_time) && $last_update_time = 0;
        $orderBy = 'created DESC';
        $start = 0;
        $limit = 0;
        $last_update_time = date('Y-m-d H:i:s', $last_update_time);
        $operation = array(
            'operation' => 'gt',
            'key' => 'created',
            'value' => $last_update_time,
        );  
		
		$followAdminGroup = array();
		$followingCount = RedisUserFollow::getFollowCount($user_id);
		$limit = 1000;
		$times = ceil($followingCount / $limit);
		for ($i=0; $i<$times; $i++) {
			$subFollow = array();
			$subFollowGroup = array();
			$subFollow = RedisUserFollow::getFollow($user_id, 'DESC', $i * $limit, $limit);
			$subFollowGroup = $groupUserHelper->getUserGroupsByRole($subFollow, array(1), array('group_id'), $orderBy, $start, $limit, array($operation));
			$subFollowGroup = Utilities::DataToArray($subFollowGroup, 'group_id');
			$followAdminGroup = array_merge($followAdminGroup, $subFollowGroup);
		}
		foreach ($followAdminGroup as $key => $gid) {
			if (UserGroupUnFollower::isUnFollow($gid, $user_id)) {
				unset($followAdminGroup[$key]);
			}
		}
		$logicGroups = array_diff($followAdminGroup, $ownGroups, $blockGroups);
		UserGroupFollower::pushMultiGroupUserFollower($user_id, $logicGroups);
	}

    /**
	 * 占用内存较大，暂不启用
     * 重建关注的杂志社, 取消和timeline的绑定。
     * 比较费时的操作是在获取关注人主编杂志社
     * 对此采取策略为不删除关注人多的杂志社列表
     * @param $user_id 用户编号
     */
    public static function rebuildFollowGroup($user_id) {
		return FALSE;
        if (empty($user_id)) {
            return FALSE;
        }

        $groupUserHelper = new GroupUser();
        $groupInfo = $groupUserHelper->getUserGroupsByRole(array($user_id), array(0, 1, 4, 5, 8), array('group_id, role, created'));
        //个人主编&&编辑杂志社
        $ownGroups = array();
        //关注杂志社
        $followGroups = array();
        //block
        $blockGroups = array();
        foreach ($groupInfo as $key => $gInfo) {
            $role = $gInfo['role'];
            $groupId = $gInfo['group_id'];
            $created = $gInfo['created'];
            if ($role == 5 || $role == 4) {
                $followGroups[] = array('group_id' => $groupId, 'created' => $created);
            }   
            elseif ($role == 1 || $role == 0) {
                $ownGroups[] = $groupId;
            }   
            elseif ($role == 8) {
                $blockGroups[] = $groupId;
            }   
        }


        $following = $followAdminGroup = array();
        $following = RedisUserFollow::getFollow($user_id);
        //这一步会慢，选择多关注用户的关注杂志社列表不删除策略
        $followAdminGroup = $groupUserHelper->getUserGroupsByRole($following, array(1), array('group_id', 'created'));
        $followAdminGroup = array_merge($followAdminGroup, $followGroups);
        if (!empty($followAdminGroup)) {
            foreach ($followAdminGroup as $key => $gInfo) {
                if (UserGroupUnFollower::sContains($gInfo['group_id'], $user_id) ||
                    in_array($gInfo['group_id'], $ownGroups) ||
                    in_array($gInfo['group_id'], $blockGroups)
                ) {
                    unset($followAdminGroup[$key]);
                }
            }
        }

        RedisUserGroup::delete($user_id);
        RedisUserGroup::addGroups($user_id, $followAdminGroup);
    }

    /**
	 * 占用内存较大，暂不启用
     * 修复用户关注杂志社(new zset)
     * @param $user_id int 用户编号
     * @param $ownGroups array 主编&编辑的杂志社
     *
     * 重建时调用
     */
    public static function repairFollowGroupNew($user_id, $last_update_time, $ownGroups = array()) {
		return FALSE;
        $groupUserHelper = new GroupUser();
        $score = RedisUserGroup::getFollowGroups($user_id, 0, 1, 'DESC', TRUE);
        //关注人的主编杂志社
        $following = RedisUserFollow::getFollow($user_id);
        if (empty($ownGroups)) {
            $ownGroups = $groupUserHelper->getUserGroupsByRole(array($user_id), array(0, 1), array('group_id'));
            $ownGroups = Utilities::DataToArray($ownGroups, 'group_id');
        }
        $followAdminGroup = array();
        empty($score) && $score = array(0);
        //从关注人创建的杂志社中找出时间大于score值的杂志社
        $orderBy = 'created DESC';
        $start = 0;
        $limit = 0;
        $score = array_pop($score);
        $score = date('Y-m-d H:i:s', $score);
        $operation = array(
            'operation' => 'gt',
            'key' => 'created',
            'value' => $score,
        );
        $followAdminGroup = $groupUserHelper->getUserGroupsByRole($following, array(1), array('group_id', 'created'), $orderBy, $start, $limit, array($operation));
        if (!empty($followAdminGroup)) {
            foreach ($followAdminGroup as $key => $gInfo) {
                if (in_array($gInfo['group_id'], $ownGroups) || UserGroupUnFollower::sContains($gInfo['group_id'], $user_id)) {
                    unset($followAdminGroup[$key]);
                }
            }
        }
        RedisUserGroup::addGroups($user_id, $followAdminGroup);
    }

	/**
	 * 关注的时候, 添加被关注人的新推到关注人的timeline.
	 * 默认添加最多5条7*24小时之内发的推.
	 */
	public static function newFollowing($followerUid, $followedUid, $timespan = 604800, $limit = 5) {
		if (UserPosterOutbox::exists($followedUid)) {
			$tids = UserPosterOutbox::getTwitter($followedUid, 0, $limit);
		}
		else {
			$twitterHelper = new Twitter(array('twitter_id'));	
			$limit = 5;
			$time = time() - 2592000;
			$tids = array();
			$tids = $twitterHelper->getUserLastTwitterAfterTime($followedUid, $time, $limit);
			if (empty($tids)) {
				return ;
			}
		}
		UserHomePosterTimeline::pushTwitters($followerUid, array_reverse($tids));
	}

	public static function cancelFollowing($followerUid, $followed_uid) {
		if (empty($followerUid) || empty($followed_uid)) {
            return FALSE;
        }   
		$twitterHelper = new Twitter(array('twitter_id'));	
		$limit = 360;
		$time = time() - 2592000;
		$tids = array();
		$tids = $twitterHelper->getUserLastTwitterAfterTime($followed_uid, $time, $limit);
		if (empty($tids)) {
			return ;
		}
        UserHomePosterTimeline::removeTwitters($followerUid, $tids);
	}

	public static function cancelGroupFollowing($followerUid, $groupId) {
		$result = TopicGroupTwitterModel::getInstance()->cancelGroupTwitter($groupId, self::HOME_POSTER_TIMELINE_SIZE);
		if (empty($result)) {
			return;
		}
		UserHomePosterTimeline::removeTwitters($followerUid, array_keys($result));
	}


    public static function newGroupFollowing($followerUid, $groupId, $timespan = 1209600, $limit = 10) {

        if (GroupPosterOutbox::exists($groupId)) {
            $twitterIds = GroupPosterOutbox::lRange($groupId, 0, $limit);
			$twitterIds = array_reverse($twitterIds);
        }   
        else {
			$groupTwitterHelper = new GroupTwitters();
			$twitters = $groupTwitterHelper->getGroupTwitters(array($groupId), array('twitter_id', 'group_id', 'elite'), 0, $limit);
			$twitterIds = $twitters[$groupId];
        }  
		//print_r("running TimlineOb!\n");
        UserHomePosterTimeline::pushTwitters($followerUid, $twitterIds);
    }  

	/**
	 * 关注杂志社进和海报墙
	 * @param $userId integer 用户编号
	 * @param $groupIds array 杂志社编号数组
	 * @param $limit integer 每个海报墙进入timeline个数
	 *
	 */
	public static function groupFollowing($userId, $groupIds, $limit = 5) {
		if (empty($userId) || empty($groupIds) || empty($limit)) {
			return FALSE;
		}
		$tids = array();
		foreach ($groupIds as $k => $gid) {
			if (!empty($gid)) {
				$oTids = GroupPosterOutbox::getRangePoster($gid, 0, $limit); 
				$tids = array_merge($tids, $oTids);
			}
		}
		UserHomePosterTimeline::pushTwitters($userId, $tids);
	}
}
