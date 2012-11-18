<?php
namespace Snake\Package\Recommend;

use Snake\Package\User\Helper\RedisUserFollow as UserFollow;
use Snake\Package\Group\Helper\UserGroupFollower;
use Snake\Package\Group\GroupUser;
Use Snake\Libs\Cache\Memcache;
Use \Snake\Libs\PlatformService\MlsStorageService;

class DBRecommendHelper extends \Snake\Libs\DB\DBModel {
    const _DATABASE_ = 'dolphin';
}

class DBRecommendWhaleHelper extends \Snake\Libs\DB\DBModel {
    const _DATABASE_ = 'whale';
}

class DBRecommendSealHelper extends \Snake\Libs\DB\DBModel {
    const _DATABASE_ = 'seal';
}

class DBRecommendSharkHelper extends \Snake\Libs\DB\DBModel {
    const _DATABASE_ = 'shark';
}

class Recommend {
    
    private static $maxReAttrNum = 6;
	private static $maxReGroupNum = 5;
	
	private static $following_uids = array(0);
	private static $following_size = 0;
    
    public function __construct() {

	}
    
    public function getReAttrByRand($randNum, $aStr="0") {
		$sqlComm = "select /*Recommend-ft*/word_id,word_name from t_dolphin_attr_words where word_id not in ({$aStr}) and isuse=1 and isred=1 order by rand() limit {$randNum}";
		$result = DBRecommendHelper::getConn()->read($sqlComm, array () );
		return $result;
	}
	/*
     *当没有属性词搭配信息时，使用此接口获取搭配信息
     *
     */
    public function getReAttrByAidIfNoMatch($attr_id, $renum=6){

        $sqlComm = "select /*Recommand-mxh*/ attr_dapei_id from t_seal_attr_dapei where attr_id = {$attr_id} order by rand() limit {$renum}";
        $result = DBRecommendSealHelper::getConn()->read($sqlComm, array() );
        if (!isset($result[0])) {
            return array();
        }
        $aStr = '';
        $aArr = array();
        foreach ($result as $value){
            $aArr[] = $value['attr_dapei_id'];
        }
        $aStr = implode(" , ", $aArr);

        $sqlComm = "select /*recommendDapei-mxh*/ word_id from t_dolphin_attr_words where word_id in ({$aStr}) and isuse = 1";
        $result = DBRecommendHelper::getConn()->read($sqlComm, array() );
        if (!isset($result[0])) {
            return array();
        }
        $dapeiArr = array();
        foreach($result as $value){
            $dapeiArr[] = $value['word_id'];    
        }
        return $dapeiArr;
    }


    
	/*
	 * 提供一个attr_id，返回推荐的属性信息（共6个）
	 * type参数为10表示搜索页请求
	 */
	public function getReAttrByAid($attr_id, $type=0, $renum=6) {
		/*
		 * AB测试控制
		 */
		/*
		global $GLOBAL_COOKIE_STRING;
		$sessid_len = strlen($GLOBAL_COOKIE_STRING);
		$lastc = "";
		if ($sessid_len>0){
			$lastc = $GLOBAL_COOKIE_STRING[$sessid_len-1];
		}
		$abNum=0;
		if (empty($GLOBAL_COOKIE_STRING) || ($sessid_len<=0) || ($lastc<"0" or $lastc>"9") ){
			$abNum = mt_rand(0,9);
		}
		else {
			$abNum = $lastc%10;
		}
		*/
		$abNum = 1;
		
		if ($abNum==0) {
			$sqlComm = "select /*Recommend-ft*/label_id from t_dolphin_attr_words where word_id={$attr_id} and isuse=1";
			$result = array ();
			$result = DBRecommendHelper::getConn()->read($sqlComm, array ());
			if (!isset($result[0])) {
				return array();
			}
			$lid = $result[0]['label_id'];
			$sqlComm = "select /*Recommend-ft*/word_id,word_name from t_dolphin_attr_words where label_id={$lid} and word_id!={$attr_id} and isuse=1 order by rand() limit {$renum}";
			$sameLabelAttrRes = array ();
			$sameLabelAttrRes = DBRecommendHelper::getConn()->read($sqlComm, array () );
			return $sameLabelAttrRes;
		}
							
		$sqlComm = "select /*Recommend-ft*/same_to from t_dolphin_attr_words where word_id={$attr_id} and isuse=1 and same_to>1";
		$result = array ();
		$result = DBRecommendHelper::getConn()->read($sqlComm, array ());
		if (isset($result[0])) {
			$attr_id = $result[0]['same_to'];
		}
		
		$sqlComm = "select /*Recommend-ft*/attr_id2 attr_id from t_seal_attr_attr_top where attr_id={$attr_id} and attr_id2 != {$attr_id} 
		 order by rank asc limit {$renum}";
		/*
		 * 对于搜索请求返回结果按rank排序
		 */
		if ($type==10) {
			$sqlComm = "select /*Recommend-ft*/attr_id2 attr_id from t_seal_attr_attr_top where attr_id={$attr_id} and attr_id2 != {$attr_id} 
		 order by rank limit {$renum}";
		}
		$attrRes = array ();
		$attrRes = DBRecommendSealHelper::getConn()->read($sqlComm, array () );
		if (!isset($attrRes[0])) {
			return array();
		}
		
		$aArr = array();
		foreach ($attrRes as $value) {
			$aArr[] = $value['attr_id'];
		}
		if (!isset($aArr[0])) {
			return array();
		}
		$aStr = implode(" , ", $aArr);
		
		$attrInfo = array();
		$i = 0;
		$sqlComm = "select /*Recommend-ft*/word_id,word_name from t_dolphin_attr_words where word_id in ({$aStr}) and isuse=1";
		$result = array ();
		$result = DBRecommendHelper::getConn()->read($sqlComm, array () );
		$aidV = array();
		foreach ($result as $value) {
			if ($i>=$renum){
				break;
			}
			$aidV[ $value['word_id'] ] = $value;
			$i++;
		}
		
		$i=0;
		foreach ($aArr as $aid){
			if (isset($aidV[$aid])) {
				$attrInfo[] = $aidV[$aid];
				$i++;
			}
		}
		
		if (count($attrInfo) >= 20) {
			$sqlComm = "select /*Recommend-ft*/attr_id2 word_id from t_seal_cf_attr_attr_top where attr_id={$attr_id} and rank<=10  
			 order by rank asc limit 5";
			$attrRes = array ();
			$attrRes = DBRecommendSealHelper::getConn()->read($sqlComm, array () );
			$idx = count($attrInfo) - 6;
			foreach ($attrRes as $value) {
				$aid = $value['word_id'];
				if (!isset($aidV[ $aid ])) {
					$sqlComm = "select /*Recommend-ft*/word_id,word_name from t_dolphin_attr_words where word_id={$aid} and isuse=1";
					$result = array ();
					$result = DBRecommendHelper::getConn()->read($sqlComm, array () );
					if (isset($result[0])) {
						$attrInfo[$idx] = $result[0];
						$idx++;
					}
				}
			}
		}
		

		if ($i<$renum && $type!=10) {
			$randNum = $renum-$i;
			$sqlComm = "select /*Recommend-ft*/word_id,word_name from t_dolphin_attr_words where word_id not in ({$aStr}) and isuse=1 order by rand() limit {$randNum}";
			$result = array ();
			$result = DBRecommendHelper::getConn()->read($sqlComm, array () );
			foreach ($result as $value) {
				$attrInfo[] = $value;
			}
		}
		return $attrInfo;
	}
	
	/*
	 * 提供一个attr_id数组，返回推荐的属性信息
	 * type参数为10表示搜索页请求
	 */
	public function getReAttrByAidArr($aArr, $type=0, $renum=6, $uid=0) {
		$renum_bak = $renum;
		if (!isset($aArr[0])) {
			if ($type == 10) {
				try {//取用户最近浏览的属性属性页id
					$cache = Memcache::instance();
					$beh_key =  "user:action:history:".$_COOKIE['MEILISHUO_GLOBAL_KEY'];
					$beh = $cache->get($beh_key);
					$json_string=str_replace("'","\"",$beh);
					$obj=json_decode($json_string, true);
					if (isset($obj['a'])) {
						$i = 0;
						foreach ($obj['a'] as $aid_count){
							$i++;
							if ($i>5) {
								break;
							}
							$aArr[] = $aid_count['i'];
						}
					}
				}
				catch (Exception $e) {
				}
				if (!isset($aArr[0])) {
					return $this->getReAttrByRand($renum_bak);
				}
			}
			else {
				return $this->getReAttrByRand($renum_bak);
			}
		}
		$aStr = implode(" , ", $aArr);
		
		$attrInfo = array();
		$aid_map = array();
		
		$sqlComm = "select /*Recommend-ft*/word_id,same_to from t_dolphin_attr_words where isuse=1 and word_id in ({$aStr}) and (label_id in (13,14,17,20) or floor(label_id/100) in (13,14,17,20))";
		$attrRes = array ();
		$attrRes = DBRecommendHelper::getConn()->read($sqlComm, array () );
		foreach ($attrRes as $value) {
			if ($renum<1) {
				return $attrInfo;
			}
			$aid = $value['word_id'];
			if ($value['same_to']>0) {
				$aid = $value['same_to'];
			}
			$tmpRes = $this->getReAttrByAid($aid, $type, $renum);
			$c=0;
			foreach ($tmpRes as $tmpValue) {
				if (isset($aid_map[$tmpValue['word_id']])) {
					continue;
				}
				$aid_map[$tmpValue['word_id']] = 1;
				$attrInfo[] = $tmpValue;
				$c++;
			}
			$renum = $renum-$c;
		}
		
		$sqlComm = "select /*Recommend-ft*/word_id,same_to from t_dolphin_attr_words where isuse=1 and word_id in ({$aStr}) and (label_id not in (13,14,17,20) and floor(label_id/100) not in (13,14,17,20))";
		$attrRes = array ();
		$attrRes = DBRecommendHelper::getConn()->read($sqlComm, array () );
		foreach ($attrRes as $value) {
			if ($renum<1) {
				return $attrInfo;
			}
			$aid = $value['word_id'];
			if ($value['same_to']>0) {
				$aid = $value['same_to'];
			}
			$tmpRes = $this->getReAttrByAid($aid, $type, $renum, $uid);
			$c=0;
			foreach ($tmpRes as $tmpValue) {
				if (isset($aid_map[$tmpValue['word_id']])) {
					continue;
				}
				$aid_map[$tmpValue['word_id']] = 1;
				$attrInfo[] = $tmpValue;
				$c++;
			}
			$renum = $renum-$c;
		}
		
		if (count($attrInfo)<1) {
			return $this->getReAttrByRand($renum_bak);
		}
		
		return $attrInfo;
	}
	
	/*
	 * 提供一个goods_id，返回推荐的属性信息（共10个属性）
	 */
	public function getReAttrByGid($gid, $attrMapArr, $renum=10) {
		$sqlComm = "select /*Recommend-ft*/attr_id from t_seal_goods_attr_top where goods_id={$gid} and tag=0 
		 order by rank asc";
		$attrRes = array ();
		$attrRes = DBRecommendSealHelper::getConn()->read($sqlComm, array () );
		if (!isset($attrRes[0])) {
			$attrRes = array ();
			foreach ($attrMapArr as $value) {
				$attrRes[] = $value;
			}
		}
		
		$aArr = array();
		$attr_map = array();
		$i = 0;
		foreach ($attrRes as $value) {
			$aArr[] = $value['attr_id'];
			$attr_map[$value['attr_id']] = $i;
			$i++;
		}
		if (!isset($aArr[0])) {
			return $this->getReAttrByRand($renum);
		}
		$aStr = implode(" , ", $aArr);
		
		
		$attrInfo = array();
		$sqlComm = "select /*Recommend-ft*/word_id,word_name,label_id from t_dolphin_attr_words where word_id in ({$aStr}) and isuse=1";
		$result = array ();
		$result = DBRecommendHelper::getConn()->read($sqlComm, array () );
		
		$firstAid = 0;
		$firstAttrInfoIdx = -1;
		foreach ($result as $value) {
			if (in_array($value['label_id'],array(13,14,17,20)) || in_array(floor($value['label_id']/100),array(13,14,17,20))) {
				$curIdx = $attr_map[$value['word_id']];
				if ($firstAttrInfoIdx<0 || ($firstAttrInfoIdx>=0 && $curIdx<$firstAttrInfoIdx)) {
					$firstAttrInfoIdx = $curIdx;
					$firstAid = $value['word_id'];
				}
			}
		}
		
		if ($firstAttrInfoIdx<0) {
			foreach ($result as $value) {
				if (in_array($value['label_id'],array(16,11)) || in_array(floor($value['label_id']/100),array(16,11))) {
					$curIdx = $attr_map[$value['word_id']];
					if ($firstAttrInfoIdx<0 || ($firstAttrInfoIdx>=0 && $curIdx<$firstAttrInfoIdx)) {
						$firstAttrInfoIdx = $curIdx;
						$firstAid = $value['word_id'];
					}
				}
			}
		}
		
		if ($firstAttrInfoIdx>=0) {
			$sqlComm = "select /*Recommend-ft*/word_id,word_name from t_dolphin_attr_words where word_id = {$firstAid} and isuse=1";
			$result = array ();
			$result = DBRecommendHelper::getConn()->read($sqlComm, array () );
			if (isset($result[0])) {
				$attrInfo[] = $result[0];
			}
		}
		
		$i = 0;
		foreach ($attrRes as $value) {
			if (count($attrInfo)>=$renum){
				break;
			}
			if ($i==$firstAttrInfoIdx){
				$i++;
				continue;
			}
			$aid = $value['attr_id'];
			$sqlComm = "select /*Recommend-ft*/word_id,word_name from t_dolphin_attr_words where word_id = {$aid} and isuse=1";
			$result = array ();
			$result = DBRecommendHelper::getConn()->read($sqlComm, array () );
			if (!isset($result[0])) {
				$i++;
				continue;
			}
			$attrInfo[] = $result[0];
			$i++;
		}
		if (count($attrInfo)<$renum){
			$randNum = $renum-count($attrInfo);
			$sqlComm = "select /*Recommend-ft*/word_id,word_name from t_dolphin_attr_words where word_id not in ({$aStr}) and isuse=1 and isred=1 order by rand() limit {$randNum}";
			$result = array ();
			$result = DBRecommendHelper::getConn()->read($sqlComm, array () );
			foreach ($result as $value) {
				$attrInfo[] = $value;
			}
		}
		return $attrInfo;
	}
	
	
	/*
	 * 提供一个属性数组，返回10个推荐属性
	 */
	public function getReAttrByArr($attrMapArr) {
		$renum = 10;
		$reData = array ();
		$attrRes = array();
		$attrRes = $attrMapArr;
		if (!isset($attrRes[0])) {
			return $this->getReAttrByRand($renum);
		}

		$aArr = array();
		$firstAttrInfoIdx = 0;
		foreach ($attrRes as $value) {
			$aArr[] = $value['attr_id'];
		}
		if (!isset($aArr[0])) {
			return $this->getReAttrByRand($renum);
		}
		$aStr = implode(" , ", $aArr);
		
		$attrInfo = array();
		
		if ($firstAttrInfoIdx>=0) {
			$sqlComm = "select /*Recommend-ft*/word_id,word_name from t_dolphin_attr_words where word_id = {$aArr[0]} and isuse=1";
			$result = array ();
			$result = DBRecommendHelper::getConn()->read($sqlComm, array () );
			if (isset($result[0])) {
				$attrInfo[] = $result[0];
			}
		}
		
		$i = 0;
		foreach ($attrRes as $value) {
			if ($i>=$renum){
				break;
			}
			if ($i==$firstAttrInfoIdx){
				$i++;
				continue;
			}
			$aid = $value['attr_id'];
			$sqlComm = "select /*Recommend-ft*/word_id,word_name from t_dolphin_attr_words where word_id = {$aid} and isuse=1";
			$result = array ();
			$result = DBRecommendHelper::getConn()->read($sqlComm, array () );
			if (!isset($result[0])) {
				continue;
			}
			$attrInfo[] = $result[0];
			$i++;
		}
		if ($i<$renum){
			$randNum = $renum-$i;
			$sqlComm = "select /*Recommend-ft*/word_id,word_name from t_dolphin_attr_words where word_id not in ({$aStr}) and isuse=1 and isred=1 order by rand() limit {$randNum}";
			$result = array ();
			$result = DBRecommendHelper::getConn()->read($sqlComm, array () );
			foreach ($result as $value) {
				$attrInfo[] = $value;
			}
		}
		return $attrInfo;
	}
	
	/*
	 * 提供一个推id，返回推荐的属性信息（共10个属性）
	 */
	public function getReAttrByTid($tid) {
		$sqlComm = "select /*Recommend-ft*/twitter_goods_id from t_twitter where twitter_id={$tid}";
		$result = array ();
		$result = MlsStorageService::QueryRead($sqlComm);
		if (!isset($result[0])) {
			return array();
		}
		if ($result[0]['twitter_goods_id']<=0) {
			return array();
		}
		$attrInfo = array();
		$attrInfo = $this->getReAttrByGid($result[0]['twitter_goods_id']);
		return $attrInfo;
	}
	
	/*
	 * 提供一个goods_id，返回推荐的杂志信息（共5个）
	 */
	public function getReGroupByGid($gid, $uid=0) {
		$sqlComm = "select /*Recommend-ft*/attr_id from t_seal_goods_attr_top where goods_id={$gid} 
		 order by rank asc limit 5";
		$attrRes = array ();
		$attrRes = DBRecommendSealHelper::getConn()->read($sqlComm, array () );
		if (!isset($attrRes[0])) {
			return array();
		}
		
		$aArr = array();
		foreach ($attrRes as $value) {
			$aArr[] = $value['attr_id'];
		}
		if (!isset($aArr[0])) {
			return array();
		}
		$aStr = implode(" , ", $aArr);
		
		/*
		$role = array(0,1,4,5);
		$userGroup = array();
		if ($uid>0){
			$userGroup = TopicGroupUserModel::getInstance()->getUserGroups($uid, $role);
		}
		$gArr = array();
		$gArr = DataToArray($userGroup, 'group_id');
		$gArr[] = 0;
		$gStr = implode(" , ", $gArr);
		*/
		
		/*
		$gArr = UserGroupFollower::lRange($uid, 0, 1500);
		if (!is_array($gArr)) {
			$gArr=array();
		}
		$gArr = array_filter($gArr);
		$gArr[] = 0;
		$gStr = implode(" , ", $gArr);
		*/
		
		$groupInfo = array();
		$groupNum = self::$maxReGroupNum;
		$fetchNum = 20;
		$sqlComm = "select /*Recommend-ft*/distinct group_id from t_seal_group_attr_top where attr_id in ({$aStr}) and rank<=3 order by rand() limit {$fetchNum}";
		$result = array ();
		$result = DBRecommendSealHelper::getConn()->read($sqlComm, array () );
		
		$groupUserHelper = new GroupUser();
		$count = 0;
		$i = 0;
		$len = count($result);
		foreach ($result as $value) {
			$isFollower == false;
			if ( ($len-$i) > ($groupNum-$count) ) {
				$isFollower = $groupUserHelper->isGroupFollower($value['group_id'], $uid);
			}
			$i++;
			
			if (!$isFollower) {
				$groupInfo[] = $value;
				$count++;
				if ($count >= $groupNum) {
					break;
				}
			}
		}
		return $groupInfo;
	}
	
	/*
	 * 提供一个推id，返回推荐的杂志信息（共5个）
	 */
	public function getReGroupByTid($tid, $uid=0) {
		$sqlComm = "select /*Recommend-ft*/twitter_goods_id from t_twitter where twitter_id={$tid}";
		$result = array ();
		$result = MlsStorageService::QueryRead($sqlComm);
		if (!isset($result[0])) {
			return array();
		}
		if ($result[0]['twitter_goods_id']<=0) {
			return array();
		}
		$groupInfo = array();
		$groupInfo = $this->getReGroupByGid($result[0]['twitter_goods_id'], $uid);
		return $groupInfo;
	}
	
	/*
	 * 选取若干个杂志，这些杂志事先经过程序验证，近期有更新，且发宝数量有最低限制
	 */
	public function getReGroup($groupNum,$group_id=0,$uid=0) {
		$gArr = array();
		/*
		$role = array(0,1,4,5);
		$userGroup = array();
		if ($uid>0){
			$userGroup = TopicGroupUserModel::getInstance()->getUserGroups($uid, $role);
			$gArr = DataToArray($userGroup, 'group_id');
		}
		//防止query过大
		if (count($gArr)>200) {
			$gArr = array();
		}
		$gArr[] = $group_id;
		$gStr = implode(" , ", $gArr);
		*/
		
		/*
		$gArr = UserGroupFollower::lRange($uid, 0, 1500);
		if (!is_array($gArr)) {
			$gArr=array();
		}
		$gArr = array_filter($gArr);
		$gArr[] = $group_id;
		$gStr = implode(" , ", $gArr);
		*/
		
		$groupInfo = array();
		$fetchNum = $groupNum + 20;
		$sqlComm = "select /*Recommend-ft*/distinct group_id from t_seal_group_class_map order by rand() limit {$fetchNum}";
		$result = array ();
		$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
		
		$groupUserHelper = new GroupUser();
		$count = 0;
		$i = 0;
		$len = count($result);
		foreach ($result as $value) {
			$isFollower == false;
			if ( ($len-$i) > ($groupNum-$count) ) {
				$isFollower = $groupUserHelper->isGroupFollower($value['group_id'], $uid);
			}
			$i++;
			
			if (!$isFollower) {
				$groupInfo[] = $value;
				$count++;
				if ($count >= $groupNum) {
					break;
				}
			}
		}
		return $groupInfo;
	}
	
	/*
	 * 获取用户最近兴趣
	 */
	public function getAttrByUserTwitter($uid,$limit_num=5) {
		$tArr = array();
		$sqlComm = "select /*Recommend-ft*/interest_twitter_id twitter_id from (select /*Recommend-ft*/interest_twitter_id from t_dolphin_twitter_interest where 
		 interest_twitter_uid={$uid} order by interest_id desc limit 20) a order by rand() limit 5";
		$result = array ();
		$result = DBRecommendHelper::getConn()->read($sqlComm, array ());
		foreach ($result as $value) {
			$tArr[] = $value['twitter_id'];
		}
		$sqlComm = "select /*Recommend-ft*/twitter_id from t_twitter where 
		 twitter_author_uid={$uid} and twitter_show_type=7 order by twitter_id desc limit 5";
		$result = array ();
		$result = MlsStorageService::QueryRead($sqlComm);
		foreach ($result as $value) {
			$tArr[] = $value['twitter_id'];
		}
		
		$tStr = "";
		if (isset($tArr[0])) {
			$tStr = implode(" , ", $tArr);
		}
		
		if ($tStr == ""){
			return array();
		}
		
		$gArr = array();
		$sqlComm = "select /*Recommend-ft*/twitter_goods_id from t_twitter where twitter_id in ($tStr) and twitter_goods_id>1";
		$result = array ();
		$result = MlsStorageService::QueryRead($sqlComm);
		foreach ($result as $value) {
			$gArr[] = $value['twitter_goods_id'];
		}
		
		$gStr = "";
		if (isset($gArr[0])) {
			$gStr = implode(" , ", $gArr);
		}
		
		if ($gStr == ""){
			return array();
		}
		
		$sqlComm = "select /*Recommend-ft*/distinct attr_id from t_seal_goods_attr_top where goods_id in ({$gStr}) and rank <=5 and tag=0 and attr_id not in (33907,36703,33908,36501,35993) order by rand()";
		$result = array ();
		$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
		$aNum = $limit_num;
		$aArr = array();
		$i = 0;
		foreach ($result as $value) {
			if ($i>=$aNum) {
				break;
			}
			$aArr[$i] = $value['attr_id'];
			$i++;
		}
		if (!isset($aArr[0])) {
			return array();
		}
		
		return $aArr;
	}
	
	
	/*
	 * 获取杂志社最近特征
	 */
	public function getAttrByGroupTwitter($group_id,$limit_num=5) {
		$tArr = array();
		$sqlComm = "select /*Recommend-ft*/twitter_id from (select twitter_id from t_whale_topic_group_twitter where group_id={$group_id} order by twitter_id desc limit 50) a 
		 order by rand() limit 10";
		$result = array ();
		$result = DBRecommendWhaleHelper::getConn()->read($sqlComm, array ());
		foreach ($result as $value) {
			$tArr[] = $value['twitter_id'];
		}
		
		$tStr = "";
		if (isset($tArr[0])) {
			$tStr = implode(" , ", $tArr);
		}
		
		if ($tStr == ""){
			return array();
		}
		
		$gArr = array();
		$sqlComm = "select /*Recommend-ft*/twitter_goods_id from t_twitter where twitter_id in ($tStr) and twitter_goods_id>1";
		$result = array ();
		$result = MlsStorageService::QueryRead($sqlComm);
		foreach ($result as $value) {
			$gArr[] = $value['twitter_goods_id'];
		}
		
		$gStr = "";
		if (isset($gArr[0])) {
			$gStr = implode(" , ", $gArr);
		}
		
		if ($gStr == ""){
			return array();
		}
		
		$sqlComm = "select /*Recommend-ft*/distinct attr_id from t_seal_goods_attr_top where goods_id in ({$gStr}) and rank <=5 and tag=0 and attr_id not in (33907,36703,33908,36501,35993) order by rand()";
		$result = array ();
		$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
		$aNum = $limit_num;
		$aArr = array();
		$i = 0;
		foreach ($result as $value) {
			if ($i>=$aNum) {
				break;
			}
			$aArr[$i] = $value['attr_id'];
			$i++;
		}
		if (!isset($aArr[0])) {
			return array();
		}
		
		return $aArr;
	}
	
	
	public function getReGroupLimitByCata($aArr, $group_id=0, $uid=0, $renum=5, $type=1) {
		$cid_map = array();
		$i=0;
		$groupInfo = array();
		/*
		$role = array(0,1,4,5);
		$userGroup = array();
		if ($uid>0){
			$userGroup = TopicGroupUserModel::getInstance()->getUserGroups($uid, $role);
		}
		$gArr = array();
		$gArr = DataToArray($userGroup, 'group_id');
		$gArr[] = $group_id;
		$gStr = implode(" , ", $gArr);
		*/
		
		/*
		$gArr = UserGroupFollower::lRange($uid, 0, 1500);
		if (!is_array($gArr)) {
			$gArr=array();
		}
		$gArr = array_filter($gArr);
		$gArr[] = $group_id;
		$gStr = implode(" , ", $gArr);
		*/
		$gArr = array();
		$gArr[] = $group_id;
		$gStr = implode(" , ", $gArr);
		
		$renum_bak = $renum;
		$renum = 2 * $renum;
		$richNum = 0;
		$groupNum = $renum-$richNum;
		
		$groupUserHelper = new GroupUser();
		
		if ($group_id>0) {
			$sqlComm = "select /*Recommend-ft*/class_id from t_dolphin_group_class_map where group_id={$group_id}";
			$cresult = array ();
			$cresult = DBRecommendHelper::getConn()->read($sqlComm, array ());
			if (empty($cresult)) {
				$sqlComm = "select twitter_type /*Recommend-ft*/class_id from t_dolphin_cms_index_welcome where data_id={$group_id} and page_type =54";
				$cresult = array ();
				$cresult = DBRecommendHelper::getConn()->read($sqlComm, array ());
			}
			
			$cid_map = array();
			foreach ($cresult as $key => $cvalue) {
				if ($i>=$renum) {
					break;
				}
				$cid = $cvalue['class_id'];
				if (isset($cid_map[$cid])) {
					continue;
				}
				$cid_map[$cid] = 1;
				
				$sqlComm = "select /*Recommend-ft*/distinct group_id from t_seal_group_class_map where class_id = {$cid} and group_id not in ({$gStr}) order by rand() limit {$groupNum}";
				$result = array ();
				$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
				
				foreach ($result as $value) {
					$groupInfo[] = $value;
					$gArr[] = $value['group_id'];
					$i = $i+1;
				}
				$gStr = implode(" , ", $gArr);
				if ($i<$renum) {
					$groupNum = $renum-$i;
				}
				else {
					break;
				}
			}
			if (isset($aArr[0])) {
				$aStr = implode(" , ", $aArr);
				
				$fgAttrs = array(0);
				$sqlComm = "select /*Recommend-ft*/word_id,same_to from t_dolphin_attr_words where isuse=1 and word_id in ({$aStr}) and (label_id in (10) or floor(label_id/100) in (10))";
				$attrRes = array ();
				$attrRes = DBRecommendHelper::getConn()->read($sqlComm, array ());
				foreach ($attrRes as $value) {
					$aid = $value['word_id'];
					if ($value['same_to']>0) {
						$aid = $value['same_to'];
					}
					$fgAttrs[] = $aid;
				}
				$fgStr = implode(" , ", $fgAttrs);
				
		
				$sqlComm = "select /*Recommend-ft*/class_id from t_seal_attr_groupclass_top where attr_id in ({$fgStr}) and rank<=5 and class_id not in (14,32,56,76,88,94,24) order by rand() limit 10";
				$cresult = array ();
				$cresult = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
				
				foreach ($cresult as $key => $cvalue) {
					if ($i>=$renum) {
						break;
					}
					
					$cid = $cvalue['class_id'];
					if (isset($cid_map[$cid])) {
						continue;
					}
					$cid_map[$cid] = 1;
					
					$sqlComm = "select /*Recommend-ft*/distinct group_id from t_seal_group_class_map where class_id = {$cid} and group_id not in ({$gStr}) order by rand() limit {$groupNum}";
					$result = array ();
					$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
					
					foreach ($result as $value) {
						$groupInfo[] = $value;
						$gArr[] = $value['group_id'];
						$i = $i+1;
					}
					$gStr = implode(" , ", $gArr);
					if ($i<$renum) {
						$groupNum = $renum-$i;
					}
					else {
						break;
					}
				}
				
				
				$sqlComm = "select /*Recommend-ft*/class_id from t_seal_attr_groupclass_top where attr_id in ({$aStr}) and attr_id not in ({$fgStr}) and rank<=5 and class_id not in (14,32,56,76,88,94,24) order by rand() limit 10";
				$cresult = array ();
				$cresult = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
				
				foreach ($cresult as $key => $cvalue) {
					if ($i>=$renum) {
						break;
					}
					
					$cid = $cvalue['class_id'];
					if (isset($cid_map[$cid])) {
						continue;
					}
					$cid_map[$cid] = 1;
					
					$sqlComm = "select /*Recommend-ft*/distinct group_id from t_seal_group_class_map where class_id = {$cid} and group_id not in ({$gStr}) order by rand() limit {$groupNum}";
					$result = array ();
					$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
					
					foreach ($result as $value) {
						$groupInfo[] = $value;
						$gArr[] = $value['group_id'];
						$i = $i+1;
					}
					$gStr = implode(" , ", $gArr);
					if ($i<$renum) {
						$groupNum = $renum-$i;
					}
					else {
						break;
					}
				}
				
				$sqlComm = "select /*Recommend-ft*/class_id from t_seal_attr_groupclass_top where attr_id in ({$aStr}) and rank<=5 and class_id in (14,32,56,76,88,94,24) order by rand() limit 10";
				$cresult = array ();
				$cresult = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
				
				foreach ($cresult as $key => $cvalue) {
					if ($i>=$renum) {
						break;
					}
					
					$cid = $cvalue['class_id'];
					if (isset($cid_map[$cid])) {
						continue;
					}
					$cid_map[$cid] = 1;
					
					$sqlComm = "select /*Recommend-ft*/distinct group_id from t_seal_group_class_map where class_id = {$cid} and group_id not in ({$gStr}) order by rand() limit {$groupNum}";
					$result = array ();
					$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
					
					foreach ($result as $value) {
						$groupInfo[] = $value;
						$gArr[] = $value['group_id'];
						$i = $i+1;
					}
					$gStr = implode(" , ", $gArr);
					if ($i<$renum) {
						$groupNum = $renum-$i;
					}
					else {
						break;
					}
				}
			
			}
			
			if ($i<$renum) {
				$richNum=$renum-$i;
				$gStr = implode(" , ", $gArr);
				$sqlComm = "select /*Recommend-ft*/group_id from t_seal_regroup where group_id not in ({$gStr}) order by rand() limit {$richNum}";
				$result = array ();
				$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
				foreach ($result as $value) {
					$groupInfo[] = $value;
				}
			}
			
			$reInfo = array();
			$count = 0;
			$i = 0;
			$len = count($groupInfo);
			foreach ($groupInfo as $value) {
				$isFollower == false;
				if ( ($len-$i) > ($renum_bak-$count) ) {
					$isFollower = $groupUserHelper->isGroupFollower($value['group_id'], $uid);
				}
				$i++;
				
				if (!$isFollower) {
					$reInfo[] = $value;
					$count++;
					if ($count >= $renum_bak) {
						break;
					}
				}
			}
			return $reInfo;
		}
		
        
	    $bestNum = 2;
	    $normalNum = $groupNum-$bestNum;
	    $gRecentArr = UserGroupFollower::lRange($uid, 0, 20);
		if (!is_array($gRecentArr)) {
			$gRecentArr=array();
		}
		$gRecentArr = array_filter($gRecentArr);
		
        if ($type!=10 && isset($gRecentArr[0])) {
			$recentUserGroupStr = implode(" , ", array_slice($gRecentArr,0,20));
			$sqlComm = "select /*Recommand-ft*/class_id from t_dolphin_group_class_map where group_id in ({$recentUserGroupStr}) and class_id not in (14,32,56,76,88,94,24) order by rand() limit 10";
			$recent_cresult = DBRecommendHelper::getConn()->read($sqlComm, array ());
			
			foreach ($recent_cresult as $key => $cvalue) {
				if ($i>=$renum) {
					break;
				}
				
				$cid = $cvalue['class_id'];
				if (isset($cid_map[$cid])) {
					continue;
				}
	            $cid_map[$cid] = 1;
	
	            if ($i<1) {
	            	/*
					$sqlComm = "select distinct group_id from t_seal_group_class_map where class_id = {$cid} and group_id not in ({$gStr}) and rate=10 order by rand() limit {$bestNum}";
					$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
					
					foreach ($result as $value) {
						$groupInfo[] = $value;
						$gArr[] = $value['group_id'];
						$i = $i+1;
					}
					$gStr = implode(" , ", $gArr);
					if ($i<$renum) {
						$groupNum = $renum-$i;
					}
					else {
						break;
		            }*/
		
					$sqlComm = "select /*Recommand-ft*/distinct group_id from t_seal_group_class_map where class_id = {$cid} and group_id not in ({$gStr}) and rate!=10 order by rand() limit {$groupNum}";
					$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
					
					foreach ($result as $value) {
						$groupInfo[] = $value;
						$gArr[] = $value['group_id'];
						$i = $i+1;
					}
					$gStr = implode(" , ", $gArr);
					if ($i<$renum) {
						$groupNum = $renum-$i;
					}
					else {
						break;
		            }
	            }
				
				$sqlComm = "select /*Recommand-ft*/distinct group_id from t_seal_group_class_map where class_id = {$cid} and group_id not in ({$gStr}) order by rand() limit {$groupNum}";
				$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
				
				foreach ($result as $value) {
					$groupInfo[] = $value;
					$gArr[] = $value['group_id'];
					$i = $i+1;
				}
				$gStr = implode(" , ", $gArr);
				if ($i<$renum) {
					$groupNum = $renum-$i;
				}
				else {
					break;
				}
			}
        }
        
        if (isset($aArr[0])) {
	        $aStr = implode(" , ", $aArr);
			
			$fgAttrs = array(0);
			$sqlComm = "select /*Recommend-ft*/word_id,same_to from t_dolphin_attr_words where isuse=1 and word_id in ({$aStr}) and (label_id in (10) or floor(label_id/100) in (10))";
			$attrRes = array ();
			$attrRes = DBRecommendHelper::getConn()->read($sqlComm, array ());
			foreach ($attrRes as $value) {
				$aid = $value['word_id'];
				if ($value['same_to']>0) {
					$aid = $value['same_to'];
				}
				$fgAttrs[] = $aid;
			}
			$fgStr = implode(" , ", $fgAttrs);
	
			$sqlComm = "select /*Recommend-ft*/class_id from t_seal_attr_groupclass_top where attr_id in ({$fgStr}) and rank<=5 and class_id not in (14,32,56,76,88,94,24) order by rand() limit 10";
			$cresult = array ();
			$cresult = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
			
			foreach ($cresult as $key => $cvalue) {
				if ($i>=$renum) {
					break;
				}
				
				$cid = $cvalue['class_id'];
				if (isset($cid_map[$cid])) {
					continue;
				}
	            $cid_map[$cid] = 1;
	
	            if ($i<1) {
	            	/*
				$sqlComm = "select distinct group_id from t_seal_group_class_map where class_id = {$cid} and group_id not in ({$gStr}) and rate=10 order by rand() limit {$bestNum}";
				$result = array ();
				$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
				
				foreach ($result as $value) {
					$groupInfo[] = $value;
					$gArr[] = $value['group_id'];
					$i = $i+1;
				}
				$gStr = implode(" , ", $gArr);
				if ($i<$renum) {
					$groupNum = $renum-$i;
				}
				else {
					break;
	            }*/
	
				$sqlComm = "select /*Recommend-ft*/distinct group_id from t_seal_group_class_map where class_id = {$cid} and group_id not in ({$gStr}) and rate!=10 order by rand() limit {$groupNum}";
				$result = array ();
				$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
				
				foreach ($result as $value) {
					$groupInfo[] = $value;
					$gArr[] = $value['group_id'];
					$i = $i+1;
				}
				$gStr = implode(" , ", $gArr);
				if ($i<$renum) {
					$groupNum = $renum-$i;
				}
				else {
					break;
	            }
	            }
				
				$sqlComm = "select /*Recommend-ft*/distinct group_id from t_seal_group_class_map where class_id = {$cid} and group_id not in ({$gStr}) order by rand() limit {$groupNum}";
				$result = array ();
				$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
				
				foreach ($result as $value) {
					$groupInfo[] = $value;
					$gArr[] = $value['group_id'];
					$i = $i+1;
				}
				$gStr = implode(" , ", $gArr);
				if ($i<$renum) {
					$groupNum = $renum-$i;
				}
				else {
					break;
				}
			}
			
			
			$sqlComm = "select /*Recommend-ft*/class_id from t_seal_attr_groupclass_top where attr_id in ({$aStr}) and attr_id not in ({$fgStr}) and rank<=5 and class_id not in (14,32,56,76,88,94,24) order by rand() limit 10";
			$cresult = array ();
			$cresult = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
			
			foreach ($cresult as $key => $cvalue) {
				if ($i>=$renum) {
					break;
				}
				
				$cid = $cvalue['class_id'];
				if (isset($cid_map[$cid])) {
					continue;
				}
				$cid_map[$cid] = 1;
				
	            if ($i<1) {
	            	/*
				$sqlComm = "select distinct group_id from t_seal_group_class_map where class_id = {$cid} and group_id not in ({$gStr}) and rate=10 order by rand() limit {$bestNum}";
				$result = array ();
				$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
				
				foreach ($result as $value) {
					$groupInfo[] = $value;
					$gArr[] = $value['group_id'];
					$i = $i+1;
				}
				$gStr = implode(" , ", $gArr);
				if ($i<$renum) {
					$groupNum = $renum-$i;
				}
				else {
					break;
	            }*/
	
				$sqlComm = "select /*Recommend-ft*/distinct group_id from t_seal_group_class_map where class_id = {$cid} and group_id not in ({$gStr}) and rate!=10 order by rand() limit {$groupNum}";
				$result = array ();
				$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
				
				foreach ($result as $value) {
					$groupInfo[] = $value;
					$gArr[] = $value['group_id'];
					$i = $i+1;
				}
				$gStr = implode(" , ", $gArr);
				if ($i<$renum) {
					$groupNum = $renum-$i;
				}
				else {
					break;
	            }
	            }
				
				$sqlComm = "select /*Recommend-ft*/distinct group_id from t_seal_group_class_map where class_id = {$cid} and group_id not in ({$gStr}) order by rand() limit {$groupNum}";
				$result = array ();
				$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
				
				foreach ($result as $value) {
					$groupInfo[] = $value;
					$gArr[] = $value['group_id'];
					$i = $i+1;
				}
				$gStr = implode(" , ", $gArr);
				if ($i<$renum) {
					$groupNum = $renum-$i;
				}
				else {
					break;
				}
			}
			
			$sqlComm = "select /*Recommend-ft*/class_id from t_seal_attr_groupclass_top where attr_id in ({$aStr}) and rank<=5 and class_id in (14,32,56,76,88,94,24) order by rand() limit 10";
			$cresult = array ();
			$cresult = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
			
			foreach ($cresult as $key => $cvalue) {
				if ($i>=$renum) {
					break;
				}
				
				$cid = $cvalue['class_id'];
				if (isset($cid_map[$cid])) {
					continue;
				}
				$cid_map[$cid] = 1;
				
	            if ($i<1) {
	            	/*
				$sqlComm = "select distinct group_id from t_seal_group_class_map where class_id = {$cid} and group_id not in ({$gStr}) and rate=10 order by rand() limit {$bestNum}";
				$result = array ();
				$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
				
				foreach ($result as $value) {
					$groupInfo[] = $value;
					$gArr[] = $value['group_id'];
					$i = $i+1;
				}
				$gStr = implode(" , ", $gArr);
				if ($i<$renum) {
					$groupNum = $renum-$i;
				}
				else {
					break;
	            }*/
	
				$sqlComm = "select /*Recommend-ft*/distinct group_id from t_seal_group_class_map where class_id = {$cid} and group_id not in ({$gStr}) and rate!=10 order by rand() limit {$groupNum}";
				$result = array ();
				$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
				
				foreach ($result as $value) {
					$groupInfo[] = $value;
					$gArr[] = $value['group_id'];
					$i = $i+1;
				}
				$gStr = implode(" , ", $gArr);
				if ($i<$renum) {
					$groupNum = $renum-$i;
				}
				else {
					break;
	            }
	            }
				
				$sqlComm = "select /*Recommend-ft*/distinct group_id from t_seal_group_class_map where class_id = {$cid} and group_id not in ({$gStr}) order by rand() limit {$groupNum}";
				$result = array ();
				$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
				
				foreach ($result as $value) {
					$groupInfo[] = $value;
					$gArr[] = $value['group_id'];
					$i = $i+1;
				}
				$gStr = implode(" , ", $gArr);
				if ($i<$renum) {
					$groupNum = $renum-$i;
				}
				else {
					break;
				}
			}
		}
		

		
		if ($i<$renum) {
			$richNum=$renum-$i;
			$gStr = implode(" , ", $gArr);
			$sqlComm = "select /*Recommend-ft*/group_id from t_seal_group_class_map where group_id not in ({$gStr}) order by rand() limit {$richNum}";
			$result = array ();
			$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
			foreach ($result as $value) {
				$groupInfo[] = $value;
			}
		}
		
		$reInfo = array();
		$count = 0;
		$i = 0;
		$len = count($groupInfo);
		foreach ($groupInfo as $value) {
			$isFollower == false;
			if ( ($len-$i) > ($renum_bak-$count) ) {
				$isFollower = $groupUserHelper->isGroupFollower($value['group_id'], $uid);
			}
			$i++;
			
			if (!$isFollower) {
				$reInfo[] = $value;
				$count++;
				if ($count >= $renum_bak) {
					break;
				}
			}
		}
		return $reInfo;
	}
	
	/*
	 * 提供一个user_id，返回推荐的杂志信息
	 */
	public function getReGroupByUid($uid, $renum=10) {
		/*
		 * AB测试控制
		 */
		/*
		global $GLOBAL_COOKIE_STRING;
		$sessid_len = strlen($GLOBAL_COOKIE_STRING);
		$lastc = "";
		if ($sessid_len>0){
			$lastc = $GLOBAL_COOKIE_STRING[$sessid_len-1];
		}
		$abNum=0;
		if (empty($GLOBAL_COOKIE_STRING) || ($sessid_len<=0) || ($lastc<"0" or $lastc>"9") ){
			$abNum = mt_rand(0,9);
		}
		else {
			$abNum = $lastc%10;
		}
		if ($abNum==0){
			return array();
		}
		*/
		
		$aArr = array();
		$i = 0;
		$anum = 10;
		try {//取用户最近浏览的属性属性页id
			$cache = Memcache::instance();
			$beh_key =  "user:action:history:".$_COOKIE['MEILISHUO_GLOBAL_KEY'];
			$beh = $cache->get($beh_key);
			$json_string=str_replace("'","\"",$beh);
			$obj=json_decode($json_string, true);
			if (isset($obj['a'])) {
				foreach ($obj['a'] as $aid_count){
					$i++;
					if ($i>5) {
						break;
					}
					$aArr[] = $aid_count['i'];
				}
			}
		}
		catch (Exception $e) {
		}
		if (!isset($aArr[0])) {
			$tmp = array();
			if ($i<10) {
				$tmp = $this->getAttrByUserTwitter($uid,$anum-$i);
			}
			else {
				$tmp = $this->getAttrByUserTwitter($uid,$anum);
			}
			$aArr = array_merge($aArr, $tmp);
		}
		return $this->getReGroupLimitByCata($aArr, 0, $uid, $renum, 1);
	}
	
	
	
	/*
	 * 提供一个group_id，返回推荐的杂志信息（默认5个）
	 */
	public function getReGroupByGroupid($group_id, $uid=0, $renum=5, $type=1) {
		/*
		 * AB测试控制
		 */
		/*
		global $GLOBAL_COOKIE_STRING;
		$sessid_len = strlen($GLOBAL_COOKIE_STRING);
		$lastc = "";
		if ($sessid_len>0){
			$lastc = $GLOBAL_COOKIE_STRING[$sessid_len-1];
		}
		$abNum=0;
		if (empty($GLOBAL_COOKIE_STRING) || ($sessid_len<=0) || ($lastc<"0" or $lastc>"9") ){
			$abNum = mt_rand(0,9);
		}
		else {
			$abNum = $lastc%10;
		}
		if ($abNum==0){
			return array();
		}
		*/
		
		$aArr = array();
		if ($type<10) {
			$aArr = $this->getAttrByGroupTwitter($group_id,10);
			return $this->getReGroupLimitByCata($aArr, $group_id, $uid, $renum, 1);
		}
				
		$aArr = array();
		$sqlComm = "select /*Recommend-ft*/attr_id from t_seal_group_attr_top where group_id={$group_id} and rank<=6 and tag=0 
		 order by rand() limit 5";
		$attrRes = array ();
		$attrRes = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
		if (!isset($attrRes[0])) {
			$aArr = $this->getAttrByGroupTwitter($group_id,5);
			if (!isset($aArr[0])) {
				return $this->getReGroup($renum,$group_id,$uid);
			}
		}
		
		foreach ($attrRes as $value) {
			$aArr[] = $value['attr_id'];
		}
		if (!isset($aArr[0])) {
			return $this->getReGroup($renum,$group_id,$uid);
		}
		$aStr = implode(" , ", $aArr);
		
		$groupInfo = array();
		//$richNum = 2;
		//$groupNum = $renum-$richNum;
		$renum_bak = $renum;
		$renum = 2 * $renum;
		$richNum = 0;
		$groupNum = $renum-$richNum;
		
		/*
		$role = array(0,1,4,5);
		$userGroup = array();
		if ($uid>0){
			$userGroup = TopicGroupUserModel::getInstance()->getUserGroups($uid, $role);
		}
		$gArr = array();
		$gArr = DataToArray($userGroup, 'group_id');
		$gArr[] = $group_id;
		$gStr = implode(" , ", $gArr);
		*/
		
		/*
		$gArr = UserGroupFollower::lRange($uid, 0, 1500);
		if (!is_array($gArr)) {
			$gArr=array();
		}
		$gArr = array_filter($gArr);
		$gArr[] = $group_id;
		$gStr = implode(" , ", $gArr);
		*/
		$gArr = array();
		$gArr[] = $group_id;
		$gStr = implode(" , ", $gArr);
		
		$sqlComm = "select /*Recommend-ft*/distinct group_id from t_seal_group_attr_top where attr_id in ({$aStr}) and rank<=6 and group_id not in ({$gStr}) order by rand() limit {$groupNum}";
		$result = array ();
		$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
		
		$i=0;
		foreach ($result as $value) {
			$groupInfo[] = $value;
			$gArr[] = $value['group_id'];
			$i = $i+1;
		}
		$gStr = implode(" , ", $gArr);
		
		if ($i<$groupNum) {
			$richNum=$renum-$i;
		}
		$sqlComm = "select /*Recommend-ft*/group_id from t_seal_regroup where group_id not in ({$gStr}) order by rand() limit {$richNum}";
		$result = array ();
		$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
		foreach ($result as $value) {
			$groupInfo[] = $value;
		}

		$groupUserHelper = new GroupUser();
		$reInfo = array();
		$count = 0;
		$i = 0;
		$len = count($groupInfo);
		foreach ($groupInfo as $value) {
			$isFollower == false;
			if ( ($len-$i) > ($renum_bak-$count) ) {
				$isFollower = $groupUserHelper->isGroupFollower($value['group_id'], $uid);
			}
			$i++;
			
			if (!$isFollower) {
				$reInfo[] = $value;
				$count++;
				if ($count >= $renum_bak) {
					break;
				}
			}
		}
		return $reInfo;
	}
	
	/*
	 * 提供一个attr_id，返回推荐的杂志信息（共5个）
	 * type参数为1表示属性页请求，为10表示搜索页请求
	 */
	public function getReGroupByAid($attr_id=-1, $type=0, $renum=5, $uid=0) {
		/*
		 * AB测试控制
		 */
		/*
		global $GLOBAL_COOKIE_STRING;
		$sessid_len = strlen($GLOBAL_COOKIE_STRING);
		$lastc = "";
		if ($sessid_len>0){
			$lastc = $GLOBAL_COOKIE_STRING[$sessid_len-1];
		}
		$abNum=0;
		if (empty($GLOBAL_COOKIE_STRING) || ($sessid_len<=0) || ($lastc<"0" or $lastc>"9") ){
			$abNum = mt_rand(0,9);
		}
		else {
			$abNum = $lastc%10;
		}
		if ($abNum==0){
			return $this->getReGroup($renum,0,$uid);
		}
		*/
		$abNum = 1;
		
		//$sqlComm = "select /*Recommend-ft*/attr_id2 attr_id from t_seal_attr_attr_top where attr_id={$attr_id} and rank<=6 and tag=0 
		// order by rank limit 5";
		if (empty($attr_id)) {
			$attr_id = -1;
		}
		$sqlComm = "select /*Recommend-ft*/attr_id2 attr_id from t_seal_attr_attr_top where attr_id={$attr_id} and rank<=5 order by rank limit 5";
		if ($type==10) {
			$sqlComm = "select /*Recommend-ft*/attr_id2 attr_id from t_seal_attr_attr_top where attr_id={$attr_id} and rank<=1 limit 1";
		}
		$attrRes = array ();
		$attrRes = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
		if (!isset($attrRes[0])) {
			return $this->getReGroup($renum,0,$uid);
		}
		
		$aArr = array();
		foreach ($attrRes as $value) {
			$aArr[] = $value['attr_id'];
		}
		if (!isset($aArr[0])) {
			return $this->getReGroup($renum,0,$uid);
		}
		$aStr = implode(" , ", $aArr);
		
		$groupInfo = array();
		//$richNum = 0;
		//$groupNum = $renum-$richNum;
		$renum_bak = $renum;
		$renum = 2 * $renum;
		$richNum = 0;
		$groupNum = $renum-$richNum;
		
		$gArr = array();
		//type为10说明是搜索使用，此时不管用户是否曾经关注过杂志
		if ($type!=10) {
			/*
			$role = array(0,1,4,5);
			$userGroup = array();
			if ($uid>0){
				$userGroup = TopicGroupUserModel::getInstance()->getUserGroups($uid, $role);
			}
			$gArr = DataToArray($userGroup, 'group_id');
			//防止query过大
			if (count($gArr)>200) {
				$gArr = array();
			}
			*/
			/*
			$gArr = UserGroupFollower::lRange($uid, 0, 1500);
			if (!is_array($gArr)) {
				$gArr=array();
			}
			*/
			$gArr = array();
		}
		//$gArr = array_filter($gArr);
		$gArr[] = 0;
		$gStr = implode(" , ", $gArr);
		
		$sqlComm = "select /*Recommend-ft*/distinct group_id from t_seal_group_name_attr where attr_id={$attr_id} and group_id not in ({$gStr}) order by rand() limit {$groupNum}";
		$result = array ();
		$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
		$i=0;
		foreach ($result as $value) {
			//$cur_group_id = $value['group_id'];
			//$sqlComm = "select /*Recommend-ft*/group_id from t_whale_topic_group where group_id={$cur_group_id} limit 1";
			//$tmpresult = array ();
			//$tmpresult = DBRecommendWhaleHelper::getConn()->read($sqlComm, array ());
			//if (count($tmpresult)<1) {
			//	continue;
			//}
			$groupInfo[] = $value;
			$gArr[] = $value['group_id'];
			$i = $i+1;
		}
		$gStr = implode(" , ", $gArr);
		
		if ($i<$groupNum) {
			$groupNum = $groupNum-$i;
			$sqlComm = "select /*Recommend-ft*/distinct group_id from t_seal_group_attr_top where attr_id in ({$aStr}) and rank<=6 and group_id not in ({$gStr}) order by rand() limit {$groupNum}";
			$result = array ();
			$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
			$i=0;
			foreach ($result as $value) {
				//$cur_group_id = $value['group_id'];
				//$sqlComm = "select /*Recommend-ft*/group_id from t_whale_topic_group where group_id={$cur_group_id} limit 1";
				//$tmpresult = array ();
				//$tmpresult = DBRecommendWhaleHelper::getConn()->read($sqlComm, array ());
				//if (count($tmpresult)<1) {
				//	continue;
				//}
				$groupInfo[] = $value;
				$gArr[] = $value['group_id'];
				$i = $i+1;
			}
			$gStr = implode(" , ", $gArr);
		}
		
		/*
		 * 对于非搜索类请求，数量不够时返回发散结果
		 */
		if ($type!=10) {
			if ($i<$groupNum) {
				$richNum=$richNum+$groupNum-$i;
			}
			$sqlComm = "select /*Recommend-ft*/distinct group_id from t_seal_group_class_map where group_id not in ({$gStr}) order by rand() limit {$richNum}";
			$result = array ();
			$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
			foreach ($result as $value) {
				$groupInfo[] = $value;
			}
		}

		$groupUserHelper = new GroupUser();
		$reInfo = array();
		$count = 0;
		$i = 0;
		$len = count($groupInfo);
		foreach ($groupInfo as $value) {
			$isFollower == false;
			if ( ($len-$i) > ($renum_bak-$count) ) {
				$isFollower = $groupUserHelper->isGroupFollower($value['group_id'], $uid);
			}
			$i++;
			
			if (!$isFollower) {
				$reInfo[] = $value;
				$count++;
				if ($count >= $renum_bak) {
					break;
				}
			}
		}
		return $reInfo;
	}
	
	/*
	 * 提供一个attr_id数组，返回推荐的杂志社信息
	 * type参数为10表示搜索页请求
	 */
	public function getReGroupByAidArr($aArr, $type=0, $renum=5, $uid=0) {
		$renum_bak = $renum;
		if (!isset($aArr[0])) {
			if ($type == 10) {
				try {//取用户最近浏览的属性属性页id
					$cache = Memcache::instance();
					$beh_key =  "user:action:history:".$_COOKIE['MEILISHUO_GLOBAL_KEY'];
					$beh = $cache->get($beh_key);
					$json_string=str_replace("'","\"",$beh);
					$obj=json_decode($json_string, true);
					if (isset($obj['a'])) {
						$i = 0;
						foreach ($obj['a'] as $aid_count){
							$i++;
							if ($i>5) {
								break;
							}
							$aArr[] = $aid_count['i'];
						}
					}
				}
				catch (Exception $e) {
				}
				if (!isset($aArr[0])) {
					return $this->getReGroup($renum,0);
				}
			}
			else {
				return $this->getReGroup($renum,0);
			}
		}
		$aStr = implode(" , ", $aArr);
		
		$groupInfo = array();
		$group_map = array();
		
		$sqlComm = "select /*Recommend-ft*/word_id,same_to from t_dolphin_attr_words where isuse=1 and word_id in ({$aStr}) and (label_id in (13,14,17,20) or floor(label_id/100) in (13,14,17,20))";
		$attrRes = array ();
		$attrRes = DBRecommendHelper::getConn()->read($sqlComm, array ());
		foreach ($attrRes as $value) {
			if ($renum<1) {
				return $groupInfo;
			}
			$aid = $value['word_id'];
			if ($value['same_to']>0) {
				$aid = $value['same_to'];
			}
			$tmpRes = $this->getReGroupByAid($aid, $type, $renum, $uid);
			$c=0;
			foreach ($tmpRes as $tmpValue) {
				if (isset($group_map[$tmpValue['group_id']])) {
					continue;
				}
				$group_map[$tmpValue['group_id']] = 1;
				$groupInfo[] = $tmpValue;
				$c++;
			}
			$renum = $renum-$c;
		}
		
		$sqlComm = "select /*Recommend-ft*/word_id,same_to from t_dolphin_attr_words where isuse=1 and word_id in ({$aStr}) and (label_id not in (13,14,17,20) and floor(label_id/100) not in (13,14,17,20))";
		$attrRes = array ();
		$attrRes = DBRecommendHelper::getConn()->read($sqlComm, array ());
		foreach ($attrRes as $value) {
			if ($renum<1) {
				return $groupInfo;
			}
			$aid = $value['word_id'];
			if ($value['same_to']>0) {
				$aid = $value['same_to'];
			}
			$tmpRes = $this->getReGroupByAid($aid, $type, $renum, $uid);
			$c=0;
			foreach ($tmpRes as $tmpValue) {
				if (isset($group_map[$tmpValue['group_id']])) {
					continue;
				}
				$group_map[$tmpValue['group_id']] = 1;
				$groupInfo[] = $tmpValue;
				$c++;
			}
			$renum = $renum-$c;
		}
		
		if (count($groupInfo)<1) {
			return $this->getReGroup($renum_bak,0);
		}
		
		return $groupInfo;
	}
	
	
	/*
	 * 选取若干个经算法验证的用户
	 */
	public function getReUser($renum,$uid=0) {
		$uArr = self::$following_uids;
		$uArr = array_filter($uArr);
		$uArr[] = $uid;
		$uStr = implode(" , ", $uArr);
		
		$userInfo = array();
		$sqlComm = "select /*Recommend-ft*/distinct user_id from t_seal_user_attr_top where user_id not in ({$uStr}) and (special_str!='' or buyer_tag=2) order by rand() limit {$renum}";
		$result = array ();
		$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
		foreach ($result as $value) {
			$userInfo[] = $value;
		}
		return $userInfo;
	}
	

	/*
	 * 提供一个attr_id，返回推荐用户信息
	 * type参数为1表示属性页请求，为10表示搜索页请求
	 */
	public function getReUserByAid($attr_id, $type=0, $renum=6, $uid=0) {
		$sqlComm = "select /*Recommend-ft*/attr_id2 attr_id from t_seal_attr_attr_top where attr_id={$attr_id} and rank<=6 and tag=0 
		 order by rank limit 5";
		if ($type==10) {
			$sqlComm = "select /*Recommend-ft*/attr_id2 attr_id from t_seal_attr_attr_top where attr_id={$attr_id} and rank<=1 limit 1";
		}
		$attrRes = array ();
		$attrRes = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
		if (!isset($attrRes[0])) {
			return array();
		}
		
		$aArr = array();
		foreach ($attrRes as $value) {
			$aArr[] = $value['attr_id'];
		}
		if (!isset($aArr[0])) {
			return array();
		}
		$aStr = implode(" , ", $aArr);
		
		$userInfo = array();
		$richNum = 0;
		$userNum = $renum-$richNum;
		
		$uArr = self::$following_uids;
		$uArr = array_filter($uArr);
		$uArr[] = $uid;
		$uStr = implode(" , ", $uArr);
		
		$sqlComm = "select /*Recommend-ft*/distinct user_id from t_seal_user_attr_top where attr_id in ({$aStr}) and rank<=5 and user_id not in ({$uStr}) and (special_str!='' or buyer_tag=2) order by rand() limit {$userNum}";
		$result = array ();
		$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
		$i=0;
		foreach ($result as $value) {
			$userInfo[] = $value;
			$uArr[] = $value['user_id'];
			$i = $i+1;
		}
		$uStr = implode(" , ", $uArr);
		
		if ($i<$userNum) {
			$userNum = $userNum-$i;
			$sqlComm = "select /*Recommend-ft*/distinct user_id from t_seal_user_attr_top where attr_id in ({$aStr}) and rank<=5 and user_id not in ({$uStr}) and (buyer_tag=1 or medal_num>=3) order by rand() limit {$userNum}";
			$result = array ();
			$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
			$i=0;
			foreach ($result as $value) {
				$userInfo[] = $value;
				$uArr[] = $value['user_id'];
				$i = $i+1;
			}
			$uStr = implode(" , ", $uArr);
		}
		
		
		if ($i<$userNum) {
			$userNum = $userNum-$i;
			$sqlComm = "select /*Recommend-ft*/distinct user_id from t_seal_user_attr_top where attr_id in ({$aStr}) and rank<=5 and user_id not in ({$uStr}) order by rand() limit {$userNum}";
			$result = array ();
			$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
			$i=0;
			foreach ($result as $value) {
				$userInfo[] = $value;
				$uArr[] = $value['user_id'];
				$i = $i+1;
			}
			$uStr = implode(" , ", $uArr);
		}
		
		
		return $userInfo;
	}
	
	/*
	 * 提供用户id和attr_id数组，返回推荐的用户信息
	 * type参数为10表示搜索页请求
	 */
	public function getReUserByAidArr($aArr=array(), $type=0, $renum=12, $uid=0, $uid2=0) {
		$renum_bak = $renum;
		$userInfo = array();
		$user_map = array();
		
		if ($uid2>0) {
			self::$following_uids = UserFollow::getFollow($uid2, 'DESC', 0, 600);
			self::$following_size = UserFollow::getFollowNumber($uid2);
		}
		elseif ($uid>0) {
			self::$following_uids = UserFollow::getFollow($uid, 'DESC', 0, 600);
			self::$following_size = UserFollow::getFollowNumber($uid);
		}
		
		if (!isset($aArr[0])) {
			if ($uid<1) {
				$tmpRes = $this->getReUser($renum,$uid);
				foreach ($tmpRes as $tmpValue) {
					if (isset($user_map[$tmpValue['user_id']])) {
						continue;
					}
					$user_map[$tmpValue['user_id']] = 1;
					$userInfo[] = $tmpValue;
				}
				return $userInfo;
			}
			$aArr = $this->getAttrByUserTwitter($uid,5);
			if (!isset($aArr[0])) {
				$tmpRes = $this->getReUser($renum,$uid);
				foreach ($tmpRes as $tmpValue) {
					if (isset($user_map[$tmpValue['user_id']])) {
						continue;
					}
					$user_map[$tmpValue['user_id']] = 1;
					$userInfo[] = $tmpValue;
				}
				return $userInfo;
			}
		}
		
		$aStr = implode(" , ", $aArr);
		
		$sqlComm = "select /*Recommend-ft*/word_id,same_to from t_dolphin_attr_words where isuse=1 and word_id in ({$aStr}) and (label_id in (13,14,17,20) or floor(label_id/100) in (13,14,17,20))";
		$attrRes = array ();
		$attrRes = DBRecommendHelper::getConn()->read($sqlComm, array ());
		foreach ($attrRes as $value) {
			if ($renum<1) {
				return $userInfo;
			}
			$aid = $value['word_id'];
			if ($value['same_to']>0) {
				$aid = $value['same_to'];
			}
			$tmpRes = $this->getReUserByAid($aid, $type, $renum, $uid);
			$c=0;
			foreach ($tmpRes as $tmpValue) {
				if (isset($user_map[$tmpValue['user_id']])) {
					continue;
				}
				$user_map[$tmpValue['user_id']] = 1;
				$userInfo[] = $tmpValue;
				$c++;
			}
			$renum = $renum-$c;
		}
		
		$sqlComm = "select /*Recommend-ft*/word_id,same_to from t_dolphin_attr_words where isuse=1 and word_id in ({$aStr}) and (label_id not in (13,14,17,20) and floor(label_id/100) not in (13,14,17,20))";
		$attrRes = array ();
		$attrRes = DBRecommendHelper::getConn()->read($sqlComm, array ());
		foreach ($attrRes as $value) {
			if ($renum<1) {
				return $userInfo;
			}
			$aid = $value['word_id'];
			if ($value['same_to']>0) {
				$aid = $value['same_to'];
			}
			$tmpRes = $this->getReUserByAid($aid, $type, $renum, $uid);
			$c=0;
			foreach ($tmpRes as $tmpValue) {
				if (isset($user_map[$tmpValue['user_id']])) {
					continue;
				}
				$user_map[$tmpValue['user_id']] = 1;
				$userInfo[] = $tmpValue;
				$c++;
			}
			$renum = $renum-$c;
		}
		
		
		if (count($userInfo)<1) {
			return $this->getReUser($renum_bak,$uid);
		}
		
		if ($renum>=1) {
			$sqlComm = "select /*Recommend-ft*/distinct user_id from t_seal_user_attr_top where special_str!='' or buyer_tag=2 order by rand() limit {$renum_bak}";
			$result = array ();
			$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
			foreach ($result as $value) {
				if ($renum<1) {
					break;
				}
				if (isset($user_map[$value['user_id']])) {
					continue;
				}
				$user_map[$value['user_id']] = 1;
				$userInfo[] = $value;
				$renum--;
			}
		}
		
		return $userInfo;
	}
	
	
	/*
	 * 提供一个goods_id，返回推荐信息（属性、宝贝等）
	 */
	public function getReDataByGid($gid) {
		$reData = array ();
		$sqlComm = "select /*Recommend-ft*/attr_id from t_seal_goods_attr_top where goods_id={$gid} and tag=0 
		 order by rank asc";
		$attrRes = array ();
		$attrRes = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
		if (!isset($attrRes[0])) {
			return array();
		}
		
		$aArr = array();
		foreach ($attrRes as $value) {
			$aArr[] = $value['attr_id'];
		}
		if (!isset($aArr[0])) {
			return array();
		}
		$aStr = implode(" , ", $aArr);
		
		$reAttrNum1 = 3;
		$attrMap = array();
		$i = 0;
		//热门
		foreach ($attrRes as $value) {
			if ($i>=$reAttrNum1){
				break;
			}
			$aid = $value['attr_id'];
			$sqlComm = "select /*Recommend-ft*/word_name from t_dolphin_attr_words where word_id={$aid} and isuse=1 and isred=1 limit 1";
			$result = array ();
			$result = DBRecommendHelper::getConn()->read($sqlComm, array ());
			if (!isset($result[0])) {
				continue;
			}
			
			$sqlComm = "select /*Recommend-ft*/goods_num from t_seal_attr_regoods_num where attr_id={$aid}";
			$numRes = array ();
			$numRes = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
			if (!isset($numRes[0])) {
				continue;
			}
			if ($numRes[0]['goods_num']<5) {
				continue;
			}
			
			$sqlComm = "select /*Recommend-ft*/distinct goods_id from t_seal_attr_regoods_top where attr_id={$aid} order by rand() limit 5";
			$goodsRes = array ();
			$goodsRes = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
			if (!isset($goodsRes[4])) {
				continue;
			}
			else {
				$reData[0][$i][$aid][1] = $goodsRes;
			}
			
			$reData[0][$i][$aid][0]=$result[0]['word_name'];
			$attrMap[$aid] = 1;
			$i++;
		}
		//元素
		foreach ($attrRes as $value) {
			if ($i>=$reAttrNum1){
				break;
			}
			if (isset($attrMap[$value['attr_id']])) {
				continue;
			}
			$aid = $value['attr_id'];
			$sqlComm = "select /*Recommend-ft*/word_name from t_dolphin_attr_words where word_id={$aid} and isuse=1 and (label_id =11 or floor(label_id/100)=11 or label_id=16 or floor(label_id/100)=16) limit 1";
			$result = array ();
			$result = DBRecommendHelper::getConn()->read($sqlComm, array ());
			if (!isset($result[0])) {
				continue;
			}
			
			$sqlComm = "select /*Recommend-ft*/goods_num from t_seal_attr_regoods_num where attr_id={$aid}";
			$numRes = array ();
			$numRes = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
			if (!isset($numRes[0])) {
				continue;
			}
			if ($numRes[0]['goods_num']<5) {
				continue;
			}
			
			$sqlComm = "select /*Recommend-ft*/distinct goods_id from t_seal_attr_regoods_top where attr_id={$aid} order by rand() limit 5";
			$goodsRes = array ();
			$goodsRes = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
			if (!isset($goodsRes[4])) {
				continue;
			}
			else {
				$reData[0][$i][$aid][1] = $goodsRes;
			}
			
			$reData[0][$i][$aid][0]=$result[0]['word_name'];
			$attrMap[$aid] = 1;
			$i++;
		}
		//风格
		foreach ($attrRes as $value) {
			if ($i>=$reAttrNum1){
				break;
			}
			if (isset($attrMap[$value['attr_id']])) {
				continue;
			}
			$aid = $value['attr_id'];
			$sqlComm = "select /*Recommend-ft*/word_name from t_dolphin_attr_words where word_id={$aid} and isuse=1 and (label_id=10 or floor(label_id/100)=10 or label_id=18 or floor(label_id/100)=18) limit 1";
			$result = array ();
			$result = DBRecommendHelper::getConn()->read($sqlComm, array ());
			if (!isset($result[0])) {
				continue;
			}
			
			$sqlComm = "select /*Recommend-ft*/goods_num from t_seal_attr_regoods_num where attr_id={$aid}";
			$numRes = array ();
			$numRes = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
			if (!isset($numRes[0])) {
				continue;
			}
			if ($numRes[0]['goods_num']<5) {
				continue;
			}
			
			$sqlComm = "select /*Recommend-ft*/distinct goods_id from t_seal_attr_regoods_top where attr_id={$aid} order by rand() limit 5";
			$goodsRes = array ();
			$goodsRes = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
			if (!isset($goodsRes[4])) {
				continue;
			}
			else {
				$reData[0][$i][$aid][1] = $goodsRes;
			}
			
			$reData[0][$i][$aid][0]=$result[0]['word_name'];
			$attrMap[$aid] = 1;
			$i++;
		}
		//其他
		foreach ($attrRes as $value) {
			if ($i>=$reAttrNum1){
				break;
			}
			if (isset($attrMap[$value['attr_id']])) {
				continue;
			}
			$aid = $value['attr_id'];
			$sqlComm = "select /*Recommend-ft*/word_name from t_dolphin_attr_words where word_id={$aid} and isuse=1 limit 1";
			$result = array ();
			$result = DBRecommendHelper::getConn()->read($sqlComm, array ());
			if (!isset($result[0])) {
				continue;
			}
			
			$sqlComm = "select /*Recommend-ft*/goods_num from t_seal_attr_regoods_num where attr_id={$aid}";
			$numRes = array ();
			$numRes = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
			if (!isset($numRes[0])) {
				continue;
			}
			if ($numRes[0]['goods_num']<5) {
				continue;
			}
			
			$sqlComm = "select /*Recommend-ft*/distinct goods_id from t_seal_attr_regoods_top where attr_id={$aid} order by rand() limit 5";
			$goodsRes = array ();
			$goodsRes = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
			if (!isset($goodsRes[4])) {
				continue;
			}
			else {
				$reData[0][$i][$aid][1] = $goodsRes;
			}
			
			$reData[0][$i][$aid][0]=$result[0]['word_name'];
			$attrMap[$aid] = 1;
			$i++;
		}
		
		$attrInfo = array();
		$i = 0;
		foreach ($attrRes as $value) {
			if ($i>=self::$maxReAttrNum){
				break;
			}
			if (isset($attrMap[$value['attr_id']])) {
				continue;
			}
			$aid = $value['attr_id'];
			$sqlComm = "select /*Recommend-ft*/word_id,word_name from t_dolphin_attr_words where word_id = {$aid} and isuse=1 limit 1";
			$result = array ();
			$result = DBRecommendHelper::getConn()->read($sqlComm, array ());
			if (!isset($result[0])) {
				continue;
			}
			$attrInfo[] = $result[0];
			$i++;
		}
		if ($i<self::$maxReAttrNum){
			$randNum = self::$maxReAttrNum-$i;
			$sqlComm = "select /*Recommend-ft*/word_id,word_name from t_dolphin_attr_words where word_id not in ({$aStr}) and isuse=1 order by rand() limit {$randNum}";
			$result = array ();
			$result = DBRecommendHelper::getConn()->read($sqlComm, array ());
			foreach ($result as $value) {
				$attrInfo[] = $value;
			}
		}
		$reData[1] = $attrInfo;
		return $reData;
	}
	
	/*
	 * 在无数据情况下返回推荐信息（属性、宝贝等）
	 */
	public function getReDataByRand() {
		$reData = array ();
		
		$reAttrNum1 = 3;
		$sqlComm = "select /*Recommend-ft*/word_id,word_name from t_dolphin_attr_words where isuse=1 and isred=1 order by rand() limit {$reAttrNum1}";
		$result = array ();
		$result = DBRecommendHelper::getConn()->read($sqlComm, array ());
		if (!isset($result[0])) {
			$reData[0] = array();
		}
		$i=0;
		foreach ($result as $value) {
			$aid = $value['word_id'];
			
			$sqlComm = "select /*Recommend-ft*/goods_num from t_seal_attr_regoods_num where attr_id={$aid}";
			$numRes = array ();
			$numRes = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
			if (!isset($numRes[0])) {
				continue;
			}
			if ($numRes[0]['goods_num']<5) {
				continue;
			}
			
			$sqlComm = "select /*Recommend-ft*/distinct goods_id from t_seal_attr_regoods_top where attr_id={$aid} order by rand() limit 5";
			$goodsRes = array ();
			$goodsRes = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
			if (!isset($goodsRes[4])) {
				continue;
			}
			else {
				$reData[0][$i][$aid][1] = $goodsRes;
			}
			
			$reData[0][$i][$aid][0]=$value['word_name'];

			$i++;
		}
		
		$randNum = self::$maxReAttrNum;
		$sqlComm = "select /*Recommend-ft*/word_id,word_name from t_dolphin_attr_words where isuse=1 order by rand() limit {$randNum}";
		$result = array ();
		$result = DBRecommendHelper::getConn()->read($sqlComm, array ());
		foreach ($result as $value) {
			$attrInfo[] = $value;
		}
		
		$reData[1] = $attrInfo;
		return $reData;
	}
	
	/*
	 * 提供一个属性数组，返回推荐信息（属性、宝贝等）
	 */
	public function getReDataByArr($attrRes) {
		$reData = array ();
		
		if (!isset($attrRes[0])) {
			return $this->getReDataByRand();
		}

		$aArr = array();
		foreach ($attrRes as $value) {
			$aArr[] = $value['attr_id'];
		}
		if (!isset($aArr[0])) {
			return $this->getReDataByRand();
		}
		$aStr = implode(" , ", $aArr);
		
		$reAttrNum1 = 3;
		$attrMap = array();
		$i = 0;
		//热门
		foreach ($attrRes as $value) {
			if ($i>=$reAttrNum1){
				break;
			}
			$aid = $value['attr_id'];
			$sqlComm = "select /*Recommend-ft*/word_name from t_dolphin_attr_words where word_id={$aid} and isuse=1 and isred=1 limit 1";
			$result = array ();
			$result = DBRecommendHelper::getConn()->read($sqlComm, array ());
			if (!isset($result[0])) {
				continue;
			}
			
			$sqlComm = "select /*Recommend-ft*/goods_num from t_seal_attr_regoods_num where attr_id={$aid}";
			$numRes = array ();
			$numRes = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
			if (!isset($numRes[0])) {
				continue;
			}
			if ($numRes[0]['goods_num']<5) {
				continue;
			}
			
			$sqlComm = "select /*Recommend-ft*/distinct goods_id from t_seal_attr_regoods_top where attr_id={$aid} order by rand() limit 5";
			$goodsRes = array ();
			$goodsRes = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
			if (!isset($goodsRes[4])) {
				continue;
			}
			else {
				$reData[0][$i][$aid][1] = $goodsRes;
			}
			
			$reData[0][$i][$aid][0]=$result[0]['word_name'];
			$attrMap[$aid] = 1;
			$i++;
		}
		//元素
		foreach ($attrRes as $value) {
			if ($i>=$reAttrNum1){
				break;
			}
			if (isset($attrMap[$value['attr_id']])) {
				continue;
			}
			$aid = $value['attr_id'];
			$sqlComm = "select /*Recommend-ft*/word_id,word_name from t_dolphin_attr_words where word_id={$aid} and isuse=1 and (label_id =11 or floor(label_id/100)=11 or label_id=16 or floor(label_id/100)=16) limit 1";
			$result = array ();
			$result = DBRecommendHelper::getConn()->read($sqlComm, array ());
			if (!isset($result[0])) {
				continue;
			}
			
			$sqlComm = "select /*Recommend-ft*/goods_num from t_seal_attr_regoods_num where attr_id={$aid}";
			$numRes = array ();
			$numRes = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
			if (!isset($numRes[0])) {
				continue;
			}
			if ($numRes[0]['goods_num']<5) {
				continue;
			}
			
			$sqlComm = "select /*Recommend-ft*/distinct goods_id from t_seal_attr_regoods_top where attr_id={$aid} order by rand() limit 5";
			$goodsRes = array ();
			$goodsRes = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
			if (!isset($goodsRes[4])) {
				continue;
			}
			else {
				$reData[0][$i][$aid][1] = $goodsRes;
			}
			
			$reData[0][$i][$aid][0]=$result[0]['word_name'];
			$attrMap[$aid] = 1;
			$i++;
		}
		//时尚
		foreach ($attrRes as $value) {
			if ($i>=$reAttrNum1){
				break;
			}
			if (isset($attrMap[$value['attr_id']])) {
				continue;
			}
			$aid = $value['attr_id'];
			$sqlComm = "select /*Recommend-ft*/word_id,word_name from t_dolphin_attr_words where word_id={$aid} and isuse=1 and (label_id=10 or floor(label_id/100)=10 or label_id=18 or floor(label_id/100)=18) limit 1";
			$result = array ();
			$result = DBRecommendHelper::getConn()->read($sqlComm, array ());
			if (!isset($result[0])) {
				continue;
			}
			
			$sqlComm = "select /*Recommend-ft*/goods_num from t_seal_attr_regoods_num where attr_id={$aid}";
			$numRes = array ();
			$numRes = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
			if (!isset($numRes[0])) {
				continue;
			}
			if ($numRes[0]['goods_num']<5) {
				continue;
			}
			
			$sqlComm = "select /*Recommend-ft*/distinct goods_id from t_seal_attr_regoods_top where attr_id={$aid} order by rand() limit 5";
			$goodsRes = array ();
			$goodsRes = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
			if (!isset($goodsRes[4])) {
				continue;
			}
			else {
				$reData[0][$i][$aid][1] = $goodsRes;
			}
			
			$reData[0][$i][$aid][0]=$result[0]['word_name'];
			$attrMap[$aid] = 1;
			$i++;
		}
		//其他
		foreach ($attrRes as $value) {
			if ($i>=$reAttrNum1){
				break;
			}
			if (isset($attrMap[$value['attr_id']])) {
				continue;
			}
			$aid = $value['attr_id'];
			$sqlComm = "select /*Recommend-ft*/word_id,word_name from t_dolphin_attr_words where word_id={$aid} and isuse=1 limit 1";
			$result = array ();
			$result = DBRecommendHelper::getConn()->read($sqlComm, array ());
			if (!isset($result[0])) {
				continue;
			}
			
			$sqlComm = "select /*Recommend-ft*/goods_num from t_seal_attr_regoods_num where attr_id={$aid}";
			$numRes = array ();
			$numRes = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
			if (!isset($numRes[0])) {
				continue;
			}
			if ($numRes[0]['goods_num']<5) {
				continue;
			}
			
			$sqlComm = "select /*Recommend-ft*/distinct goods_id from t_seal_attr_regoods_top where attr_id={$aid} order by rand() limit 5";
			$goodsRes = array ();
			$goodsRes = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
			if (!isset($goodsRes[4])) {
				continue;
			}
			else {
				$reData[0][$i][$aid][1] = $goodsRes;
			}
			
			$reData[0][$i][$aid][0]=$result[0]['word_name'];
			$attrMap[$aid] = 1;
			$i++;
		}
		
		$attrInfo = array();
		$i = 0;
		foreach ($attrRes as $value) {
			if ($i>=self::$maxReAttrNum){
				break;
			}
			if (isset($attrMap[$value['attr_id']])) {
				continue;
			}
			$aid = $value['attr_id'];
			$sqlComm = "select /*Recommend-ft*/word_id,word_name from t_dolphin_attr_words where word_id = {$aid} and isuse=1 limit 1";
			$result = array ();
			$result = DBRecommendHelper::getConn()->read($sqlComm, array ());
			if (!isset($result[0])) {
				continue;
			}
			$attrInfo[] = $result[0];
			$i++;
		}
		if ($i<self::$maxReAttrNum){
			$randNum = self::$maxReAttrNum-$i;
			$sqlComm = "select /*Recommend-ft*/word_id,word_name from t_dolphin_attr_words where word_id not in ({$aStr}) and isuse=1 order by rand() limit {$randNum}";
			$result = array ();
			$result = DBRecommendHelper::getConn()->read($sqlComm, array ());
			foreach ($result as $value) {
				$attrInfo[] = $value;
			}
		}
		$reData[1] = $attrInfo;
		return $reData;
	}
	
	public function getPassGoods($num) {
		if ($num<1) {
			$num=120;
		}
		$sqlComm = "select /*Recommend-ft*/distinct goods_id from t_seal_pass_goods order by rand() limit {$num}";
		$result = array ();
		$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
		return $result;
	}
	
	public function getReGoodsByUid($uid) {
		$renum = 60;
		$aArr = array();
		$aArr = $this->getAttrByUserTwitter($uid,5);
		$type = 0;
		if (!isset($aArr[0])) {
			$type=100;
		}
		$aStr = implode(" , ", $aArr);
		$randNum = mt_rand(0,9);
		if ($type<1) {
			if ($randNum<=1) {
				$type=1;
			}
			elseif ($randNum<=4) {
				$type=2;
			}
			elseif ($randNum<=7) {
				$type=3;
			}
			elseif ($randNum<=8) {
				$type=4;
			}
			elseif ($randNum<=9) {
				$type=5;
			}
		}
		
		if ($type==1){
			//echo "		按照大类别推荐，比如喜欢手镯可能推荐配饰。这种推荐的宝贝候选集大，推荐变化多，更新鲜。<br>";
			$sqlComm = "select /*Recommend-ft*/count(*) num,label_id from t_dolphin_attr_words where word_id in ({$aStr}) and isuse=1 
			 and label_id != 10 and floor(label_id/100)!=10 and label_id != 11 and floor(label_id/100)!=11 and label_id != 12 and floor(label_id/100)!=12 
			  group by label_id";
			$result = array ();
			$result = DBRecommendHelper::getConn()->read($sqlComm, array ());
			
			$aArr = array();
			foreach ($result as $value) {
				$num = $value['num'];
				$lid = $value['label_id'];
				$sqlComm = "select /*Recommend-ft*/word_id attr_id from t_dolphin_attr_words where label_id={$lid} and isuse=1 order by rand() limit {$num}";
				$iresult = array ();
				$iresult = DBRecommendHelper::getConn()->read($sqlComm, array ());
				foreach ($iresult as $ivalue) {
					$aArr[] = $ivalue['attr_id'];
				}
			}
			/*
			if (!isset($aArr[0])) {
				return array();
			}
			*/
		}
		
		if ($type==2){
			//echo "		按照款式或小类别精准推荐，比如用户喜欢手镯就给她推荐手镯。这种推荐的宝贝候选集小，推荐变化不是很大，但精确。这种推荐不按照用户喜欢的风格、元素或材质，但可能根据用户喜欢的品牌、卡通元素等。<br>";
			$sqlComm = "select /*Recommend-ft*/word_id attr_id from t_dolphin_attr_words where word_id in ({$aStr}) and isuse=1 and label_id != 10 
			 and floor(label_id/100)!=10 and label_id != 11 and floor(label_id/100)!=11 and label_id != 12 and floor(label_id/100)!=12 
			  order by rand()";
			$result = array ();
			$result = DBRecommendHelper::getConn()->read($sqlComm, array ());
			$aNum = 15;
			$aArr = array();
			$i = 0;
			foreach ($result as $value) {
				if ($i>=$aNum) {
					break;
				}
				$aArr[$i] = $value['attr_id'];
				$i++;
			}
			/*
			if (!isset($aArr[0])) {
				return array();
			}
			*/
		}
		
		if ($type==3){
			//echo "		按照用户喜欢的属性推荐，可能包括款式、风格、元素等。这种推荐的宝贝候选集不算小，优点是能反映用户喜欢的风格和元素，缺点是可能推荐用户不感兴趣的款式，而用户只是喜欢该款式的元素。";
			$sqlComm = "select /*Recommend-ft*/word_id attr_id from t_dolphin_attr_words where word_id in ({$aStr}) and isuse=1 order by rand()";
			$result = array ();
			$result = DBRecommendHelper::getConn()->read($sqlComm, array ());
			$aNum = 15;
			$aArr = array();
			$i = 0;
			foreach ($result as $value) {
				if ($i>=$aNum) {
					break;
				}
				$aArr[$i] = $value['attr_id'];
				$i++;
			}
			/*
			if (!isset($aArr[0])) {
				return array();
			}
			*/
		}
		
		if ($type==4){
			//echo "		按照元素、风格推荐。这种推荐的宝贝候选集不算小，完全忽略款式，只推荐用户喜欢的风格、元素或卡通元素。<br>";
			$sqlComm = "select /*Recommend-ft*/word_id attr_id from t_dolphin_attr_words where word_id in ({$aStr}) and isuse=1 and (label_id = 10 
			 or floor(label_id/100)!=10 or label_id != 11 or floor(label_id/100)!=11 or label_id != 16 or floor(label_id/100)!=16 
			  or label_id != 12 or floor(label_id/100)!=12 )
			  order by rand()";
			$result = array ();
			$result = DBRecommendHelper::getConn()->read($sqlComm, array ());
			$aNum = 15;
			$aArr = array();
			$i = 0;
			foreach ($result as $value) {
				if ($i>=$aNum) {
					break;
				}
				$aArr[$i] = $value['attr_id'];
				$i++;
			}
			/*
			if (!isset($aArr[0])) {
				return array();
			}
			*/
		}
		
		if ($type==5){
			//echo "		按照与用户喜好的属性选杂志，取杂志的宝贝推荐。<br>";
			
			$aStr = implode(" , ", $aArr);
			
			/*
			$role = array(0,1,4,5);
			$userGroup = array();
			if ($uid>0){
				$userGroup = TopicGroupUserModel::getInstance()->getUserGroups($uid, $role);
			}
			$gArr = array();
			$gArr = DataToArray($userGroup, 'group_id');
			$gArr[] = 0;
			$gStr = implode(" , ", $gArr);
			*/
			/*
			$gArr = UserGroupFollower::lRange($uid, 0, 1500);
			if (!is_array($gArr)) {
				$gArr=array();
			}
			*/
			$gArr = array();
			$gArr[] = 0;
			$gStr = implode(" , ", $gArr);
			
			$groupInfo = array();
			$groupNum = 5;
			$sqlComm = "select /*Recommend-ft*/distinct group_id from t_seal_group_attr_top where attr_id in ({$aStr}) and rank<=6 and group_id not in ({$gStr}) order by rand() limit {$groupNum}";
			$result = array ();
			$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
			
			$i=0;
			$gArr = array();
			foreach ($result as $value) {
				$groupInfo[] = $value;
				$gArr[] = $value['group_id'];
				$i = $i+1;
			}
			
			if ($i<$groupNum) {
				$richNum=$groupNum-$i;
				$sqlComm = "select /*Recommend-ft*/group_id from t_seal_regroup where group_id not in ({$gStr}) order by rand() limit {$richNum}";
				$result = array ();
				$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
				foreach ($result as $value) {
					$groupInfo[] = $value;
					$gArr[] = $value['group_id'];
				}
			}
			$gStr = implode(" , ", $gArr);
			
			$sqlComm = "select /*Recommend-ft*/a.twitter_id tid from (select twitter_id from t_whale_topic_group_twitter 
			 where group_id in ({$gStr}) and twitter_show_type=7 order by twitter_id desc limit 500) a 
			  order by rand() limit {$renum}";
			$result = array ();
			$result = DBRecommendWhaleHelper::getConn()->read($sqlComm, array ());
			$tmpArr = array();
			foreach ($result as $value) {
				$tmpArr[] = $value['tid'];
			}
			$tmpStr = implode(" , ", $tmpArr);
			
			$sqlComm = "select /*Recommend-ft*/twitter_goods_id from t_twitter 
			 where twitter_id in ({$tmpStr}) and twitter_goods_id>0";
			$result = array ();
			$result = MlsStorageService::QueryRead($sqlComm);
			$tmpArr = array();
			foreach ($result as $value) {
				$tmpArr[] = $value['twitter_goods_id'];
			}
			$tmpStr = implode(" , ", $tmpArr);
			
			$sqlComm = "select /*Recommend-ft*/distinct goods_id from t_dolphin_goods_verify 
			 where goods_id in ({$tmpStr}) and verify_stat!=2";
			$result = array ();
			$result = DBRecommendHelper::getConn()->read($sqlComm, array ());
			$res = array();
			$goods_map = array();
			foreach ($result as $value) {
				$goods_map[$value['goods_id']] = 1;
			}
			if (count($goods_map)<$renum) {
				$result = $this->getPassGoods($renum);
				foreach ($result as $value) {
					$goods_map[$value['goods_id']] = 1;
				}
			}
			$i=0;
			foreach ($goods_map as $gid=>$value){
				$res[]=$gid;
				$i++;
				if ($i>=$renum) {
					break;
				}
			}
			
			return $res;
		}
		
		
		$res = array ();
		$limitNum = 6;
		/*$sqlComm = "select goods_id from t_goods_attr_top_re
			 where attr_id in ({$aStr}) and rank=1 order by rand() limit {$limitNum}";*/
		$goods_map = array();
		foreach ($aArr as $aid) {
			$sqlComm = "select /*Recommend-ft*/distinct goods_id from t_seal_attr_regoods_top where attr_id = {$aid} order by rand() limit {$limitNum}";
			$result = array ();
			$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
			if (!isset($result[0])) {
				continue;
			}
			foreach ($result as $value) {
				$goods_map[$value['goods_id']] = 1;
			}
		}
		if (count($goods_map)<$renum) {
			$result = $this->getPassGoods($renum);
			foreach ($result as $value) {
				$goods_map[$value['goods_id']] = 1;
			}
		}
		
		$i=0;
		foreach ($goods_map as $gid=>$value){
			$res[]=$gid;
			$i++;
			if ($i>=$renum) {
				break;
			}
		}
		return $res;
	}
	
	public function getReGoodsByGid($gid,$attrMapArr,$renum=7) {
		/*
		 * AB测试控制
		 */
		/*
		global $GLOBAL_COOKIE_STRING;
		$sessid_len = strlen($GLOBAL_COOKIE_STRING);
		$lastc = "";
		if ($sessid_len>0){
			$lastc = $GLOBAL_COOKIE_STRING[$sessid_len-1];
		}
		$abNum=0;
		if (empty($GLOBAL_COOKIE_STRING) || ($sessid_len<=0) || ($lastc<"0" or $lastc>"9") ){
			$abNum = mt_rand(0,9);
		}
		else {
			$abNum = $lastc%10;
		}
		*/
		$abNum = 1;
		
		$aStrMaxLen = 10;
		$goods_map = array();
		//$renum = 7;
		$sqlComm = "select /*Recommend-ft*/attr_id from t_seal_goods_attr_top where goods_id={$gid} and tag=0 
		 order by rank asc limit 10";
		$attrRes = array ();
		$attrRes = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
		if (!isset($attrRes[0])) {
			$tmplen=$aStrMaxLen;
			if (count($attrMapArr)<$aStrMaxLen) {
				$tmplen = count($attrMapArr);
			}
			if ($tmplen>0) {
				$attrRes = array_slice($attrMapArr,0,$tmplen);
			}
			if (!isset($attrMapArr[0])) {
				$result = $this->getPassGoods($renum);
				foreach ($result as $value) {
					$goods_map[$value['goods_id']] = 1;
				}
			}
		}
		
		if ($abNum==0){
			$aArr = array(-1);
			$i=0;
			foreach ($attrRes as $value) {
				if ($i>=$aStrMaxLen) {
					break;
				}
				$aid = $value['attr_id'];
				$aArr[] = $aid;
				$i++;
			}
			$aStr = implode(" , ", $aArr);
			
			$sqlComm = "select /*Recommend-ft*/word_id attr_id,word_name,label_id from t_dolphin_attr_words where word_id in ({$aStr}) and isuse=1";
			$result = array ();
			$result = DBRecommendHelper::getConn()->read($sqlComm, array ());
			
			$attrRes = array();
			
			foreach ($result as $value) {
				if (in_array($value['label_id'],array(13,14,17,20)) || in_array(floor($value['label_id']/100),array(13,14,17,20))) {
					$attrRes[] = $value;
				}
			}
		}
		if (isset($attrRes[0])) {
			$aArr = array(-1);
			$i=0;
			foreach ($attrRes as $value) {
				if ($i>=$aStrMaxLen) {
					break;
				}
				$aid = $value['attr_id'];
				$aArr[] = $aid;
				$i++;
			}
			$aStr = implode(" , ", $aArr);
			
			
			$sqlComm = "select /*Recommend-ft*/word_name,word_id,label_id from t_dolphin_attr_words where word_id in ({$aStr}) and isuse=1";
			$tmpRes = array ();
			$tmpRes = DBRecommendHelper::getConn()->read($sqlComm, array ());
			if (!isset($tmpRes[0])) {
				$result = $this->getPassGoods($renum);
				foreach ($result as $value) {
					$goods_map[$value['goods_id']] = 1;
				}
			}
			else {
				$amap = array();
				$aname_map = array();
				foreach ($tmpRes as $value) {
					$amap[$value['word_id']] = $value['label_id'];
					$aname_map[$value['word_id']] = $value['word_name'];
				}
				
				$specialArr = array();//特殊属性，优先展示
				$sqlComm = "select /*Recommend-ft*/attr_id from t_seal_special_attr where attr_id in ({$aStr})";
				$tmpRes = array ();
				$tmpRes = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
				foreach ($tmpRes as $value) {
					$specialArr[] = $value['attr_id'];
				}
				foreach ($specialArr as $aid1) {
					if (count($goods_map)<$renum) {
						$sqlComm = "select /*Recommend-ft*/goods_id from t_seal_attr_regoods_top where attr_id = {$aid1} order by rand() limit {$renum}";
						$result = array ();
						$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
						if (!isset($result[0])) {
							continue;
						}
						foreach ($result as $value) {
							$goods_map[$value['goods_id']] = 1;
							if (count($goods_map)>=$renum) {
								break;
							}
						}
						
					}
				}
				
				
				if (count($goods_map)<$renum) {
					$tag=1;
					foreach ($attrRes as $value) {
						$aid = $value['attr_id'];
						$lid = $amap[$aid];
						if (in_array($lid,array(14,20)) || in_array(floor($lid/100),array(14,20))) {
							break;
						}
						if (in_array($lid,array(13,17)) || in_array(floor($lid/100),array(13,17))) {
							$tag=2;
							break;
						}
					}
					$ksArr = array();//包括服装、零食等
					$ktArr = array();//卡通元素
					$ysArr = array();//普通元素（除颜色元素）
					$yanseArr = array();//颜色元素
					$fgArr = array();//风格
					$ppArr = array();//品牌
					$czArr = array();//材质
					$otherArr = array();//风格、材质、品牌、店铺等属性
					
					
					$ksArr2 = array();//包括家居、美容	
					$ppArr2 = array();//品牌	
					
					if ($tag==1){
						foreach ($attrRes as $value) {
							$aid = $value['attr_id'];
							$lid = $amap[$aid];
							if (in_array($lid,array(14,20)) || in_array(floor($lid/100),array(14,20))) {
								$ksArr[]=$aid;
							}
							elseif (in_array($lid,array(16)) || in_array(floor($lid/100),array(16))) {
								$ktArr[]=$aid;
							}
							elseif (in_array($lid,array(11)) || ($lid!=1111 && in_array(floor($lid/100),array(11))) ) {
								$ysArr[]=$aid;
							}
							elseif (in_array($lid,array(1111))) {
								$yanseArr[]=$aid;
							}
							elseif (in_array($lid,array(10)) || in_array(floor($lid/100),array(10))) {
								$fgArr[]=$aid;
							}
							elseif (in_array($lid,array(15)) || in_array(floor($lid/100),array(15))) {
								$ppArr[]=$aid;
							}
							elseif (in_array($lid,array(12)) || in_array(floor($lid/100),array(12))) {
								$czArr[]=$aid;
							}
							else {
								$otherArr[]=$aid;
							}
						}
						
						foreach ($ksArr as $aid1) {
							if (count($goods_map)>=$renum) {
								break;
							}
							foreach ($ktArr as $aid2) {
								if (strpos($aname_map[$aid1],$aname_map[$aid2])!==false) {
									continue;
								}
								if (count($goods_map)>=$renum) {
									break;
								}
								$sqlComm = "select /*Recommend-ft*/goods_id from t_seal_attr_attr_regoods_top where (attr_id,attr_id2) = ({$aid1},{$aid2}) order by rand() limit {$renum}";
								//$sqlComm = "select /*Recommend-ft*/distinct goods_id from t_seal_attr_regoods_top where attr_id = {$aid} order by rand() limit {$limitNum}";
								$result = array ();
								$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
								if (!isset($result[0])) {
									continue;
								}
								foreach ($result as $value) {
									$goods_map[$value['goods_id']] = 1;
									if (count($goods_map)>=$renum) {
										break;
									}
								}
							}
							foreach ($ysArr as $aid2) {
								if (strpos($aname_map[$aid1],$aname_map[$aid2])!==false) {
									continue;
								}
								if (count($goods_map)>=$renum) {
									break;
								}
								$sqlComm = "select /*Recommend-ft*/goods_id from t_seal_attr_attr_regoods_top where (attr_id,attr_id2) = ({$aid1},{$aid2}) order by rand() limit {$renum}";
								//$sqlComm = "select /*Recommend-ft*/distinct goods_id from t_seal_attr_regoods_top where attr_id = {$aid} order by rand() limit {$limitNum}";
								$result = array ();
								$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
								if (!isset($result[0])) {
									continue;
								}
								foreach ($result as $value) {
									$goods_map[$value['goods_id']] = 1;
									if (count($goods_map)>=$renum) {
										break;
									}
								}
							}
							foreach ($yanseArr as $aid2) {
								if (strpos($aname_map[$aid1],$aname_map[$aid2])!==false) {
									continue;
								}
								if (count($goods_map)>=$renum) {
									break;
								}
								$sqlComm = "select /*Recommend-ft*/goods_id from t_seal_attr_attr_regoods_top where (attr_id,attr_id2) = ({$aid1},{$aid2}) order by rand() limit {$renum}";
								//$sqlComm = "select /*Recommend-ft*/distinct goods_id from t_seal_attr_regoods_top where attr_id = {$aid} order by rand() limit {$limitNum}";
								$result = array ();
								$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
								if (!isset($result[0])) {
									continue;
								}
								foreach ($result as $value) {
									$goods_map[$value['goods_id']] = 1;
									if (count($goods_map)>=$renum) {
										break;
									}
								}
							}
							foreach ($fgArr as $aid2) {
								if (strpos($aname_map[$aid1],$aname_map[$aid2])!==false) {
									continue;
								}
								if (count($goods_map)>=$renum) {
									break;
								}
								$sqlComm = "select /*Recommend-ft*/goods_id from t_seal_attr_attr_regoods_top where (attr_id,attr_id2) = ({$aid1},{$aid2}) order by rand() limit {$renum}";
								//$sqlComm = "select /*Recommend-ft*/distinct goods_id from t_seal_attr_regoods_top where attr_id = {$aid} order by rand() limit {$limitNum}";
								$result = array ();
								$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
								if (!isset($result[0])) {
									continue;
								}
								foreach ($result as $value) {
									$goods_map[$value['goods_id']] = 1;
									if (count($goods_map)>=$renum) {
										break;
									}
								}
							}
							foreach ($ppArr as $aid2) {
								if (strpos($aname_map[$aid1],$aname_map[$aid2])!==false) {
									continue;
								}
								if (count($goods_map)>=$renum) {
									break;
								}
								$sqlComm = "select /*Recommend-ft*/goods_id from t_seal_attr_attr_regoods_top where (attr_id,attr_id2) = ({$aid1},{$aid2}) order by rand() limit {$renum}";
								//$sqlComm = "select /*Recommend-ft*/distinct goods_id from t_seal_attr_regoods_top where attr_id = {$aid} order by rand() limit {$limitNum}";
								$result = array ();
								$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
								if (!isset($result[0])) {
									continue;
								}
								foreach ($result as $value) {
									$goods_map[$value['goods_id']] = 1;
									if (count($goods_map)>=$renum) {
										break;
									}
								}
							}
							foreach ($czArr as $aid2) {
								if (strpos($aname_map[$aid1],$aname_map[$aid2])!==false) {
									continue;
								}
								if (count($goods_map)>=$renum) {
									break;
								}
								$sqlComm = "select /*Recommend-ft*/goods_id from t_seal_attr_attr_regoods_top where (attr_id,attr_id2) = ({$aid1},{$aid2}) order by rand() limit {$renum}";
								//$sqlComm = "select /*Recommend-ft*/distinct goods_id from t_seal_attr_regoods_top where attr_id = {$aid} order by rand() limit {$limitNum}";
								$result = array ();
								$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
								if (!isset($result[0])) {
									continue;
								}
								foreach ($result as $value) {
									$goods_map[$value['goods_id']] = 1;
									if (count($goods_map)>=$renum) {
										break;
									}
								}
							}
							foreach ($otherArr as $aid2) {
								if (strpos($aname_map[$aid1],$aname_map[$aid2])!==false) {
									continue;
								}
								if (count($goods_map)>=$renum) {
									break;
								}
								$sqlComm = "select /*Recommend-ft*/goods_id from t_seal_attr_attr_regoods_top where (attr_id,attr_id2) = ({$aid1},{$aid2}) order by rand() limit {$renum}";
								//$sqlComm = "select /*Recommend-ft*/distinct goods_id from t_seal_attr_regoods_top where attr_id = {$aid} order by rand() limit {$limitNum}";
								$result = array ();
								$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
								if (!isset($result[0])) {
									continue;
								}
								foreach ($result as $value) {
									$goods_map[$value['goods_id']] = 1;
									if (count($goods_map)>=$renum) {
										break;
									}
								}
							}
							
							if (count($goods_map)<$renum) {
								$sqlComm = "select /*Recommend-ft*/goods_id from t_seal_attr_regoods_top where attr_id = {$aid1} order by rand() limit {$renum}";
								//$sqlComm = "select /*Recommend-ft*/distinct goods_id from t_seal_attr_regoods_top where attr_id = {$aid} order by rand() limit {$limitNum}";
								$result = array ();
								$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
								if (!isset($result[0])) {
									continue;
								}
								foreach ($result as $value) {
									$goods_map[$value['goods_id']] = 1;
									if (count($goods_map)>=$renum) {
										break;
									}
								}
							}
							
						}
					}
					
					if ($tag==2){
						foreach ($attrRes as $value) {
							$aid = $value['attr_id'];
							$lid = $amap[$aid];
							if (in_array($lid,array(13,17)) || in_array(floor($lid/100),array(13,17))) {
								$ksArr2[]=$aid;
							}
							elseif (in_array($lid,array(15)) || in_array(floor($lid/100),array(15))) {
								$ppArr2[]=$aid;
							}
							else {
								$otherArr[]=$aid;
							}
						}
						
						foreach ($ksArr2 as $aid1) {
							if (count($goods_map)>=$renum) {
								break;
							}
							foreach ($ppArr2 as $aid2) {
								if (strpos($aname_map[$aid1],$aname_map[$aid2])!==false) {
									continue;
								}
								if (count($goods_map)>=$renum) {
									break;
								}
								$sqlComm = "select /*Recommend-ft*/goods_id from t_seal_attr_attr_regoods_top where (attr_id,attr_id2) = ({$aid1},{$aid2}) order by rand() limit {$renum}";
								//$sqlComm = "select /*Recommend-ft*/distinct goods_id from t_seal_attr_regoods_top where attr_id = {$aid} order by rand() limit {$limitNum}";
								$result = array ();
								$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
								if (!isset($result[0])) {
									continue;
								}
								foreach ($result as $value) {
									$goods_map[$value['goods_id']] = 1;
									if (count($goods_map)>=$renum) {
										break;
									}
								}
							}
							foreach ($otherArr as $aid2) {
								if (strpos($aname_map[$aid1],$aname_map[$aid2])!==false) {
									continue;
								}
								if (count($goods_map)>=$renum) {
									break;
								}
								$sqlComm = "select /*Recommend-ft*/goods_id from t_seal_attr_attr_regoods_top where (attr_id,attr_id2) = ({$aid1},{$aid2}) order by rand() limit {$renum}";
								//$sqlComm = "select /*Recommend-ft*/distinct goods_id from t_seal_attr_regoods_top where attr_id = {$aid} order by rand() limit {$limitNum}";
								$result = array ();
								$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
								if (!isset($result[0])) {
									continue;
								}
								foreach ($result as $value) {
									$goods_map[$value['goods_id']] = 1;
									if (count($goods_map)>=$renum) {
										break;
									}
								}
							}
							
							if (count($goods_map)<$renum) {
								$sqlComm = "select /*Recommend-ft*/goods_id from t_seal_attr_regoods_top where attr_id = {$aid1} order by rand() limit {$renum}";
								//$sqlComm = "select /*Recommend-ft*/distinct goods_id from t_seal_attr_regoods_top where attr_id = {$aid} order by rand() limit {$limitNum}";
								$result = array ();
								$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
								if (!isset($result[0])) {
									continue;
								}
								foreach ($result as $value) {
									$goods_map[$value['goods_id']] = 1;
									if (count($goods_map)>=$renum) {
										break;
									}
								}
								
							}
							
						}
						foreach ($ksArr2 as $aid) {
							if ($abNum==0 || count($goods_map)>=$renum) {
								break;
							}
							$lid = $amap[$aid];
							
							$sqlComm = "select /*Recommend-ft*/word_id attr_id from t_dolphin_attr_words where label_id={$lid} and isuse=1 order by rand() limit 3";
							$sameLabelAttrRes = array ();
							$sameLabelAttrRes = DBRecommendHelper::getConn()->read($sqlComm, array ());
							foreach ($sameLabelAttrRes as $value) {
								if (count($goods_map)>=$renum) {
									break;
								}
								$aid1 = $value['attr_id'];
								$sqlComm = "select /*Recommend-ft*/goods_id from t_seal_attr_regoods_top where attr_id = {$aid1} order by rand() limit {$renum}";
								//$sqlComm = "select /*Recommend-ft*/distinct goods_id from t_seal_attr_regoods_top where attr_id = {$aid} order by rand() limit {$limitNum}";
								$result = array ();
								$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
								if (!isset($result[0])) {
									continue;
								}
								foreach ($result as $value) {
									$goods_map[$value['goods_id']] = 1;
									if (count($goods_map)>=$renum) {
										break;
									}
								}
							}
						}
						foreach ($ppArr2 as $aid) {
							if (count($goods_map)>=$renum) {
								break;
							}
							
							$sqlComm = "select /*Recommend-ft*/goods_id from t_seal_attr_regoods_top where attr_id = {$aid} order by rand() limit {$renum}";
							$result = array ();
							$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
							if (!isset($result[0])) {
								continue;
							}
							foreach ($result as $value) {
								$goods_map[$value['goods_id']] = 1;
								if (count($goods_map)>=$renum) {
									break;
								}
							}

						}
						foreach ($ppArr2 as $aid) {
							if (count($goods_map)>=$renum) {
								break;
							}
							$lid = $amap[$aid];
							
							$sqlComm = "select /*Recommend-ft*/word_id attr_id from t_dolphin_attr_words where label_id={$lid} and isuse=1 order by rand() limit 3";
							$sameLabelAttrRes = array ();
							$sameLabelAttrRes = DBRecommendHelper::getConn()->read($sqlComm, array ());
							foreach ($sameLabelAttrRes as $value) {
								if (count($goods_map)>=$renum) {
									break;
								}
								$aid1 = $value['attr_id'];
								$sqlComm = "select /*Recommend-ft*/goods_id from t_seal_attr_regoods_top where attr_id = {$aid1} order by rand() limit {$renum}";
								//$sqlComm = "select /*Recommend-ft*/distinct goods_id from t_seal_attr_regoods_top where attr_id = {$aid} order by rand() limit {$limitNum}";
								$result = array ();
								$result = DBRecommendSealHelper::getConn()->read($sqlComm, array ());
								if (!isset($result[0])) {
									continue;
								}
								foreach ($result as $value) {
									$goods_map[$value['goods_id']] = 1;
									if (count($goods_map)>=$renum) {
										break;
									}
								}
							}
						}
					}
					
				}
			}
		}
		
		if (count($goods_map)<$renum) {
			$result = $this->getPassGoods($renum-count($goods_map));
			foreach ($result as $value) {
				$goods_map[$value['goods_id']] = 1;
			}
		}
		
		$res = array ();
		$i=0;
		foreach ($goods_map as $gidkey=>$value) {
			if ($gid!=$gidkey && $i<$renum-1) {
				$res[] = $gidkey;
				$i++;
			}
		}
		
		return $res;
	}

	public function groupIdOutOfDANPING ($groupId) {
		$sql = "select group_id from t_dolphin_group_class_map where class_id not in (32) and group_id = {$groupId} and isuse = 1";
		$result = DBRecommendHelper::getConn()->read($sql, array ());
		return $result;
	}
	//判断一队杂志设中的优质杂志
	public function getExelentGroup($gids, $master = false, $hashkey = '') {
		if (empty($gids)) return false; 
		$gids = implode(',', $gids);
		$sql = "select group_id from t_dolphin_group_class_map where class_id not in (32) and group_id in ($gids) and isuse = 1";
		$result = DBRecommendHelper::getConn()->read($sql, array (), $master, $hashkey);
		return $result;
	}
	//删除某top杂志对应的推荐
	public function deleteTopAttrByGid($groupId) {
		$sql = "delete from t_seal_group_attr_top where group_id =:_group_id";
		$sqlData['_group_id'] = $groupId;
		$result = DBRecommendSealHelper::getConn()->write($sql, $sqlData);
		return $result;
	}
	//删除某杂志对应的推荐
	public function deleteAttrByGid($groupId) {
		$sql = "delete from t_seal_group_name_attr where group_id =:_group_id";
		$sqlData['_group_id'] = $groupId;
		$result = DBRecommendSealHelper::getConn()->write($sql, $sqlData);
		return $result;
	}
    
}
