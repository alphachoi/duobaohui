<?php
namespace Snake\Package\Famous;

/**
 * 达人相关活动相关sql查询操作
 * @author yishuliu@meilishuo.com
 * @since 2012-11-01
 * @version 1.0
 */

Use \Snake\Package\User\Helper\RedisUserConnectHelper;
Use \Snake\Package\User\Helper\RedisUserStatisticHelper;
Use \Snake\Package\User\Helper\CacheUserHelper;
Use \Snake\Package\User\User;
Use \Snake\Package\Famous\Helper\DBFamousHelper;
Use \Snake\Package\User\Helper\DBUserHelper;
Use \Snake\libs\Cache\Memcache;
Use \Snake\Package\Shareoutside\ShareHelper;

/**
 * @author yishuliu@meilishuo.com
 * @since 2012-11-01
 * @version 1.0
 */

class FamousActivity {

	private static $instance;
	private $no_cache = TRUE;
	private $table = 't_dolphin_daren_activity';
	private $types = array(1 => '美妆达人', 2 => '搭配达人', 3 => '扫货达人');

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

	/**
	 * 根据活动类别取出对应列的数据
	 */
	public function getActUserInfoByType($type_id, $params = array('user_id', 'division', 'img', 'vote', 'group_id'), $start = NULL, $length = NULL) {
		$str = implode(',', $params);
		$sql = "SELECT {$str} FROM {$this->table} WHERE division = {$type_id}";
		if (!empty($length)) {
			$sql .= " ORDER BY vote DESC limit {$start}, {$length}";
		}
		//print_r($sql);die('##');
		$result = array();
        $result = DBFamousHelper::getConn()->read($sql, array());
		return $result;
	}
	
	/**
     * Top5数据
     */
	public function getTopUserByType($limit, $type_id = NULL, $params = array('user_id', 'division', 'img', 'vote', 'group_id')) {
		$str = implode(',', $params);
		if (empty($type_id)) {
			$sql = "(SELECT {$str} FROM {$this->table} WHERE division = 1 ORDER BY vote DESC LIMIT {$limit}) UNION ALL (SELECT {$str} FROM {$this->table} WHERE division = 2 ORDER BY vote DESC LIMIT {$limit}) UNION ALL (SELECT {$str} FROM {$this->table} WHERE division = 3 ORDER BY vote DESC LIMIT {$limit})";
		}
		else {
			$sql = "SELECT {$str} FROM {$this->table} WHERE division = {$type_id} ORDER BY vote DESC LIMIT {$limit}";
		}
		$result = array();
        $result = DBFamousHelper::getConn()->read($sql, array());
		/*if (empty($type_id)) {
			$sepeResult = array();
			foreach($result as $key =>$item) {
				$sepeResult[$this->types[$item['division']]][] = $item; 
			}
			return $sepeResult;
		}*/
		return $result;
	}

    /**  
     * 插入信息
     * @param $userInfo array
     *
     * @return insertId
     */
    public function insertActUserInfo($userInfo) {
        if (empty($userInfo)) {
            return FALSE;
        }    
        $sqlComm = "INSERT INTO {$this->table} " . 
            "(user_id, division, group_id, img, vote) VALUES " . 
            "(:user_id, :division, :group_id, :img, :vote)";
        $sqlData = array(
            'user_id' => $userInfo['user_id'],
            'division' => $userInfo['division'],
            'group_id' => $userInfo['group_id'],
            'img' => $userInfo['img'],
            'vote' => 0, //$userInfo['vote'],
        );   
    
        $result = FALSE;
        $result = DBFamousHelper::getConn()->write($sqlComm, $sqlData);
        if ($result === 1) { 
            $insertId = DBFamousHelper::getConn()->getInsertId(); 
        }    
        else {
            $insertId = $result;
        }    
        return $insertId;
    }    

	public function checkWhetherIn($user_id) {
        $cacheHelper = Memcache::instance();
        $cacheKey = "Famous:FamousActivity";
		
		$result = $cacheHelper->get($cacheKey);
		if (!empty($result)) {
			$response = in_array($user_id, $result);
			return $response;
		}
		else {
			$sql = "SELECT * FROM {$this->table} WHERE user_id = {$user_id} AND division = 3";
			$result = array();
        	$result = DBFamousHelper::getConn()->read($sql, array());
			if (empty($result)) {
				return FALSE;
			}
			return TRUE;
		}
	}

	public function setActUserInfoCache() {
        $cacheHelper = Memcache::instance();
        $cacheKey = "Famous:FamousActivity";
		
		$result = $cacheHelper->get($cacheKey);
		if (!empty($result)) {
			return;
		}
		$sql = "SELECT user_id FROM {$this->table} WHERE division = 3";
		$result = array();
        $result = DBFamousHelper::getConn()->read($sql, array());
		
		$cacheHelper->set($cacheKey, $result, 86400);
		return $result;
	}

    /**
     * 更新用户信息
     */
    public function updateActUserVoteNum($user_id, $num = NULL) {
		/*if (empty($infos)) {
			return FALSE;
		}*/
		if (!empty($num)) {
			$sqlComm = "UPDATE {$this->table} SET vote = {$num} WHERE user_id = {$user_id} ";
		}
		else {
			$sqlComm = "UPDATE {$this->table} SET vote = vote + 1 WHERE user_id = {$user_id} ";
		}
        $sqlData = array();
		
        $result = DBUserHelper::getConn()->write($sqlComm, $sqlData);
        return $result;
    }

    //查询符合条件的所有达人totalnum
    public function getFamousActListTotal($userType = '') {
        if (!empty($userType)) {
            $sql = "SELECT count(distinct(user_id)) as totalNum FROM {$this->table} WHERE division ={$userType} and img is not null and img != ''";
        }   
        else {
            $sql = "SELECT count(distinct(user_id)) as totalNum FROM {$this->table} WHERE division in (1, 2, 3) and img is not null and img != ''";
        }   
        $result = array();
        $result = DBFamousHelper::getConn()->read($sql, array());
        return $result[0]['totalNum'];
    }   

    public function shareOutsites($user_id, $shareQzone, $shareSina) {
		$tokenq = RedisUserConnectHelper::getUserToken('qzone', $user_id);
		$openId = RedisUserConnectHelper::getUserAuth('qzone', $user_id);
		if (!empty($tokenq) && !empty($openId)) {
			ShareHelper::sync($user_id, 'share', '', 4, 0, $shareQzone['content'], '', array('url' => $shareQzone['url'], 'image' => null, 'comment' => $shareQzone['comment']));
		}    

		$tokenw = RedisUserConnectHelper::getUserToken('weibo', $user_id);
		if (!empty($tokenw)) {
			ShareHelper::sync($user_id, 'share', '', 3, 0, $shareSina['content'], null, array('image' => null));
		}   
    }  

    public function shareAssemble($userId, $userInfo, $tab, $limit) {
		$nickname = $userInfo['nickname'];
        $shareQzone = array();
        $shareQzone['content'] = ">>美丽说，陪你美丽每一天！";
		$shareQzone['url'] = !empty($tab) ? "http://www.meilishuo.com/wbapp/haibao/pk?limit=$limit&tab=$tab" : "http://www.meilishuo.com/wbapp/haibao/pk";
        $shareQzone['comment'] = "@美丽说 时尚偶像巅峰对决，我投票给{$nickname}，希望她获胜！>>" . $shareQzone['url'];

        $shareSina = array();
        $shareSina['url'] = !empty($tab) ? "http://www.meilishuo.com/wbapp/haibao/pk?limit=$limit&tab=$tab" : "http://www.meilishuo.com/wbapp/haibao/pk";
        $shareSina['content'] = "@美丽说 时尚偶像巅峰对决，我投票给{$nickname}，希望她获胜！>>" . $shareSina['url'];

        $this->shareOutsites($userId, $shareQzone, $shareSina);
    }

	public function checkUserParticipate($userId, $fromMaster) {
		if (empty($userId)) {
			return FALSE;
		}
		$sql = "SELECT count(*) as num FROM {$this->table} WHERE user_id = {$userId}";
        $result = array();
        $result = DBFamousHelper::getConn()->read($sql, array(), $fromMaster);
        return ($result[0]['num'] >= 1) ? TRUE : FALSE;
	}
}
