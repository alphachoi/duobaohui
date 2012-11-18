<?php
namespace Snake\Package\Edm;
use Snake\Package\User\Helper\DBUserHelper;
use Snake\Package\Edm\Helper\DBEdmHelper;
class  GetEdmUser {
	private $table = 't_dolphin_user_profile';
	private $gtable = 't_whale_mail_ignore';
	
	/**
	 * 获得前一天的注册的用户
	*/
	public function getUserOneDay() {
		//获得当前最大的用户id
		$sqlComm = "select max(user_id) as user_id  from $this->table ";
		$sqlData = array();
		$user_id = DBUserHelper::getConn()->read($sqlComm, $sqlData);
		$max_id = $user_id[0]['user_id'];
		//在最新的100万的用户里查找
		$max_id = $max_id - 500000;
		$end = time();
		$start = $end - 24*3600;
		$sqlComm = "select user_id,nickname,email from $this->table where user_id > $max_id  and unix_timestamp(ctime) >= $start and unix_timestamp(ctime) <=$end";
		$info =  DBUserHelper::getConn()->read($sqlComm, $sqlData);
		return $info;
	}
	/**
	 *获取取消订阅的用户
	 */
	 
	public function isIgnore($email, $type, $colum = 'email', $master = false, $hashkey="") {
		
		$sqlComm = "select $colum from $this->gtable where email=:email and type=:type";
		$sqlData = array('email' => $email,
						 'type'  => $type
		);
		$result = DBEdmHelper::getConn()->read($sqlComm, $sqlData);
		return $result;
	}
	/**
	 *获得所有的取消订阅的用户
	 */
	public function getAllIgnore($type, $colum = 'email', $master = false, $hashkey="") {
		$sqlComm = "select $colum from $this->gtable where type=:type";
		$sqlData['type'] = $type;
		$result = DBEdmHelper::getConn()->read($sqlComm, $sqlData, $master, $hashkey);
		return $result;
	}
	
	
}
