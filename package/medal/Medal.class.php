<?php
namespace Snake\Package\Medal;
/**
 * 勋章相关的接口
 * @author Chen Hailong
 */

Use \Snake\Package\Medal\Helper\DBMedalHelper 	   AS DBMedalHelper;
Use \Snake\Package\User\User;
Use \Snake\Libs\Cache\Memcache;
Use \Snake\Package\Msg\Helper\RedisUserRepinNotice;

/**
 * 勋章相关的接口
 * @author Chen Hailong
 */
class Medal {
	
	private $medal = array();
	private $medalParams = array('medal_id', 'medal_title', 'medal_details', 'medal_type', 'medal_icon', 'medal_next_level_mid', 'medal_condition_exp', 'medal_alt', 'medal_notice', 'medal_share_wording');
	private $medalMapParams = array('map_id', 'user_id', 'medal_id', 'medal_num', 'update_time', 'medal_alt_ext');
	private $outOfDateMedals = array(11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 41, 42, 43, 44, 45, 46, 47, 51, 52, 53, 56, 57, 58, 59, 60, 61, 62, 64, 66, 68, 70, 71, 72);

	static $instance = NULL;
	static $cache = array();	

	public function __construct() {
    }   

	public function __get($name) {
        if (array_key_exists($name, $this->medal)) {
            return $this->medal[$name];
        }   
        return NULL;
    }   
    
    public function __set($name, $value) {
        $this->medal[$name] = $value;
    }   

    public function getMedal() {
        return $this->medal;
    }   

    /** 
     * 获取用户的所有勋章
     * @param int $userId
     * @param array $params 需要请求的数据项
     * @return multitype: result array order by medal_type DESC
     */
    public function getMedalByUid($userId, $params = array('*', 'medal_title', 'medal_details', 'medal_type', 'medal_icon', 'medal_next_level_mid', 'medal_alt'), $orderBy = TRUE, $hasKey = FALSE) {
		if (empty($userId)) {
			return FALSE;
		}
		$medalMapParams = $this->medalMapParams;
		$medalParams = $this->medalParams;
		$paramsMedal = array();
		$paramsMedalMap = array();
        foreach ($params as $key => $value) {
            if ($value == '*') {
                $paramsMedalMap = $medalMapParams;
                continue;
            }   
            if ($value == 'medal_id') {
                continue;
            }   
            if (in_array($value, $medalParams)) {
                $paramsMedal[] = $value;
            }   
            else {
                $paramsMedalMap[] = $value;
            }   
        }   
		
		$str = implode(',', $paramsMedalMap);
		$sql = "SELECT  /*UserMedal-lys*/ medal_id, $str FROM t_dolphin_medal_map WHERE user_id =:_user_id";
		if ($orderBy === TRUE) {
			$sql = $sql . " ORDER BY update_time DESC";
		}
		$sqlData['_user_id'] = $userId; 
		$result = DBMedalHelper::getConn()->read($sql, $sqlData, FALSE, 'medal_id');

		$medalIds = array();
		$strComm = implode(',', $paramsMedal);
		foreach ($result as $value) {
            $medalIds[] = $value['medal_id'];
        }
		if (!empty($medalIds)) {
			$medalIds = implode(',', $medalIds);
			if (!empty($strComm)) {
                $sqlComm = "SELECT /*UserMedal-lys*/ medal_id, $strComm FROM t_dolphin_medal WHERE medal_id in ($medalIds) ORDER BY medal_type DESC";
            }
            $resultInfo = DBMedalHelper::getConn()->read($sqlComm, array(), FALSE, 'medal_id');
			//IFNULL($result.medal_alt_ext, $resultInfo.medal_alt) as $resultInfo.medal_alt
			//将两次查询结果按medal_id merge
			$return = array();
			foreach ($result as $key => $value) {
				if ($value['medal_id'] == $resultInfo[$key]['medal_id']) {
					if (!empty($value['medal_alt_ext'])) {
						$resultInfo[$key]['medal_alt'] = $value['medal_alt_ext']; 
					}
					unset($value['medal_alt_ext']);
					$return[] = array_merge($result[$key], $resultInfo[$key]);
				}
			}	
			return $return;
		}
		else {
			return FALSE;
		}
    }   
	
	/**
	 * 根据用户id，获取用户已经获得的勋章的id和获取时间
	 * @access public
	 * @param int $uid 
	 * @return array $result
	 */
    function getUserMedalIdAndTime($uid) {
        if (empty($uid)) {
            return FALSE;
        }   
        $sqlComm = "SELECT medal_id, update_time FROM t_dolphin_medal_map WHERE user_id=:user_id ORDER BY map_id DESC";
        $sqlData['user_id'] = $uid;
        $result = array();
        $result = DBMedalHelper::getConn()->read($sqlComm, $sqlData, FALSE, 'medal_id');
        return $result;
    }

	/**
  	 * 勋章列表页面的勋章信息
  	 * @author Chen Hailong
	 * @param int $medalTypeId
	 * @param int $userId
	 * @access public
	 * @return array $result
  	 */
	public function getMedalListInfo($medalTypeId, $userId) {
        //获取勋章
        if (empty($medalTypeId)) { // all medals
			$medalTypeIds = array();
        }   
        else if ($medalTypeId == 3) { // 3 and 4 in same tab
            $medalTypeIds = array($medalTypeId);
            $medalTypeIds[] = 4;
        }   
        else if ($medalTypeId == 5) { // 5 and 6 in same tab
            $medalTypeIds = array($medalTypeId);
            $medalTypeIds[] = 6;
        }   
        else { // one type one tab
			$medalTypeIds = array($medalTypeId);
        }   
        $myMedalInfo = $this->getMedalInfoByMultiTypes($medalTypeIds);
		if (empty($userId)) {
			return $myMedalInfo;
		}
		$userMedalIds = $this->getUserMedalIdAndTime($userId);
        foreach ($myMedalInfo as $key => $item) {
            if (!empty($userMedalIds[$key])) {
                $myMedalInfo[$key] = array_merge($item, $userMedalIds[$key]);
            }   
            else {
                $myMedalInfo[$key]['update_time'] = 0;
            }   
            $myMedalInfo[$key]['user_id'] = $userId;
        }
		return $myMedalInfo;
	}

    /** 
     * 根据分类获取勋章
	 * @author Chen Hailong <br /> 
	 * @access public <br />
     * @param array $medalTypes ; empty means all type<br />
     * @return array $result ; hash_key is medal_id  <br />
     */
    public function getMedalInfoByMultiTypes($medalTypes = array()) {
        $sqlComm = "SELECT t1.* FROM t_dolphin_medal t1 ";
        $data = $sqlCond = array();
		if (!empty($medalTypes)) {
			foreach ($medalTypes as $key => $typeId) {
				$sqlCond[] = "medal_type=:medalType" . $key;
				$data['medalType' . $key] = $typeId;
			}
			$sqlComm .=  "WHERE " . implode(' OR ', $sqlCond);
		}
		$sqlComm .= " ORDER BY medal_id DESC";
        $result = array();
        $result = DBMedalHelper::getConn()->read($sqlComm, $data, FALSE, 'medal_id');
        return $result;
    }

	public function getMedalInfoByMids($medalIds) {
        if (empty($medalIds)) { 
            return FALSE;
        }   
        $midsStr = implode(',', $medalIds);
        $sqlComm = "SELECT * FROM t_dolphin_medal WHERE medal_id in ({$midsStr})";
        $result = array();
        $result = DBMedalHelper::getConn()->read($sqlComm, array(), FALSE, 'medal_id');
        return $result;
    }   
	
	/**
	 * 判定一下用户获得勋章的详细信息，如果没有返回FALSE
	 * @param int $userId
	 * @param int $medalId
	 * @access public
	 * @author Chen Hailong
	 * @return array $result or false
	 */
    public function getMedalByUidAndMid($userId, $medalId) {
        if (empty($userId)) {
            return FALSE;
        }   
        $sqlComm = "SELECT medal_id, update_time, user_id FROM t_dolphin_medal_map
                    WHERE user_id=:uid AND medal_id = :mid LIMIT 1";

        $result = DBMedalHelper::getConn()->read($sqlComm, array('uid' => $userId, 'mid' => $medalId), FALSE, 'medal_id');
        return $result;
    }  

	/**
     * 获取用户的勋章数量
     * @param $userId
     * @param $medalId
     */
	public function getUserMedalNum($userId, $medalId, $fromMaster = FALSE) {
        if (empty($userId) || empty($medalId)) {
            return 0;
        }
		$sqlData = array();
		$sqlData['user_id'] = $userId;
		$sqlData['medal_id'] = $medalId;
        $sqlComm = "SELECT medal_num FROM t_dolphin_medal_map WHERE user_id=:user_id AND medal_id=:medal_id";
        $result = array();
        $result = DBMedalHelper::getConn()->read($sqlComm, $sqlData, FALSE);

        if (isset($result[0]['medal_num'])) {
            return $result[0]['medal_num'];
        } 
		else {
            return 0;
        }
	}

	/**
     * 为用户添加勋章
     * @param $userId
     * @param $medalId
     * @param $sum      如果用户已经有该标签，数量加1
     */
    public function addUserMedal($userId, $medalId, $sum = TRUE) {
		if ($this->getUserMedalNum($userId, $medalId) > 0) {
            if ($sum === TRUE) {
                //已经有该勋章，数量加1
                $sqlComm = "UPDATE t_dolphin_medal_map SET medal_num=medal_num+1 WHERE user_id={$userId} AND medal_id={$medalId}";
				DBMedalHelper::getConn()->write($sqlComm, array());
            }
        } 
		else {
            //没有该勋章，插入新记录
			$data = array();
            $data['user_id'] = $userId;
            $data['medal_id'] = $medalId;
            $data['medal_num'] = 1;

            $sqlComm = "INSERT INTO t_dolphin_medal_map (medal_num, user_id, medal_id) VALUES (:medal_num, :user_id, :medal_id)";
			DBMedalHelper::getConn()->write($sqlComm, $data);

            //$cacheObj = Memcache::instance();
			//$cacheObj->delete('person:medal_' . $userId);
            //$cacheObj->delete('MEDAL_MM_LIST:' . $medalId);

            //得到勋章相关信息
            $medalInfo = $this->getMedalInfoByMids(array($medalId));
            $medalIcon = "http://i.meilishuo.net/css/images/medal/icons/small_" . $medalInfo[$medalId]['medal_icon'];
			
			$userHelper = new User();
			$userInfo = $userHelper->getUserInfo($userId, array('nickname', 'avatar_c'));
            $nickname = $userInfo['nickname'];

            //同步获得勋章的动态
            RedisUserRepinNotice::setNotice($userId, array(                                                                                                                                       
                'from' => $userId, 
                'type' => 'medal', 
                'nickname' => $nickname,
                'medalname' => $medalInfo[$medalId]['medal_title'],                                                                                                                               
                'avatar_url' => $medalIcon, 
                'url' => "http://www.meilishuo.com/medal/detail/" . $medalId,                                                                                                                     
                'medal_id' => $medalId,
                'time' => $_SERVER['REQUEST_TIME'])                                                                                                                                              
            );   
		}
    }

	public function getMedalInfoByExp($exp) {
        $sqlComm = "SELECT * FROM t_dolphin_medal WHERE medal_condition_exp LIKE '%{$exp}%' AND medal_type < 3";
        $result = array();
        $result = DBMedalHelper::getConn()->read($sqlComm, array(), FALSE);
        return $result;
    }   
	
	/**
	 * 获取指定勋章的获得人数
	 * @access public
	 * @author Chen Hailong 
	 * @param int $medalId
	 * @return int $number
	 */
    public function getMedalGotNum($medalId) {
		$cacheObj = Memcache::instance();
		$cacheKey = 'medal:number_' . $medalId;
		$number = $cacheObj->get($cacheKey);
		if (!empty($number)) {
			return $number;
		}
        $sqlComm = "SELECT count(*) num FROM t_dolphin_medal_map WHERE medal_id =:medal_id";
        $sqlData['medal_id'] = $medalId;
        $result = DBMedalHelper::getConn()->read($sqlComm, $sqlData, FALSE);
		if (!empty($result[0]['num'])) {
			$cacheObj->set($cacheKey, $result[0]['num'], 3600);
			return $result[0]['num'];
		}
        return 0; 

    }   

    /**     
     * 通过勋章id取得已获得该勋章的MM, MM信息主要包括uid, nickname, avatar_c
     * @param int $medalId <br />
     * @access public <br />
     * @author Chen Hailong <br />
     * @return array $result <br /> 
     */     
    function getMedalGotMms($medalId, $page, $pageSize) {
        $cacheObj = Memcache::instance();
        $memcacheKey = 'Medal:GetMedalGotMms:' . $medalId;
        /*if (empty($page)) {
            $result = $cacheObj->get($memcacheKey);
            if (!empty($result)) {
                return $result;
            }
        }*/   

        $start = $page * $pageSize;
        $sqlComm = "SELECT user_id, update_time FROM t_dolphin_medal_map WHERE medal_id =:_medal_id AND user_id > 0 ORDER BY map_id DESC LIMIT :_start, :_limit";
        $sqlData['_medal_id'] = $medalId;
        $sqlData['_start'] = $start;
        $sqlData['_limit'] = $pageSize;
        $result = DBMedalHelper::getConn()->read($sqlComm, $sqlData, FALSE, 'user_id');
        if (!empty($result)) {
			$userHelpper = new User();
			$userInfo = $userHelpper->getUserInfos(array_keys($result));
        }   
        foreach ($result as $key => $item) {
			if (empty($userInfo[$key])) {
				unset($result[$key]);
			}
            $result[$key]['avatar_c'] = !empty($userInfo[$key]['avatar_c']) ? $userInfo[$key]['avatar_c'] : '/css/images/0.gif';
            $result[$key]['nickname'] = !empty($userInfo[$key]['nickname']) ? $userInfo[$key]['nickname'] : '';
        }
        if (empty($page)) {
            $cacheObj->set($memcacheKey, $result, 3600);
        }
        return $result;
    }

    /** 
     * 添加一个申请
     * @param $mInfo array 包括 userId,medalId,content,ext_info,attachment
     * @param $ignore FALSE
     */
    function insertMedalApply($mInfo, $ignore = FALSE) {
		if (!empty($ignore)) {
        	$sqlComm = "INSERT IGNORE INTO t_dolphin_medal_apply (user_id, medal_id, content, ext_info, attachment, apply_time) VALUES (:_user_id, :_medal_id, :_content, :_ext_info, :_attachment, NOW())";
		}
		else {
        	$sqlComm = "INSERT INTO t_dolphin_medal_apply (user_id, medal_id, content, ext_info, attachment, apply_time) VALUES (:_user_id, :_medal_id, :_content, :_ext_info, :_attachment, NOW())";
		}
		$sqlData = array(
            '_user_id' => $mInfo['userId'],
            '_medal_id' => $mInfo['medalId'],
            '_content' => $mInfo['content'],
            '_ext_info' => $mInfo['ext_info'],
            '_attachment' => $mInfo['attachment'],
		);
		$result = DBMedalHelper::getConn()->write($sqlComm, $sqlData);
		return $result;
    }   

    /** 
     * 修改申请状态
     * @param $applyId
     * @param $status
     */
    function updateMedalApplyStatus($applyId, $status) { 
        $sqlComm = "UPDATE t_dolphin_medal_apply SET status={$status} WHERE apply_id={$applyId}";
		$result = DBMedalHelper::getConn()->write($sqlComm, array());
		return $result;
    }   

	/**
	 * 检查该勋章是否过期
	 */
    public function checkMedalAvailable($medalId) {
        $cacheObj = Memcache::instance();
        $memcacheKey = 'Medal:checkMedalAvailable:' . $medalId;

		$result = $cacheObj->get($memcacheKey);
		if (isset($result[$medalId])) {
			return $result[$medalId];
		}
		
        if (in_array($medalId, $this->outOfDateMedals) == TRUE) {
			$result = array($medalId => 0);
        }   
		else {
			$result = array($medalId => 1);
		}	
        $cacheObj->set($memcacheKey, $result, 86400);
        return $result[$medalId];
    }   	
}
