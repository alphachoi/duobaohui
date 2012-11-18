<?php
namespace Snake\Package\Msg;
use \Snake\Package\User\User;
use \Snake\Package\Msg\Helper\DBMsgHelper;
use \Snake\Package\Msg\Helper\DBLikeHelper;
use \Snake\Package\Msg\Msg;
use \Snake\Package\User\UserStatistic;
use \Snake\Package\Msg\Helper\RedisUserNotification;
use \Snake\Libs\DB\MakeSql;

class Alert  implements \Snake\Libs\Interfaces\Iobserver{
	public function __construct (){

	}
	public function onChanged($sender, $params) {
		switch($sender) {
			case 'Follow':
				$this->setNumByParamAndUid($params['alert'],$params['other_id']);
				break;
			default :
				break;
		}
	}
	//所有的读操作
	public function getData($param, $sqlData, $master, $hashkey) {
		$sqlHelper = new MakeSql();
		$sql = $sqlHelper->MakeSqlType('select', $param);
		$result = DBMsgHelper::getConn()->read($sql, $sqlData, $master, $hashKey);
		return $result;
	}
	//dolphin库写操作
	public function writeData($param, $sqlData, $type) {
		$sqlHelper = new MakeSql();
		$sql = $sqlHelper->MakeSqlType($type, $param);
		$result = DBMsgHelper::getConn()->write($sql, $sqlData);
		return $result;
	}
	//whale写操作
	public function writeDataInWhale($param, $sqlData, $type) {
		$sqlHelper = new MakeSql();
		$sql = $sqlHelper->MakeSqlType($type, $param);
		$result = DBLikeHelper::getConn()->write($sql, $sqlData);
		return $result;
	}


	/**添加提醒
	 * $paramStr string fan_num,atme_num,pmsg_num,recommend_num
	 * 需要添加多个提醒时用,分格
	 */

    public function setNumByParamAndUid($paramStr, $userId) {
        if (empty($userId)) {
            return FALSE;
        }   
        if (!RedisUserNotification::exists($userId)) {
			$msgHelper = new Msg();	
			$msgHelper->insertRowByUid($userId);
        }   
		$strArr = explode(',', $paramStr);
		foreach ($strArr  as $str) { 
			RedisUserNotification::incr($userId, $str);
		}
        return TRUE;

    } 

	/**
	 *再数据库中添加@我
	 */
	public function createNewAlert( $uid , $tid , $sourceTwitterUid = 0 ){
		$data['alert_uid'] = $uid;
		$data['twitter_id'] = $tid;
		$data['alert_time'] = time();
		$param = array('table' => 't_dolphin_twitter_alert',
					   'insert' => array('alert_uid' => ':alert_uid','twitter_id' => ':twitter_id', 'alert_time' => ':alert_time')
		);
		$res = $this->writeData($param, $data, 'insert');
		if( $res==false ){
			return false;
		} else {
			return true;
		}
	}
	/** 
	 * 获得@我的tid
	 * param $userid
	 *
	 */
	public function getAtMeTids($uid, $start = 0, $limit = 20, $colum = 'twitter_id', $master = false, $hashkey = "") {
		$param = array('table' => 't_dolphin_twitter_alert',
					   'colum' => $colum,
					   'where' => array('alert_show' =>1, 'alert_uid' => ':uid'),
					   'order' => 'twitter_id desc',
					   'limit' => "$start, $limit"
		);
		$sqlData['uid'] = $uid;
		$result = $this->getData($param, $sqlData, $master, $hashkey);
		return $result;
		 
	}
	/**
	 *再数据库中添加小红心
	 */
	public function createNewLike( $to_uid ,$from_uid, $tid ){
		$time = time();
		$data = array('user_id' => $to_uid,
					  'author_id' => $from_uid,
					  'twitter_id' => $tid,
					  'update_time' => $time
		);
		$param = array('table' => 't_whale_twitter_recommend',
					   'insert' => array('twitter_id' => ':twitter_id', 'user_id' => ':user_id', 'author_id' => ':author_id', 'update_time' => ':update_time')
		);
		$res = $this->writeDataInWhale($param, $data, 'insert');
		if( $res==false ){
			return false;
		} else {
			return true;
		}
	}
	/**
	/**
	 * 添加小红心提醒
	 *
	 */
	public function like($to_uid, $from_uid, $tid) {
		if (empty($to_uid)||empty($from_uid)||empty($tid)) {
			return false;	
		}	 	 
		$ret = $this->createNewLike($to_uid, $from_uid, $tid);
		$this->setNumByParamAndUid('recommend_num', $to_uid);
		UserStatistic::getInstance()->setStaticNumber($to_uid, 'recommend_num', 'recommend_num + 1');
		return $ret;
    }
    /**
	 *添加@提醒
     *param data @人的数组
	 *param tid 推id
	 *return true
	 */
	public function at($data, $tid) {
		$ret = false;
		if (is_array($data)) {
			foreach ($data as $u) {
				$res = $this->createNewAlert($u, $tid);
				$this->setNumByParamAndUid ('atme_num', $u);
				UserStatistic::getInstance()->setStaticNumber($u, 'alert_num', 'alert_num + 1');
			}
		}
		return $ret;
	}
	/**
	 * hack @
	 * param stringContent 一段文字
	 * return 处理好的文字和@的用户 或者为false
	 */
	public function hackAt($stringContent) {
		$retString = "";
		$stringContent = str_replace ( '＠', '@', $stringContent );

		$checkStatus = strpos ( $stringContent, '@' );
		 $stringContent = str_replace("：",":", $stringContent);
		if ($checkStatus >= 0) {
			$pattern = '/@[^\s:]+/si';
			$atNum = preg_match_all ( $pattern, $stringContent, $matches );
			if ($atNum > 0) {
				list($content,$atRes) = $this->_handingAt ( $matches, $stringContent );
				return array($content,$atRes);
			} else {
				return array($stringContent, array());
			}
		} else {
			return array($stringContent, array());
		}
	}
		
	public function _handingAt($matches, $stringContent) {
		$atArr = '';
		$trans = array ();
		$userHelper = new User();
		foreach ( $matches [0] as $key => $nickname ) {
			//处理冒号
			if (preg_match('/[\x80-\xff]./', $nickname) == 0 && preg_match('/([u4e00-u9fa5])/', $nickname) == 0) {
				$nicknames = rtrim ( ltrim ( $nickname, '@' ), ':' ); //同时支持全角和半角的冒号
			} else {
				$nicknames = ltrim ( $nickname, '@' );
			}
			$nicknameExt = '';
			//如果之前有冒号，现在补上
			//测试nickname中是否有中文字符
			//结果大于0，表明有中文字符
			//print_r(preg_match('/[\x80-\xff]./', $nickname));
			//print_r(preg_match('/([u4e00-u9fa5])/', $nickname));
			if (preg_match('/[\x80-\xff]./', $nickname) == 0 && preg_match('/([u4e00-u9fa5])/', $nickname) == 0) {
				if (strlen ($nickname) > strlen ($nicknames) + 1) {
					$nicknameExt = "：";
				}
				else {
					//处理空格
					$nicknames = rtrim ( $nicknames );
					if (strlen ( $nickname ) > strlen ( $nicknames ) + 1) {
						$nicknameExt = " ";
					}
				}
			}
			else {
				if (mb_strlen ( $nickname ) > mb_strlen ( $nicknames ) + 1) {
					$nicknameExt = "：";
				}
				else {
					//处理空格
					$nicknames = rtrim ( $nicknames );
					if (mb_strlen ( $nickname ) > mb_strlen ( $nicknames ) + 1) {
						$nicknameExt = " ";
					}
				}
			}
			$nicknames = htmlspecialchars_decode($nicknames);
			$param = array("nickname" => $nicknames);
			$userInfo = $userHelper->getUserProfile ($param, "user_id");
			if (empty ( $userInfo )) {
				continue;
			} else {
				$uid = $userInfo [0] ['user_id'];
				$url = "<a href='/person/u/{$uid}' class='a1 nk' target='_blank'>@{$nicknames}</a>{$nicknameExt}";
				$trans [$nickname] = $url;
				$atArr [$key] = $uid;
			}
		}
		$stringContent = strtr ( $stringContent, $trans );
		return array($stringContent,$atArr);
	}
		
		
		
		
		
		
}
