<?php
namespace Snake\Package\User;

Use \Snake\Package\User\User;
Use \Snake\Package\User\Helper\DBUserHelper 	       AS DBUserHelper;
Use \Snake\Package\User\Helper\RedisUserFollow      AS RedisUserFollow;

class UserRelation {
	
	private static $instance = NULL;
	private $table = 't_dolphin_userfriend_info';
	
	/** 
     * @return userRelation Object
     */
    public static function getInstance(){
		if (empty(self::$instance)) {
			self::$instance = new self(); 
		}
        return self::$instance;
    }   

	/** 
     * Get user relation from t_dolphin_userfriend_info. <br/>
     *
     * @param integer $sourceUserId <br/>
     * @param array $destUserIds <br/>
     * @param array $params select variable <br/>
     * @return array with destUserId as hash_key <br/>
     */    
    public function getUserRelation($sourceUserId, $destUserIds = array(), $columns = array('map_id')) {
        if (empty($sourceUserId) || empty($destUserIds)) {
            return FALSE;
        }   
        $destUser = implode(',', $destUserIds);
        $str = implode(',', $columns);
		
        $sqlComm = "SELECT follower_id, $str FROM t_dolphin_userfriend_info WHERE user_id=:_user_id AND follower_id in ($destUser)";
		$sqlParams = array('_user_id' => $sourceUserId);
        $sqlResult = DBUserHelper::getConn()->read($sqlComm, $sqlParams, FALSE, 'follower_id');
		foreach ($destUserIds as &$value) {
			if (!empty($sqlResult[$value])) {
                $sqlResult[$value] = 1;
			}
			else {
                $sqlResult[$value] = 0;
			}	
		}
        return $sqlResult;
    }

	/** 
     * 检查$sourceUser是否关注$destUser <br/>
     * @param $sourceUser <br/>
     * @param $destUser <br/>
     * @return bool true/false <br/>
     */
    public function checkUsersIfFollow($sourceUser, $destUser, $friend = TRUE) {
        return self::getInstance()->getTwoUserRelation($sourceUser, $destUser, $friend);
    }   

    /** 
     * 获取两个用户的关系 <br/>
     * @return int <br/>
     *    2 表示互相关注，1代表关注，0代表未关注
	 *    3 代表我自已
     */
    public function getTwoUserRelation($sourceUser, $destUser, $friend = TRUE) {
        if (empty($sourceUser) || empty($destUser)) {
            return FALSE;
        }   
		if ($sourceUser == $destUser) {
			return 3;
		}
        $isFollow = (int) RedisUserFollow::isFollowed($sourceUser, $destUser);
        if (empty($isFollow)) {
            return 0;
        }   
        if (empty($friend)) {
            return $isFollow;
        }
        $isFans = (int) RedisUserFollow::isFollowed($destUser, $sourceUser);
        return $isFollow + $isFans;
    }
   
    /** 
     * 获取一个用户与一组要匹配用户的关系 <br/>
     * @return array 以destUserId作为键值 <br/>
     *    2 表示互相关注，1代表关注，0代表未关注
     */
	public function getUsersRelation($sourceUserId, $destUserIds = array(), $friend = FALSE) {
		if (empty($sourceUserId) || empty($destUserIds)) {
			return FALSE;
		}
		$isFollow = array();
		foreach ($destUserIds as $key => $value) {
			$isFollow[$value] = (int) RedisUserFollow::isFollowed($sourceUserId, $value);
		}
        if (empty($friend)) {
            return $isFollow;
        }
		$isFans = array();
		foreach ($destUserIds as $key => $value) {
			$isFans[$value] = (int) RedisUserFollow::isFollowed($value, $sourceUserId);
			$result[$value] = $isFans[$value] + $isFollow[$value];
		}
		return $result;
	}

	/** 
     * 设定$sourceUser 关注 $destUser的函数 <br/>
	 * @author yishuliu@meilishuo.com <br/>
     * @param  $sourceUser <br/>
     * @param  $destUser <br/>
     * @return BOOL TRUE if success <br/>
     */
    function setUserFollow($sourceUser, $destUser) { 
        if (empty($sourceUser) || empty($destUser)){
            return FALSE;
        }   

		//检查被关注user是否存在
		$userHelper = new User();
		$num = $userHelper->checkIfUserExists($destUser, TRUE);
		if (empty($num)) {
			return FALSE;
		}

        //检查是否已关注
        if ($this->checkUsersIfFollow($sourceUser, $destUser)) {
            return FALSE;
        }   

        //检查$destUser是否关注$sourceUser
        $checkStatus = $this->checkUsersIfFollow($destUser, $sourceUser);
        $sqlComm = ""; 
        $follow_time = $_SERVER['REQUEST_TIME'];
        $friend_show = 1;
        $sqlData = array();
        if (empty($checkStatus)) { 
            $sqlComm = "INSERT INTO t_dolphin_userfriend_info (user_id, follower_id, follow_time) VALUES" . " ({$sourceUser}, {$destUser}, {$follow_time})";
        } 
		else {
            //如果$destUser已经关注了$sourceUser则把他们的好友关系置1
            $sqlComm = "INSERT INTO t_dolphin_userfriend_info (user_id, follower_id, follow_time, friend_show) VALUES" . " ({$sourceUser}, {$destUser}, {$follow_time}, {$friend_show})";
		    //先要设定$destUser 对应 $sourceUser的好友关系
            //$this->setUsersFriends($destUser, $sourceUser);	
        }   
        $sqlData = array();
		DBUserHelper::getConn()->write($sqlComm, $sqlData);
		return TRUE;
        //return DBUserHelper::getConn()->getAffectedRows();
    }   

	/**
	 * 设定$sourceUser取消关注$destUser
	 * @param $sourceUser
	 * @param $destUser
	 *
	 */
	public function setUserCancelFollow($sourceUser, $destUser) {
		if (empty($sourceUser) || empty($destUser)) {
			return FALSE;
		}
		$result = $this->checkUsersIfFollow($sourceUser, $destUser);
		if ($result === 0) {
			return FALSE;
		}
		$sqlComm = "DELETE FROM {$this->table} WHERE user_id=:user_id AND follower_id=:follower_id";
		$sqlData = array(
			'user_id' => $sourceUser,
			'follower_id' => $destUser,
		);	
		DBUserHelper::getConn()->write($sqlComm, $sqlData);
		return TRUE;
	}
	
	/**
	 * 根据user_id的到与这个用户互相关注的用户列表
	 * @param $userId
	 * @return array
	 * @author yishuliu@meilishuo.com
	 */
	public function getMutualFollow($userId) {
		if (empty($userId)) {
			return FALSE;
		}
		$destUids = RedisUserFollow::getFollow($userId);
		if (empty($destUids)) {
			return FALSE;
		}
		foreach ($destUids as $key => $value) {
			$result = RedisUserFollow::isFollowed($value, $userId);
			if ($result !== TRUE) {
				unset($destUids[$key]);
			}
		}
		return $destUids;
	}

    private function setFriendFlag($sourceUser , $destUser , $flag) {
        $sqlComm = "UPDATE t_dolphin_userfriend_info SET friend_show=:friend_show WHERE ".
            " user_id=:user_id AND follower_id=:follower_id ";
        $sqlData['user_id'] = $sourceUser;
        $sqlData['follower_id'] = $destUser;
        $sqlData['friend_show'] = $flag;
		DBUserHelper::getConn()->write($sqlComm, $sqlData);
        return TRUE;
    }

    /**
     * 设置$sourceUser 是 $destUser 的朋友
     * @param $sourceUser
     * @param $destUser
     */
    public function setUsersFriends($sourceUser , $destUser) {
        return $this->setFriendFlag($sourceUser, $destUser, 1);
    }

    /**
     * 取消设置$sourceUser 是 $destUser 的朋友
     * @param $sourceUser
     * @param $destUser
     */
    public function setUsersUnfriends($sourceUser , $destUser) {
        return $this->setFriendFlag($sourceUser, $destUser, 0);
    }

    /** 
     * 获得一个用户所关注的人的ID
     * @param int $userId
     * @return Array
     */
    public function getUserFollowerByUserId($userId, $start = 0, $length = 20, $type = 'all') {
        if(empty($userId)){
            return FALSE;
        }   
        $condition = ''; 
        if ($type == 'twitter') {
            $condition = " friend_show = 1 AND ";
        }   
        $sqlComm = "SELECT follower_id FROM t_dolphin_userfriend_info WHERE $condition user_id=:user_id ORDER BY map_id DESC";

        if ($length != 0 && $start != -1) {
            $sqlComm .=  " limit {$start},{$length}";
        }   
        $sqlData['user_id'] = $userId;
        $result = array();
		$result = DBUserHelper::getConn()->read($sqlComm, $sqlData);
        return $result;
    }   
}
