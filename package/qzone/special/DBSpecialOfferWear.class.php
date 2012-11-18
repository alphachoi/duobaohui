<?php

namespace Snake\Package\Qzone\Special;

use \Snake\Package\Qzone\Helper\DBQzoneActivityHelper;

class DBSpecialOfferWear {

    public function __construct() {

    }   

    public function getAllGoods() {
        $sql = "SELECT * FROM t_dolphin_qzone_goods WHERE 1 ";
        $result = array();
        $sqlData = array();
        $result = DBQzoneActivityHelper::getConn()->read($sql, $sqlData, FALSE, 'twitter_id');
        return $result;
    }   

    public function getGoodsComments($twitterIds) {
        if (empty($twitterIds)) {
            return array(); 
        }
        $twitterIds = implode(',', $twitterIds);
        $sql = "SELECT twitter_id, commentator_img, contents, sortno  FROM t_dolphin_qzone_goods_comments WHERE twitter_id IN ({$twitterIds}) ORDER BY twitter_id DESC, sortno DESC";
        $sqlData = array();
        $result = DBQzoneActivityHelper::getConn()->read($sql, $sqlData, FALSE);
        return $result;
    }   

	public function setSpecialValue($name, $data){
		if (empty($data)) {
			return FALSE;
		}
		$sql =  "REPLACE INTO `t_dolphin_variables` (`name`, `value` ) VALUES (:name, :value)";
		$sqlData = array(
			'name' => $name,
			'value' => $data
		);
		$result = DBQzoneActivityHelper::getConn()->write($sql, $sqlData);
		return $result;
	}

	public function getSpecialValue($name) {
		$sql = "SELECT value FROM t_dolphin_variables WHERE name = :name";
		$sqlData = array(
			'name' => $name
		);
		$result = DBQzoneActivityHelper::getConn()->read($sql, $sqlData, FALSE);
		return $result;
	}  
}
