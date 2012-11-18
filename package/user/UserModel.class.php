<?php
namespace Snake\Package\User;

use \Snake\Package\User\Helper\DBUserHelper;

class UserModel {

    public function __construct() {
	}

    private function permission($col) {
		if ("*" == $col) {
			die('please do not select *');
		}
    }
	/*
	这个函数很强大，只有你想不到的,没有它做不到的。如果需要请在此扩展
	*/
    function getUserProfile($param = array(), $col = "*" , $getFromMaster = FALSE) {
		$this->permission($col);
    	$sqlData = array();
		$sqlComm = "SELECT $col FROM t_dolphin_user_profile WHERE 1 ";
		if (isset($param['user_id'])) {
			if (is_array($param['user_id'])) {
				$uids = implode(",", $param['user_id']);
				$sqlComm .= "AND user_id in($uids)"; 	
			}
			else {
				$sqlComm .= "AND user_id=:_user_id";
				$sqlData['_user_id'] = $param['user_id'];
			}
		}
		if (isset($param['active_code'])) {
			$sqlComm .= " AND active_code=:active_code";
			$sqlData['active_code'] = $param['active_code'];
		}
		if (isset($param['nickname'])) {
			$sqlComm .= " AND nickname=:nickname";
			$sqlData['nickname'] = $param['nickname'];
		}
   		if (isset($param['email'])) {
			$sqlComm .= " AND email=:email";
			$sqlData['email'] = $param['email'];
		}
		$result = DBUserHelper::getConn()->read($sqlComm, $sqlData, $getFromMaster);
		return $result;
    }
}
