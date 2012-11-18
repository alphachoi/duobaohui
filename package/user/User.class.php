<?php
namespace Snake\Package\User;

/**
 * @author yishuliu@meilishuo.com
 * @since 2012-05-14
 * @version 1.0
 */

Use \Snake\Package\User\Helper\RedisUserConnectHelper;
Use \Snake\Package\User\Helper\RedisUserStatisticHelper;
Use \Snake\Package\User\Helper\CacheUserHelper;
Use \Snake\Package\User\Helper\DBUserHelper;
Use \Snake\Libs\Base\ZooClient;
Use \Snake\Package\Spam\SpamUser;
Use \Snake\libs\Cache\Memcache;

/**
 * @author yishuliu@meilishuo.com
 * @since 2012-05-14
 * @version 1.0
 */

class User {

	private $user = array();
	private $table = 't_dolphin_user_profile';
	private $extTable = 't_dolphin_user_profile_extinfo';
	private $profileParams = array('user_id', 'nickname', 'email', 'ctime', 'password', 'active_code', 'cookie', 'is_actived', 'invite_code', 'last_logindate', 'status', 'realname', 'istested', 'reg_from', 'last_email_time', 'level', 'isPrompt', 'isBusiness', 'login_times');
	private $extProfileParams = array('gender, birthday, province_id, city_id, about_me, avatar_c, is_taobao_buyer, verify_icons, verify_msg, weibo_url, is_taobao_seller');
	private $superUserParams = array('s_id', 'data_id', 'data_type', 'page_type', 'msg', 'imgurl', 'sortno', 'ctime', 'operatorid', 'user_type');
	private $superUserType = array('id', 'type_name', 'sortno', 'page_type');

	private $avatarTypes = array('a' => 'avatar_a', 'b' => 'avatar_b', 'd' => 'avatar_d', 'e' => 'avatar_e');
    private $userVTypes = array('t', 's', 'b');
	private $avatarArray = array(
                '/css/images/0.gif',    
                'css/images/0.gif',    
				'ap/c/a7/04/8504b5b4489fa19808ee7ff34da2_100_100.jpeg',
				'ap/c/e3/35/07fcaae925f55368632aa1021906_100_100.jpeg',
				'ap/c/e6/49/0a372d5d155e5be38dc22e43d3de_100_100.jpeg',
                'ap/c/41/48/1eaa34e2a4384b7d5bb9009dcd43_100_100.jpg',
                'ap/c/80/7b/1f0eb0b8262f5df08a0281f5b33a_100_100.jpg',
                'ap/c/f3/94/6857d75b2dac8a469d05ee11cc18_100_100.jpg',
                'ap/c/58/da/d4aedf49695ee939309f50102073_128_128.png',
                'ap/c/fc/06/ad8fac52975c449395d1de74d075_100_100.jpg',    
                'ap/c/09/a8/7d60f32f1e842c83ac1f25386dcd_120_120.png',    
				'ap/c/cb/72/b0c84941e79d4cda8137f12310c7_120_120.png',
                'ap/c/b9/9a/10c1753b1430455bff57031fa1c1_180_180.gif',    
                'ap/c/64/9d/4912bf9c0458f48756bca1bd5b13_80_80.gif',    
                'ap/c/ca/55/52722bd27a5bd0ae78e5de2a63b0_80_80.png', 
                'ap/c/f5/1f/b71ab7042046661450087b3e8bb6_200_200.jpg'); 

	public function __construct($user = array()) {
		$this->user = $user;
	}

	public function __get($name) {
		if (!is_array($this->user)) {
			return NULL;
		}
		if (array_key_exists($name, $this->user)) {
			return $this->user[$name];
		}
		return NULL;
	}
	
	public function __set($name, $value) {
		$this->user[$name] = $value;
	}

	public function getUser() {
		return $this->user;
	}

	/*
	 *  private method
	 *
	 *	@param array $userInfo 
	 *  @param boolean $isNoKeyArray 是否是一维数组 默认传入为二维数组
	 *	@return array $userInfo replace avatat_c with whole url
	 *
	 */
	private function _getUserPicConvert ($userInfos, $isNoKeyArray = FALSE) {
		if (empty($isNoKeyArray)) {
			foreach ($userInfos as $key => $value) {
				if (isset($value['avatar_a'])) {
					$userInfos[$key]['avatar_a'] = $this->_convertAvaterUrl($value['avatar_a']);
				}
				if (isset($value['avatar_b'])) {
					$userInfos[$key]['avatar_b'] = $this->_convertAvaterUrl($value['avatar_b']);
				}
				if (isset($value['avatar_c'])) {
					$userInfos[$key]['avatar_c'] = $this->_convertAvaterUrl($value['avatar_c']);
				}
				if (isset($value['avatar_d'])) {
					$userInfos[$key]['avatar_d'] = $this->_convertAvaterUrl($value['avatar_d']);
				}
				if (isset($value['avatar_e'])) {
					$userInfos[$key]['avatar_e'] = $this->_convertAvaterUrl($value['avatar_e']);
				}
			}
		}
		else {
			if (isset($userInfos['avatar_a'])) {
				$userInfos['avatar_a'] = $this->_convertAvaterUrl($userInfos['avatar_a']);
			}
			if (isset($userInfos['avatar_b'])) {
				$userInfos['avatar_b'] = $this->_convertAvaterUrl($userInfos['avatar_b']);
			}
			if (isset($userInfos['avatar_c'])) {
				$userInfos['avatar_c'] = $this->_convertAvaterUrl($userInfos['avatar_c']);
			}
			if (isset($userInfos['avatar_d'])) {
				$userInfos['avatar_d'] = $this->_convertAvaterUrl($userInfos['avatar_d']);
			}
			if (isset($userInfos['avatar_e'])) {
				$userInfos['avatar_e'] = $this->_convertAvaterUrl($userInfos['avatar_e']);
			}
		}
		return $userInfos;
	}
	
	/*
     * 转换用户的头像地址为URL
     * 现在同时支持新旧地址
     * @param string $picPath
     * @return string $avatarUrl
     */
    private function _convertAvaterUrl($key) {
        $key = trim($key);
		$result = \Snake\Libs\Base\Utilities::convertPicture($key);
        return $result;
   	} 
  
	/** 
     * Get user info from t_dolphin_user_profile and
	 * t_dolphin_user_profile_extinfo. <br/>
     * @author yishuliu@meilishuo.com 
     * @param integer $userId <br/>
     * @param array $params select variable <br/>
     * @return array with user_id as hash_key <br/>
     */	
	public function getUserInfo($userId, $params = array('nickname', 'email', 'ctime', 'is_actived', 'status', 'realname', 'istested', 'reg_from', 'last_email_time', 'level', 'isPrompt', 'isBusiness', 'login_times', 'gender', 'birthday', 'province_id', 'city_id', 'about_me', 'avatar_a', 'avatar_b', 'avatar_c', 'avatar_d', 'avatar_e', 'is_taobao_buyer', 'verify_icons', 'verify_msg', 'weibo_url', 'is_taobao_seller'), $fromMaster = FALSE, $isWholePicPath = TRUE) {
		if (empty($userId)) {
			return FALSE;
		}
		$cacheHelper = Memcache::instance();
		$cacheKey = "User:getUserInfo:{$userId}";

		$userInfo = $cacheHelper->get($cacheKey);
		if (empty($userInfo)) {
			$col = 'nickname, email, ctime, is_actived, status, realname, istested, reg_from, last_email_time, level, isPrompt, isBusiness, login_times';
			$sql = "SELECT /*User-lys*/ user_id, $col FROM t_dolphin_user_profile WHERE user_id = :_user_id ";	

			$colExt = 'gender, birthday, province_id, city_id, about_me, avatar_c, is_taobao_buyer, verify_icons, verify_msg, weibo_url, is_taobao_seller';
			$sqlExt = "SELECT /*User-lys*/ user_id, $colExt FROM t_dolphin_user_profile_extinfo WHERE user_id = :_user_id ";	
			
			$sqlData = array();
			$sqlData['_user_id'] = $userId;
			
			$result = DBUserHelper::getConn()->read($sql, $sqlData, $fromMaster, 'user_id');
			$resultExt = DBUserHelper::getConn()->read($sqlExt, $sqlData, $fromMaster, 'user_id');

			foreach ($resultExt as $key => $value) {
				/*if (!isset($resultExt[$key]['avatar_c']) || empty($resultExt[$key]['avatar_c'])) {
					$resultExt[$key]['avatar_c'] = !empty($resultExt[$key]['avatar_a']) ? str_replace('/a/', '/c/', $resultExt[$key]['avatar_a']) : \Snake\Libs\Base\Utilities::convertPicture('/css/images/0.gif');
				}*/
				if (!empty($isWholePicPath) && strpos($resultExt[$key]['avatar_c'], 'http://') === FALSE) {
					$resultExt[$key] = $this->_getUserPicConvert($resultExt[$key], TRUE);
				}
				if (isset($value['verify_msg'])) {
					$resultExt[$key]['verify_msg'] = json_decode($resultExt[$key]['verify_msg'], TRUE); //verify_msg在数据库中是json数据，需要json_decode
					//$resultExt[$key]['verify_msg'] = json_decode($resultExt[$key]['verify_msg']); //verify_msg在数据库中是json数据，需要json_decode
				}
				if (isset($value['verify_icons'])) {
					$resultExt[$key]['verify_icons'] = explode(',', $value['verify_icons']);
				}

				//根据用户verify_icons数据返回对应浮窗悬浮值
				$extraMsg = $this->assembleMsg($resultExt[$key], $userId);
				$resultExt[$key] = array_merge($resultExt[$key], $extraMsg);

				if ($value['user_id'] == $result[$key]['user_id']) {
					$result[$key] = array_merge($result[$key], $resultExt[$key]);
					//去除新浪微博等互联过来的用户nickname带＃问题
					$result[$key] = $this->_getNickName($result[$key]);
				}
			}

			$cacheHelper->set($cacheKey, $result[$userId], 3600);
		}	
		else {
			$result = array($userId => $userInfo);
		}
		if (!empty($isWholePicPath) && strpos($result[$userId]['avatar_c'], 'http://') === FALSE) {
			$result = $this->_getUserPicConvert($result);
		}
		$newResult = array();
		if (!empty($params)) {
			foreach ($params as $key => $value) {
				if ($value == 'user_id') {
					continue;
				}
				//将avatar_c转化成其他avatar头像
				if (in_array($value, $this->avatarTypes)) {
					$newResult[$value] = $this->picConvertFromAvatarC($result[$userId]['avatar_c'], substr($value, strlen('avatar_')));
				}
				else {
					$newResult[$value] = $result[$userId][$value];
				}
			}
			$newResult['user_id'] = $result[$userId]['user_id'];
		}
		
		//先暂时这样写 上线一天后去掉
		if (isset($newResult['verify_icons']) && !is_array($newResult['verify_icons'])) {
			$newResult['verify_icons'] = explode(',', $newResult['verify_icons']);
		}
		if (isset($newResult['verify_msg']) && !is_array($newResult['verify_msg'])) {
			$newResult['verify_msg'] = (array) $newResult['verify_msg'];
		}

		//根据用户verify_icons数据返回对应浮窗悬浮值
		$extraMsg = $this->assembleMsg($newResult, $userId);
		foreach ($extraMsg as $item => $evalue) {
			$newResult[$item] = $evalue;
		}    
		
		$this->user = $newResult;
		return $this->user;
	}

	/** 
     * Get user infos from t_dolphin_user_profile and
	 * t_dolphin_user_profile_extinfo. <br/>
     * @author yishuliu@meilishuo.com
     * @param array $userIds <br/>
     * @param array $params select variable <br/>
     * @return array with user_id as hash_key <br/>
     */	
	public function getUserInfos($uIds, $params = array('nickname', 'avatar_c'), $setHashKey = TRUE, $fromMaster = FALSE, $isWholePicPath = TRUE) {
		if (empty($uIds)) {
			return FALSE;
		}
		$userInfos = array();
		foreach ($uIds as $key => $singleId) {
			if (!empty($setHashKey)) {
				$userInfos[$singleId] = $this->getUserInfo($singleId, $params, $fromMaster, $isWholePicPath);
			}
			else {
				$userInfos[] = $this->getUserInfo($singleId, $params, $fromMaster, $isWholePicPath);
			}
		}
		return $userInfos;

		$cacheHelper = Memcache::instance();
		//判断每一个user是否有cache
		$userInfos = array();
		$noCacheUids = array();
		foreach ($uIds as $key => $singleId) {
        	$cacheKey = "User:getUserInfo:{$singleId}";
        	$userInfo = $cacheHelper->get($cacheKey);	
			if (empty($userInfo)) {
				$noCacheUids[] = $singleId;
			}
			else {
				$userInfos[$singleId] = $this->getUserInfo($singleId, $params, $fromMaster, $isWholePicPath);
			}
		}
		if (!empty($noCacheUids)) {
			$userIds = implode(',', $noCacheUids);

			$col = 'nickname, email, ctime, is_actived, status, realname, istested, reg_from, last_email_time, level, isPrompt, isBusiness, login_times';
			$sql = "SELECT /*User-lys*/ user_id, $col FROM t_dolphin_user_profile WHERE user_id in ($userIds)";	

			$colExt = 'gender, birthday, province_id, city_id, about_me, avatar_c, is_taobao_buyer, verify_icons, verify_msg, weibo_url, is_taobao_seller';
			$sqlExt = "SELECT /*User-lys*/ user_id, $colExt FROM t_dolphin_user_profile_extinfo WHERE user_id in ($userIds)";	

			$result = DBUserHelper::getConn()->read($sql, array(), $fromMaster, 'user_id');
			$resultExt = DBUserHelper::getConn()->read($sqlExt, array(), $fromMaster, 'user_id');

			foreach ($resultExt as $key => $value) {
				if (!isset($resultExt[$key]['avatar_c']) || empty($resultExt[$key]['avatar_c'])) {
					$resultExt[$key]['avatar_c'] = !empty($resultExt[$key]['avatar_a']) ? str_replace('/a/', '/c/', $resultExt[$key]['avatar_a']) : \Snake\Libs\Base\Utilities::convertPicture('/css/images/0.gif');
				}
				if (!empty($isWholePicPath) && strpos($resultExt[$key]['avatar_c'], 'http://') === FALSE) {
					$resultExt[$key] = $this->_getUserPicConvert($resultExt[$key], TRUE);
				}
				if (isset($value['verify_msg'])) {
					$resultExt[$key]['verify_msg'] = json_decode($resultExt[$key]['verify_msg']); //verify_msg在数据库中是json数据，需要json_encode
				}
				if ($value['user_id'] == $result[$key]['user_id']) {
					$result[$key] = array_merge($result[$key], $resultExt[$key]);
					//去除新浪微博等互联过来的用户nickname带＃问题
					$result[$key] = $this->_getNickName($result[$key]);
				}
				$memKey = "User:getUserInfo:{$value['user_id']}";
				$cacheHelper->set($memKey, $result[$key], 3600);

			}
			//根据传入参数取得所需要的参数，即去掉多余参数
			$newUserInfos = $this->_removeExtraParams($result, $params);
		}

		//merge从memcache中取得的数据和sql查询的数据
		$newResult = array();
		if (empty($userInfos)) {
			$newResult = $newUserInfos;
		}
		elseif (empty($newUserInfos)) {
			$newResult = $userInfos;
		}
		else {
			$newResult = $userInfos + $newUserInfos;
		}
		if (empty($newResult)) {
			return FALSE;
		}

		$hasKeyResult = array();
		$noKeyResult = array();

		if (!empty($setHashKey)) {
			foreach ($newResult as $key => $value) {
				$hasKeyResult[$value['user_id']] = $value;
			}
			return $hasKeyResult;
		}
		else {
			foreach ($uIds as $key => $uId) {
				$noKeyResult[] = $newResult[$uId];
			}
			/*$userIds = array_flip($uIds); 
			foreach ($newResult as $key => $value) {
				$noKeyResult[$userIds[$value['user_id']]] = $value;
			}
			ksort($noKeyResult);*/
			return $noKeyResult;
		}
	}
	
	private function _removeExtraParams($userInfos, $params) {
        //$newResult = array();
        if (!empty($params)) {
			foreach ($userInfos as $uKey => $uvalue) {
				foreach ($params as $key => $value) {
					if ($value == 'user_id') {
						continue;
					}
					//将avatar_c转化成其他avatar头像
					elseif (in_array($value, $this->avatarTypes)) {
						$newResult[$uvalue['user_id']][$value] = $this->picConvertFromAvatarC($uvalue['avatar_c'], substr($value, strlen('avatar_')));
					}					
					else {
						$newResult[$uvalue['user_id']][$value] = $userInfos[$uKey][$value];
					}
				}
				$newResult[$uvalue['user_id']]['user_id'] = $userInfos[$uKey]['user_id'];
			}
        }
		return $newResult;
	}

	/**
	 * 批量返回用户基本信息
	 */
	public function getUserBaseInfos($userIds, $fields = array('user_id', 'nickname'), $key = FALSE, $fromMaster = FALSE) {
		if (empty($userIds) || empty($fields)) {
			return FALSE;
		}
		!is_array($fields) && $fields = array($fields);
		!is_array($userIds) && $userIds = array($userIds);
		$fields = implode(',', $fields);
		$userIds = implode(',', $userIds);

		$sqlComm = "SELECT {$fields} FROM {$this->table} WHERE user_id IN ({$userIds})";
		$sqlData = array();
		$result = DBUserHelper::getConn()->read($sqlComm, $sqlData, $fromMaster);	
		if (empty($result)) {
			return array();
		}
		if ($key === TRUE && strpos($fields, 'user_id') !== FALSE) {
			$oResult = array();
			foreach ($result as $index => $uInfo) {
				$uid = $uInfo['user_id'];	
				$oResult[$uid] = $uInfo;
			}
			$result = $oResult;
		}
		return $result;
	}

	/** 
     * Get user base infos from t_dolphin_user_profile. <br/>
     * @author yishuliu@meilishuo.com <br/>
     * @param int $userId <br/>
     * @param array $params select variable <br/>
     * @return array with user_id as hash_key <br/>
     */	
	public function getUserBaseInfo($userId, $params = array('user_id', 'nickname'), $fromMaster = FALSE) {
		if (empty($params) || empty($userId)) {
			return FALSE;
		}	
		$cacheHelper = Memcache::instance();
		$cacheKey = "User:getUserBaseInfo:{$userId}";
		//$result = $cacheHelper->get($cacheKey);
		//print_r($result);die;
		if (empty($result)) {
			$str = implode(',', $this->profileParams);
			$sql = "SELECT /*User-lys*/ $str FROM t_dolphin_user_profile WHERE user_id = :_user_id ";	

			$sqlData = array();
			$sqlData['_user_id'] = $userId;
			$baseInfo = DBUserHelper::getConn()->read($sql, $sqlData, $fromMaster);
			//去除新浪微博等互联过来的用户nickname带＃问题
			$baseInfo[0] = $this->_getNickName($baseInfo[0]);
			$result = $baseInfo[0]; 
			$cacheHelper->set($cacheKey, $result, 60 * 5);
		}

		$newResult = array();
		if (!empty($params)) {
			foreach ($params as $key => $value) {
				if ($value == 'user_id') {
					continue;
				}
				$newResult[$value] = $result[$value];
			}
			$newResult['user_id'] = $result['user_id'];
		}
		$this->user = $newResult;
		return $this->user;
	}

	public function getUserExtInfos($userIds, $fields = array('user_id', 'province_id', 'city_id'), $key = FALSE, $fromMaster = FALSE) {
		if (empty($userIds) || empty($fields)) {
			return FALSE;
		}
		!is_array($userIds) && $userIds = array($userIds); 
		!is_array($fields) && $fields = array($fields);
		$userIds = implode(',', $userIds);
		
		//$str = implode(',', $this->extProfileParams);
		//$sqlComm = "SELECT user_id, $str FROM {$this->extTable} WHERE user_id IN ({$userIds})";
		$fields = implode(',', $fields);
		$sqlComm = "SELECT {$fields} FROM {$this->extTable} WHERE user_id IN ({$userIds})";
		$result = DBUserHelper::getConn()->read($sqlComm, array(), $fromMaster);
		if (empty($result)) {
			return array();
		}
		if ($key === TRUE && strpos($fields, 'user_id') !== FALSE) {
			$oResult = array();
			foreach ($result as $uInfo) {
				/*foreach ($fields as $key => $value) {
					if (in_array($value, $this->avatarTypes)) {
						$oResult[$uInfo['user_id']][$value] = $this->picConvertFromAvatarC($uInfo['avatar_c'], substr($value, strlen('avatar_')));
					}
					else {
						$oResult[$uInfo['user_id']][$value] = $uInfo[$value];
					}
				}*/
				$uid = $uInfo['user_id'];
				$oResult[$uid] = $uInfo;	
			}
			$result = $oResult;
		}
		return $result;
	}

	/** 
     * Get user ext infos from t_dolphin_user_profile_extinfo. <br/>
     * @author yishuliu@meilishuo.com <br/>
     * @param int $userId <br/>
     * @param array $params select variable <br/>
     * @return array with user_id as hash_key <br/>
     */	
	public function getUserExtInfo($userId, $params = array('user_id', 'province_id', 'city_id'), $fromMaster = FALSE) {
		if (empty($params) || empty($userId)) {
			return FALSE;
		}	

		$cacheHelper = Memcache::instance();
		$cacheKey = "User:getUserExtInfo:{$userId}";
		$result = $cacheHelper->get($cacheKey);
		if (empty($result)) {
			$str = implode(',', $this->extProfileParams);
			$sql = "SELECT /*User-lys*/ user_id, $str FROM t_dolphin_user_profile_extinfo WHERE user_id = :_user_id ";	
			$sqlData = array();
			$sqlData['_user_id'] = $userId;
			$result = DBUserHelper::getConn()->read($sql, $sqlData, $fromMaster, 'user_id');
			foreach ($result as $key => $value) {
				if (isset($value['verify_msg'])) {
					$result[$key]['verify_msg'] = json_decode($value['verify_msg'], TRUE); //verify_msg在数据库中是json数据，需要json_encode
				}
                if (isset($value['verify_icons'])) {
                    $result[$key]['verify_icons'] = explode(',', $value['verify_icons']);
                }
                //根据用户verify_icons数据返回对应浮窗悬浮值
                $extraMsg = $this->assembleMsg($result[$key], $userId);
                $result[$key] = array_merge($result[$key], $extraMsg);
			}
			$cacheHelper->set($cacheKey, $result, 60);
		}
		$newResult = array();
		if (!empty($params)) {
			foreach($params as $key => $value) {
                //将avatar_c转化成其他avatar头像
                if (in_array($value, $this->avatarTypes)) {
                    $newResult[$value] = $this->picConvertFromAvatarC($result[$userId]['avatar_c'], substr($value, strlen('avatar_')));
                }
				else {
					$newResult[$value] = $result[$userId][$value];
				}
			}
		}
		return $newResult;
	}

    /**
     * 更新用户的扩展信息
     * @param array $updateData
     */
	public function updateUserExtInfo($updateData = array()) {
        if (!isset($updateData['user_id'])) {
             return FALSE;
        }
		$cacheHelper = Memcache::instance();
		//$uidCache = md5($userId . implode(',', $params) . $fromMaster);
		$cacheKey = "User:getUserExtInfo:{$updateData['user_id']}";
		$memKey = "User:getUserInfo:{$updateData['user_id']}";

        $sqlComm = "UPDATE t_dolphin_user_profile_extinfo SET";
        $sqlData = $updateData;
        $updateBy = "";
        foreach($updateData as $key => $value) {
            if ($key != "user_id") {
                $sqlComm .= " $key=:$key,";
            }
            else {
                $updateBy = " WHERE $key=:$key";
            }
        }

        $sqlComm = substr_replace($sqlComm, $updateBy, -1);
		DBUserHelper::getConn()->write($sqlComm, $sqlData);

		$cacheHelper->delete($cacheKey);
		$cacheHelper->delete($memKey);
        return TRUE;
    }

	/**
	 * 更新用户的个信基本信息
	 * @param $updateData array 
	 */
	public function updateUserInfo($updateData = array()) {
		if (empty($updateData['user_id'])) {
			return FALSE;
		}	
		$cacheHelper = Memcache::instance();
		$cacheKey = "User:getUserBaseInfo:{$updateData['user_id']}";
		$memKey = "User:getUserInfo:{$updateData['user_id']}";

		$sqlComm = "UPDATE t_dolphin_user_profile SET";
		$updateBy = '';
		foreach ($updateData as $key => $value) {
			if ($key != 'user_id') {
				$sqlComm .= " {$key} =: {$key}";
			}
			else {
				$updateBy = " WHERE {$key} =: {$key}";
			}
		}	
		$sqlComm = $sqlComm . $updateBy;
		DBUserHelper::getConn()->write($sqlComm, $updateData);

		$cacheHelper->delete($cacheKey);
		$cacheHelper->delete($memKey);
		return TRUE;
	}

	// 根据用户uid得到用户所在省份
	public function getUserProvince($userId, $fromMaster = FALSE) {
		if (empty($userId)) {
			return FALSE;
		}
		$userExtInfo = $this->getUserExtInfo($userId);
		if (empty($userExtInfo['province_id'])) {
			return FALSE;
		}
		$sqlCommProvince = "SELECT S_PROVNAME FROM t_dolphin_dictionary_province WHERE N_PROVID = :_provid";
		$sqlData = array('_provid' => $userExtInfo['province_id']);
		$resultPro = DBUserHelper::getConn()->read($sqlCommProvince, $sqlData, $fromMaster);	
		//print_r($sqlData);die;
		$this->user['province'] = $resultPro[0];
		return $this->user['province'];
	}

	// 根据用户uid得到用户所在城市
	public function getUserCity($userId, $fromMaster = FALSE) {
		if (empty($userId)) {
			return FALSE;
		}
		$userExtInfo = $this->getUserExtInfo($userId);
		if (empty($userExtInfo['city_id'])) {
			return FALSE;
		}
		$sqlCommProvince = "SELECT S_CITYNAME FROM t_dolphin_dictionary_city WHERE N_CITYID = :_cityid";
		$sqlData = array('_cityid' => $userExtInfo['city_id']);
		$resultPro = DBUserHelper::getConn()->read($sqlCommProvince, $sqlData, $fromMaster);	
		$this->user['city'] = $resultPro[0];
		return $this->user['city'];
	}

	/** 
     * Get user label from t_dolphin_personal_label and
	 * t_dolphin_label_info. <br/>
     * @author yishuliu@meilishuo.com <br/>
     * @param integer $userId <br/>
     * @param array $params select variable <br/>
     * @return array with label_id as hash_key <br/>
     */	
	public function getUserLabel($userId, $params = array('user_id', 'label_name', 'label_id', 'type')) {
		$personalLabel = array('user_id', 'label_id', 'ctime');
		$paramsUser = array();
		$paramsUserExt = array();
		foreach ($params as $key => $value) {
			if ($value == '*') {
				$paramsUser = $personalLabel;
				continue;
			}
			if ($value == 'user_id' || $value == 'label_id') {
				continue;
			}
			if (in_array($value, $personalLabel)) {
				$paramsUser[] = $value;
			}
			else {
				$paramsUserExt[] = $value;
			}	
		}

		$str = implode(',', $paramsUser);
		$strComm = implode(',', $paramsUserExt);
		//print_r($strComm);exit;
		if (!empty($str)) {
			$sql = "SELECT /*User-lys*/ user_id, label_id, $str FROM t_dolphin_personal_label WHERE user_id = :_user_id";	
		}
		else {
			$sql = "SELECT /*User-lys*/ user_id, label_id FROM t_dolphin_personal_label WHERE user_id = :_user_id";
		}
		$sqlData = array('_user_id' => $userId);
		$result = DBUserHelper::getConn()->read($sql, $sqlData, FALSE, 'label_id');	
		foreach ($result as $key => $value) {
			$labelIds[] = $result[$key]['label_id'];
		}
		if (!empty($labelIds)) { 
			$labelIds = implode(',', $labelIds);
			if (!empty($strComm)) {
				$sqlComm = "SELECT /*User-lys*/ label_id, $strComm FROM t_dolphin_label_info WHERE label_id in ($labelIds)";	
			}
			else {
				$sqlComm = "SELECT /*User-lys*/ label_id FROM t_dolphin_label_info WHERE label_id in ($labelIds)";	
			}
			$resultInfo = DBUserHelper::getConn()->read($sqlComm, array(), FALSE, 'label_id');	
		}
		if (!is_array($resultInfo) || empty($resultInfo)) {
			return FALSE;
		}
		$return = array();
		foreach ($resultInfo as $key => $value) {
			if ($value['label_id'] == $result[$key]['label_id']) {
				$return[] = array_merge($result[$key], $resultInfo[$key]);
			}
		}
		$this->user['label'] = $return;
		return $this->user['label'];
	}

	/** 
     * Get user info from t_dolphin_user_profile. <br/>
     * @author yishuliu@meilishuo.com 
     * @param string $userData (user cookie value) <br/>
     * @param array $params select variable <br/>
     * @return array userInfo <br/>
     */	
	public function getUserFromCookie($userData, $params = array('*', 'avatar_c', 'avatar_d'), $isWholePicPath = FALSE, $fromMaster = FALSE) {
		if (empty($userData)) {
			return FALSE;
		}
		if (is_array($userData)) {
			$result = array_values($userData);
			$userData = $result[0];
		}

		$cacheHelper = Memcache::instance();
		$cacheKey = "User:getUserFromCookie:{$userData}";
		$userCookieInfo = $cacheHelper->get($cacheKey);
		if (!empty($userCookieInfo)) {
			//头像全路径
			if (!empty($isWholePicPath) && strpos($userCookieInfo['avatar_c'], 'http://') === FALSE) {
				$userCookieInfo = $this->_getUserPicConvert($userCookieInfo, TRUE);
			}
			$this->user = $userCookieInfo;
			return $userCookieInfo;
		}

		$profileParams = $this->profileParams;
		foreach ($params as $key => $value) {
			if ($value == '*') {
				$paramsUser = $profileParams;
				continue;
			}
			if ($value == 'user_id') {
				continue;
			}
			if (in_array($value, $profileParams)) {
				$paramsUser[] = $value;
			}
			else {
				$paramsUserExt[] = $value;
			}	
		}

		foreach ($paramsUser AS $key => $value) {
			if ($paramsUser[$key] == 'is_mall') {
				unset($paramsUser[$key]);
				break;
			}
		}
		$str = implode(',', $paramsUser);
		$sql = "SELECT /*User-lys*/ user_id, $str FROM t_dolphin_user_profile WHERE cookie =:cookie";	
		$sqlData['cookie'] = $userData;
		$result = DBUserHelper::getConn()->read($sql, $sqlData, $fromMaster);

		foreach ($paramsUserExt AS $key => $value) {
			if ($paramsUserExt[$key] == 'is_mall' || $paramsUserExt[$key] == 'avatar_d') {
				unset($paramsUserExt[$key]);
				break;
			}
		}
		$userId = $result[0]['user_id'];
		$strExt = implode(',', $paramsUserExt);
		$sqlExt = "SELECT /*User-lys*/ user_id, $strExt FROM t_dolphin_user_profile_extinfo WHERE user_id = :_user_id";	
		$sqlDataExt['_user_id'] = $userId;
		$resultExt = DBUserHelper::getConn()->read($sqlExt, $sqlDataExt, $fromMaster);
        
		foreach ($resultExt as $key => $value) {
			if ($value['user_id'] == $result[$key]['user_id']) {
				$result[$key] = array_merge($result[$key], $resultExt[$key]);
			}
		}
		$result[0]['avatar_d'] = $this->picConvertFromAvatarC($result[0]['avatar_c'], 'd');
        
        if (!empty($userId)) {
            $sql = "SELECT /*User-sunsl*/ uid FROM t_dolphin_mall_profile WHERE uid = $userId";
            $isMall = DBUserHelper::getConn()->read($sql, array(), $fromMaster);
            if (!empty($isMall)) {
                $result[0]['is_mall'] = 1;
            } 
        }
        
		$spamHelper = new SpamUser();
		$day = $spamHelper->getInvalidTime($userId);
        $result[0]['block_remain'] = $day;	
		
		//头像全路径
		if (!empty($isWholePicPath) && strpos($result[0]['avatar_c'], 'http://') === FALSE) {
			$result = $this->_getUserPicConvert($result);
		}
		//去除新浪微博等互联过来的用户nickname带＃问题
		$result[0] = $this->_getNickName($result[0]);
		$this->user = $result[0];
		$cacheHelper->set($cacheKey, $result[0], 60 * 15);
		return $this->user;
	}
	
	/**  
     * 根据用户名更改用户密码
     * @param  $username
     * @param  $password
     * @return bool
     */
    public function getUserBaseInfoByUsernameAndPassword($username, $password) {
        $sqlComm = "SELECT * FROM t_dolphin_user_profile WHERE (email=:username OR nickname = :username) AND password=:password";
        $sqlData['username'] = $username;
        //$sqlData['password'] = md5($password);
        $sqlData['password'] = $password;
        $result = array();

		$result = DBUserHelper::getConn()->read($sqlComm, $sqlData, TRUE);	
        if (isset($result[0]['user_id']) && empty($result[0]['user_id']) == FALSE) {
			//去除新浪微博等互联过来的用户nickname带＃问题
			$result[0] = $this->_getNickName($result[0]);
			$this->user = $result[0];
            return $this->user;
        } else {
            return FALSE;
        }    
    }    

	/**  
     * 根据用户名或email获得用户信息
     * @param  $email_or_nickname
     * @return array
     */
    public function getUserBaseInfoByNicknameOrEmail($email_or_nickname, $str = 'user_id') {
		if (empty($email_or_nickname)) {
			return FALSE;
		}
        $sqlComm = "SELECT {$str} FROM t_dolphin_user_profile WHERE (email=:username OR nickname = :username)";
        $sqlData['username'] = $email_or_nickname;
        $result = array();

		$result = DBUserHelper::getConn()->read($sqlComm, $sqlData);	
        if (isset($result[0]['user_id']) && empty($result[0]['user_id']) == FALSE) {
			//去除新浪微博等互联过来的用户nickname带＃问题
			$result[0] = $this->_getNickName($result[0]);
			$this->user = $result[0];
            return $this->user;
        } else {
            return FALSE;
        }    
    }    

    /**
     * 查询用户的globalKey
     * @param $user_id
     *
     */
    public function checkGlobalKey($user_id, $fromMaster = FALSE) {//查询登录用户的globalKey
        $sqlComm = "SELECT global_key FROM t_dolphin_user_gkey WHERE user_id = :user_id";
        $sqlData['user_id'] = $user_id;
        $result = array();
		$result = DBUserHelper::getConn()->read($sqlComm, $sqlData, $fromMaster);
        return $result;
    }

    /**
     * 插入新的记录，用户的globalKey,uid,time
     * @param $user_id,$cookie_key,$time用户的注册时间
     *
     */
    public function setGlobalKey($globalKey, $uid, $time) {//插入用户的globalKey
		$log = new \Snake\Libs\Base\SnakeLog("global_key", "normal");
		$log->w_log(print_r(array($globalKey, $uid, $time), true));
        $sqlComm = "INSERT IGNORE INTO t_dolphin_user_gkey (global_key,user_id,register_time) VALUES (:global_key,:user_id,:register_time) ";
        $sqlData['global_key'] = $globalKey;
        $sqlData['user_id'] = $uid;
        $sqlData['register_time'] = $time;

		DBUserHelper::getConn()->write($sqlComm, $sqlData);
        return TRUE;
    }


    public function updateGlobalKey($globalKey, $user_id) {//插入用户的globalKey
        $sqlComm = "UPDATE t_dolphin_user_gkey SET global_key =:global_key WHERE user_id =:user_id";
        $sqlData['global_key'] = $globalKey;
        $sqlData['user_id'] = $user_id;
		DBUserHelper::getConn()->write($sqlComm, $sqlData);
        return TRUE;
    }    

	/**
     * 向user_ip插入一条记录
     * @param $user_id 用户编号
     * @param $clientIP 用户IP 
     */
    public function insertUserIP($user_id, $clientIP) {
        if (empty($user_id) || empty($clientIP)) {
            return FALSE;
        }
        $sqlComm = "INSERT INTO t_dolphin_user_ip (user_id, ip) VALUES (:_user_id, :_ip)";
        $sqlData = array(
            '_user_id' => $user_id,
            '_ip' => $clientIP,
        );
        DBUserHelper::getConn()->write($sqlComm, $sqlData);
        return TRUE;
    }


	/**
     * 更新用户的免登录cookie信息
     * @param  $user_id TODO
     * @param  $cookie
     */
    public function updateUserCookie($user_id, $cookie = NULL) {
        if (empty($cookie)) {
            $client = ZooClient::getClient($user_id);
            $client->user_login($user_id, $_SERVER['REQUEST_TIME']);
            return TRUE;
        }
        $sqlComm = "UPDATE t_dolphin_user_profile SET cookie =:cookie, login_times = login_times + 1, last_logindate = now() WHERE user_id =:user_id ";
        $sqlData = array();
        $sqlData['user_id'] = $user_id;
        $sqlData['cookie'] = $cookie;
		$result = DBUserHelper::getConn()->write($sqlComm, $sqlData);
        return $result;
    }

	public function updateLogonInfo($userId) {
		$sql = "UPDATE t_dolphin_user_profile SET login_times = login_times + 1, last_logindate = now() WHERE user_id = :_user_id";
		$sqlData['_user_id'] = $userId;
		DBUserHelper::getConn()->write($sql, $sqlData);
	}

	public function getUserStatistic($userId) {
		$result = RedisUserStatisticHelper::getUserStatistic($userId);
		$this->user['statistic'] = $result;
		return $this->user['statistic'];
	}

	public function getUserOnline($userId) {
		$result = CacheUserHelper::getUserOnline($userId);
		$this->user['online_status'] = $result;
		return $this->user['online_status'];
	}

	public function getUserLikeTwitters($userId, $start, $limit) {
		//the client has been initialized by the abstract Controller
		$client = ZooClient::getClient();
		$result = $client->user_likes_twitters($userId, $start, $limit);
		return $result;
	}

	/** 
     * 剔除没有用户头像的用户id <br/>
     * @author yishuliu@meilishuo.com
     * @param array $users<br/>
     * @return array array[0]有头像的用户，array[1]没有头像的用户<br/>
     */	
	public function filterUserAvatar($users, $avatar = "avatar_c") {
		if (empty($users)) {
			return array(array(),array());
		}
        $userHasPics = array();
        $userHasNotPics = array();
		foreach ($users as $key => $value) {
			$avatarSuffix = "";
			if (strpos($value[$avatar], 'http://') !== FALSE) {
				$urlArray = array();
				$urlArray = parse_url($value[$avatar]);
				$path = $urlArray['path'];
				if (substr($path, 0, 1) == '/') {
					$avatarSuffix = substr($path, 1);
				}
				else {
					$avatarSuffix = $path;
				}
			}

			if (!in_array($avatarSuffix, $this->avatarArray) && !empty($avatarSuffix)) {
                $userHasPics[] = $value;   
            }    
			else {
				$userHasNotPics[] = $value;
			}
		}
		return array($userHasPics, $userHasNotPics);
	}


	/** 
     * 剔除没有用户头像的用户id <br/>
     * @author yishuliu@meilishuo.com
     * @param array $userIds <br/>
     * @return array userIds 有用户头像的用户userIds数组 <br/>
     */	
	public function filterByAvatar($userIds) {
        $userIds = array_unique($userIds);
        $userInfos = $this->getUserInfos($userIds, array('nickname', 'avatar_c', 'is_uploaded'), TRUE, FALSE, FALSE);
        $userHasPics = array();
        $userHasNotPics = array();
		$noAvatar = $this->avatarArray;
        foreach ($userIds as $userId) {
			if (strpos($userInfos[$userId]['avatar_c'], 'http://') !== FALSE) {
				$urlArray = array();
				$urlArray = parse_url($userInfos[$userId]['avatar_c']);
				$path = $urlArray['path'];
				if (substr($path, 0, 1) == '/') {
					$userInfos[$userId]['avatar_c'] = substr($path, 1);
				}
				else {
					$userInfos[$userId]['avatar_c'] = $path;
				}
			}
            if (!in_array($userInfos[$userId]['avatar_c'], $noAvatar) && !empty($userInfos[$userId]['avatar_c'])) {
                $userHasPics[] = $userId;   
            }    

            //else {
            //  $userHasNotPics[] = array('user_id' => $userId, 'avatar' => $userInfos[$userId]['avatar_c']);   
            //}  
        }    
        return $userHasPics;
    }    

	/**
	 * 插入用户基本信息
	 * @param $userInfo array
	 *
	 * @return insertId
	 * @author ChaoGuo
	 */
	public function insertUserBaseInfo($userInfo) {
		if (empty($userInfo)) {
            return FALSE;
        }   
        $sqlComm = "INSERT IGNORE INTO {$this->table} " . 
            "(nickname, email, ctime, password, active_code, invite_code, last_logindate, is_actived, realname, reg_from, cookie) VALUES " . 
            "(:nickname, :email, NOW(), :password, :active_code, :invite_code, NOW(), :_isActived, :realname, :_reg_from, :cookie)";
        $sqlData = array(
            'nickname' => $userInfo['nickname'],
            'email' => $userInfo['email'],
            'password' => $userInfo['password'],
            'active_code' => $userInfo['activateCode'],
            'invite_code' => $userInfo['inviteCode'],
            '_isActived' => $userInfo['isActived'],
            'realname' => $userInfo['realname'],
            '_reg_from' => $userInfo['regFrom'],
            'cookie' => $userInfo['cookie'],
        );  
    
        $result = FALSE;
        $result = DBUserHelper::getConn()->write($sqlComm, $sqlData);
        if ($result === 1) {
            $insertId = DBUserHelper::getConn()->getInsertId(); 
        }   
        else {
            $insertId = $result;
        }   
        return $insertId;
	}

	/**
     * 插入用户详细信息
     * @param $user_id 用户编号
     * @param $userExtInfo array 用户详细信息
     * 
	 * @author ChaoGuo
     */
    public function insertUserExtInfo($user_id, $userExtInfo) {
        if (empty($user_id) || empty($userExtInfo) || !is_array($userExtInfo)) {
            return FALSE;
        }
        $sqlComm = "INSERT IGNORE INTO {$this->extTable} " .
            "(user_id, gender, birthday, province_id, city_id, msn, qq, blog, about_me, interests, hobby, school, workplace) " .
            "VALUES (:_user_id, :gender, :birthday, :_province_id, :_city_id, :msn, :qq, :blog, :about_me, :interests, :hobby, :school, :workplace)";
        $sqlData = array(
            '_user_id' => $user_id,
            'gender' => $userExtInfo['gender'],
            'birthday' => $userExtInfo['birthday'],
            '_province_id' => $userExtInfo['province_id'],
            '_city_id' => $userExtInfo['city_id'],
            'msn' => $userExtInfo['msn'],
            'qq' => $userExtInfo['qq'],
            'blog' => $userExtInfo['blog'],
            'about_me' => $userExtInfo['about_me'],
            'interests' => $userExtInfo['interests'],
            'hobby' => $userExtInfo['hobby'],
            'school' => $userExtInfo['school'],
            'workplace' => $userExtInfo['workplace'],
        );
        $result = DBUserHelper::getConn()->write($sqlComm, $sqlData);
        return $result;
    }
	
	 /**
     * 激活一个用户
     * @param $activate_code 激活码
     *
	 * @author ChaoGuo
     */
    public function activateUser($activate_code) {
        if (empty($activate_code)) {
            return FALSE;
        }
        $sqlComm = "SELECT user_id, email, password, is_actived FROM {$this->table} WHERE active_code=:active_code LIMIT 0, 1";
        $sqlData['active_code'] = $activate_code;
        $result = array();
        $result = DBUserHelper::getConn()->read($sqlComm, $sqlData, TRUE);
        if (!empty($result[0])) {
            $r_value = $result[0];
			$result = 1;
            if ($r_value['is_actived'] == -2) {
                return FALSE;
            }
			elseif ($r_value['is_actived'] == 0 || $r_value['is_actived'] == 2) {
				$sqlComm = "UPDATE {$this->table} SET is_actived = 1 WHERE user_id=:_user_id";
				unset($sqlData);
				$sqlData['_user_id'] = $r_value['user_id'];
				$result = DBUserHelper::getConn()->write($sqlComm, $sqlData);
			}
			return array(
				'status' =>$result,
				'user_id' => $r_value['user_id'],
				'email' => $r_value['email'],
				'password' => $r_value['password'],
			);
        }
		return FALSE;
    }
	
	/**
     * 根据激活码返回状态信息
     * @param $activate_code 激活码
     *
     */
    public function getActivedByCode($activate_code, $fromMaster = FALSE) {
        if (empty($activate_code)) {
            return FALSE;
        }
        $sqlComm = "SELECT is_actived FROM {$this->table} WHERE active_code=:activate_code LIMIT 0, 1";
        $sqlData['activate_code'] = $activate_code;
        $result = array();
        $result = DBUserHelper::getConn()->read($sqlComm, $sqlData, $fromMaster);
        if (!empty($result[0])) {
            return $result[0]['is_actived'];
        }
        return FALSE;
    }

	/**
     * 根据不同的条件获取用户信息
     * @param $param array 条件
     * @param $columns 查询字段
     * @param $fromMaster 是否从主库中查询
     *
     */
    public function getUserProfile($param, $columns = '*', $fromMaster = FALSE) {
        if (empty($param) || !is_array($param)) {
            return FALSE;
        }
		if (is_array($columns)) {
			$columns = implode(',', $columns);
		}
        $sqlComm = "SELECT $columns FROM {$this->table} WHERE 1=1 ";
        if (isset($param['user_id'])) {
            $sqlComm .= "AND user_id=:_user_id ";
            $sqlData['_user_id'] = $param['user_id'];
        }
        if (isset($param['activate_code'])) {
            $sqlComm .= "AND active_code=:activate_code ";
            $sqlData['activate_code'] = $param['activate_code'];
        }
        if (isset($param['nickname'])) {
            $sqlComm .= "AND nickname=:nickname ";
            $sqlData['nickname'] = $param['nickname'];
        }
        if (isset($param['invite_code'])) {
            $sqlComm .= "AND invite_code=:invite_code ";
            $sqlData['invite_code'] = $param['invite_code'];
        }
        if (isset($param['email'])) {
            $sqlComm .= "AND email=:email ";
            $sqlData['email'] = $param['email'];
        }
        $result = array();
        $result = DBUserHelper::getConn()->read($sqlComm, $sqlData, $fromMaster);
        return $result;
    }
	
	//处理新浪微博等互联过来的 昵称带#的问题
	private function _getNickName($result) {
		if (!is_array($result)) {
			return FALSE;
		}
		if (isset($result['nickname'])) {
			if (mb_strpos($result['nickname'], '#', 0, 'utf-8') > 0) {
				$nick = explode("#", $result['nickname']);
				$result['nickname'] = $nick[0];
			}
		}
		return $result;
	}
	
	/**
	 * 判断一个用户是否存在
     * @author yishuliu@meilishuo.com
	 * @param $user_id
	 * @return int 1代表存在该用户 0代表该用户不存在
	 */
	public function checkIfUserExists($user_id, $fromMaster = FALSE) {
		$sqlComm = "SELECT count(user_id) as num FROM t_dolphin_user_profile WHERE user_id = :_user_id";
        $sqlData['_user_id'] = $user_id;
        $num = array();
        $num = DBUserHelper::getConn()->read($sqlComm, $sqlData, $fromMaster);
		$result = ($num[0]['num'] == 1) ? 1 : 0; 
		return $result;
	}

	/**
	 *获得一定条件的用户
	 *login_time 登陆次数
	 *param $peroid 时间撮
	 *类型 哪个邮箱
	 *author gstan
	 */
	 public function getUserInfoForEdm($max_login_time,$min_login_time, $period, $max_period,$type, $limit) {
		 
		 $sqlComm = "select user_id,email, nickname from {$this->table} where is_actived != 2 and level = 0";
		 if (isset($max_login_time) && $max_login_time != 0) {
			$sqlComm .= " and login_times < {$max_login_time} ";	 
		 }
		 if (isset($min_login_time) && $min_login_time != 0){
			$sqlComm .= " and login_times > {$min_login_time} "; 	 
		 }
		 if (isset($period) && $period != 0) {
			$sqlComm .= " and unix_timestamp(last_logindate) > {$period} ";	 
		 }
		 if (isset($max_period) && $max_period !=0) {
			$sqlComm .= " and unix_timestamp(last_logindate) <  {$max_period} ";	 
		 }
		 if (isset($type)) {
			$sqlComm .= " and email like " . "'{$type}'";
		 }
		 if (isset($limit)) {
			$sqlComm .= " limit {$limit}" ;	 
		 }
		 print_r($sqlComm);
		 $result = array();
		 $sqlData = array();
		 $result = DBUserHelper::getConn()->read($sqlComm, $sqlData);
		 return $result;
	}

	/**
	 * 检查用户是否是超级主编之类的什么的
	 *
	 */
	public function checkUserProperty($userId) {
		$userExtInfo = $this->getUserExtInfo($userId, array('user_id', 'verify_icons'));
		if (empty($userExtInfo['verify_icons'])) {
			$result = array('blueV' => 0, 'pinkV' => 0, 'purpleV' => 0, 'editor' => 0);
			return $result;
		}
		$userProperty = is_array($userExtInfo['verify_icons']) ? $userExtInfo['verify_icons'] : explode(',', $userExtInfo['verify_icons']);
		
		$blueV = \Snake\Libs\Base\Utilities::inArray('t', $userProperty) == TRUE ? 1 : 0; //资深爱美丽:蓝v
		$pinkV = \Snake\Libs\Base\Utilities::inArray('s', $userProperty) == TRUE ? 1 : 0; //美丽说特别认证：粉v
		$purpleV = \Snake\Libs\Base\Utilities::inArray('b', $userProperty) == TRUE ? 1 : 0; //品牌认证：紫v
		$editor = \Snake\Libs\Base\Utilities::inArray('e', $userProperty) == TRUE ? 1 : 0; //美丽说超级主编
		$result = array('blueV' => $blueV, 'pinkV' => $pinkV, 'purpleV' => $purpleV, 'editor' => $editor);
		return $result;
	}

	/**
	 * 根据用户verify_icons数据返回对应浮窗悬浮值
	 *
	 */
    public function assembleMsg($userInfo, $userId) {
		$result = array();
        if (!empty($userInfo['verify_icons'][0])) {
            $tmp_icons = $userInfo['verify_icons'];
            foreach ($tmp_icons as $key => $value) {
                if ($value == 't') {
                    $result['identity']['blueV'] = ($userId !== 18185784) ? '资深爱美丽' : '首都网警';
                }   
                elseif ($value == 's') {
                    $result['identity']['pinkV'] = '美丽说特别认证';
                }   
                elseif ($value == 'b') {
                    $result['identity']['purpleV'] = '美丽说认证品牌';
                }   
                elseif ($value == 'e') {
                    $result['identity']['editorV'] = '美丽说超级主编';
                }   
            }   
        }   
		else {
            $result['identity']['blueV'] = '';
            $result['identity']['pinkV'] = '';
            $result['identity']['purpleV'] = '';
            $result['identity']['editorV'] = '';
		}
		if (!empty($userInfo['verify_msg'])) {
            $tmp_desc = is_array($userInfo['verify_msg']) ? $userInfo['verify_msg'] : explode(',', $userInfo['verify_msg']);
			foreach ($tmp_desc as $key => $value) {
                if ($key == 't') {
                    $result['identity']['description']['blueV'] = $value;
                }   
                elseif ($key == 's') {
                    $result['identity']['description']['pinkV'] = $value;
                }   
                elseif ($key == 'b') {
                    $result['identity']['description']['purpleV'] = $value;
				}
				elseif ($key == 'e') {
                    $result['identity']['description']['editorV'] = $value;
				}
			}
            //$result['identity']['description'] = is_array($userInfo['verify_msg']) ? $userInfo['verify_msg'] : explode(',', $userInfo['verify_msg']);
		}
        if ($userInfo['is_taobao_buyer'] == 1) {
            $result['identity']['heart_buyer'] = '美丽说心级买家认证';
            $result['identity']['diamond_buyer'] = '';
        }   
        elseif ($userInfo['is_taobao_buyer'] == 2) {
            $result['identity']['heart_buyer'] = '';
            $result['identity']['diamond_buyer'] = '美丽说黄钻买家认证';
        }   
		else {
            $result['identity']['heart_buyer'] = '';
            $result['identity']['diamond_buyer'] = '';
		}
		return $result;
    }   

	/**
	 * 将c图转化成其他格式的头像
	 */
	public function picConvertFromAvatarC($avatar_c, $type = 'a') {
		$result = str_replace('/c/', '/' . $type . '/', $avatar_c);
		return $result;
	}

	public function verifyUser($userId) {
        $userExtInfo = $this->getUserExtInfo($userId, array('user_id', 'verify_icons'));
        $response = FALSE;
        if (empty($userExtInfo['verify_icons'])) {
            return $response;
        }    
        $userProperty = explode(',', $userExtInfo['verify_icons']);
        foreach ($userProperty as $key => $value) {
            if (in_array($value, $this->userVTypes)) {
                $response = TRUE;
                break;
            }    
        }    
        return $response;
	}
}
