<?php

namespace Snake\Package\Qzone;

use \Snake\Package\Qzone\Helper\DBQzoneActivityHelper;

class DBQzoneActivity {

	public function __construct() {

	}

	public function getAllActivities(
								$start = 0, 
								$limit = 5, 
								//$select = '/*qzone-lhz*/activity_id, title, activity_type, summary, products_introduction, products_img, products_imglink, products_preview_img, sortno, products_price, organizer, index_banner, activity_banner, begin_time, end_time, top_banner, trynumber, activity_url, valid') {
								$select = '/*qzone-lhz*/activity_id, title, activity_type, sortno, products_price, organizer, activity_banner, begin_time, end_time, trynumber,  activity_url, valid',
								$valid = 1) {

		if ($select == '*') {
			return FALSE;
		}
		$time = time();
		if ($valid == 1) {
			$where = "valid = 1";
			$str = "begin_time < " . $time;
			$orderBy = "end_time DESC, begin_time DESC, activity_id DESC";
		}
		else {
			$where = "(valid = 1 OR valid = 2)";
			$str = "begin_time > " . $time;
			$orderBy = "sortno DESC";
		}
		$sql = "SELECT {$select} FROM t_dolphin_qzone_activity_info WHERE {$where} AND {$str} ORDER BY {$orderBy} LIMIT {$start}, {$limit}";
		$result = array();
		$sqlData = array();
		$result = DBQzoneActivityHelper::getConn()->read($sql, $sqlData);
		return $result;
	}

	public function getActivitiesNumber() {
		$time = time();
		$sql = "SELECT count(activity_id) AS count FROM t_dolphin_qzone_activity_info WHERE valid = 1 AND begin_time < {$time}";
		$result = array();
		$sqlData = array();
		$result = DBQzoneActivityHelper::getConn()->read($sql, $sqlData);
		return $result;
	}

	public function getApplyUsers(
								$activityId, 
								$status = array(0),
								$start = 0,
								$limit = 5,
								//$select = "/*qzone-lhz*/id, activity_id, real_name, reason, img_url") {
								$select = "/*qzone-lhz*/id, img_url") {
		$status = implode(',', $status);
		$sql = "SELECT {$select} FROM t_dolphin_qzone_activity_apply WHERE activity_id = {$activityId} AND status in ({$status}) ORDER BY ctime DESC LIMIT {$start}, {$limit}";
		$result = array();
		$sqlData = array();
		$result = DBQzoneActivityHelper::getConn()->read($sql, $sqlData);
		return $result;
	}

	public function getCarouselUsers(
									$limit = 20, 
									$status = 1,
									$select = "/*qzone-lhz*/id, activity_id, real_name, ctime, reason, img_url") {

		$sql = "SELECT {$select} FROM t_dolphin_qzone_activity_apply WHERE status = {$status} ORDER BY ctime DESC LIMIT {$limit}";
		$result = array();
		$sqlData = array();
		$result = DBQzoneActivityHelper::getConn()->read($sql, $sqlData);
		return $result;
	}

	public function getActivities(
								$activityIds,
								$select = "/*qzone-lhz*/activity_id, title, products_price") {

		if (empty($activityIds)) {
			return array();
		}
		$activityIds = implode(',', $activityIds);
		$sql = "SELECT {$select} FROM t_dolphin_qzone_activity_info WHERE activity_id IN ({$activityIds})";
		$result = array();
		$sqlData = array();
		$result = DBQzoneActivityHelper::getConn()->read($sql, $sqlData, FALSE, 'activity_id');
		return $result;
	}

	public function getApplyUsersNums($activityIds) {
		if (empty($activityIds)) {
			return array();
		}
		$activityIds = implode(',', $activityIds);
		$sql = "SELECT count(id) AS count, activity_id FROM t_dolphin_qzone_activity_apply WHERE activity_id in ({$activityIds}) GROUP BY activity_id";
		$result = array();
		$sqlData = array();
		$result = DBQzoneActivityHelper::getConn()->read($sql, $sqlData, FALSE, 'activity_id');
		return $result;
		
	}
	
	public function test() {
		$sql = "SELECT * FROM t_dolphin_qzone_activity_apply WHERE activity_id = 111 limit 1";
		$result = array();
		$sqlData = array();
		$result = DBQzoneActivityHelper::getConn()->read($sql, $sqlData, FALSE, 'activity_id');
		return $result;
	}
	
	public function runInsert($data) {
		$sql = "INSERT t_dolphin_qzone_activity_apply 
				(activity_id, user_id, real_name, telephone, address, note, reason, email, openid, img_url, nickname) VALUES
				(:activity_id , :user_id , :real_name , :telephone , :address , :note , :reason , :email , :openid , :img_url , :nickname)";
		$sqlData = array();
		$result = DBQzoneActivityHelper::getConn()->write($sql, $data);
		return $result;

	}

	public function getUserValid($openId, $activityId) {
		$sql = "SELECT status, openid, activity_id FROM t_dolphin_qzone_activity_apply WHERE openid = :openid AND activity_id = :_activity_id ";
		$sqlData = array(
			'openid' => $openId,
			'_activity_id' => $activityId
		);
		$result = DBQzoneActivityHelper::getConn()->read($sql, $sqlData);
		
		return $result;
	}

}

