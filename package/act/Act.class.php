<?php
namespace Snake\Package\Act;

/**
 * @author guoshuaitan@meilishuo.com
 * @since 2012-07-05
 * @version 1.0
 */
Use Snake\Libs\DB\MakeSql;
Use Snake\Package\Act\Helper\DBActHelper;


/**
 * act class 
 * 活动信息
 * @author guoshuaitan@meilishuo.com
 * @since 2012-06-28
 * @version 1.0
 */
class Act {
	
	private $table = 't_dolphin_activity_singlePage_info';
	private $twtable = "t_dolphin_activity_singlePage_twitter";
	public function __construct() {}
	
	/**
	 * 获取活动信息
	 * @return array
	 * @param $tids 推id
	 * @param $colum 查询的列
	 * @param $param 其他的参数
	 */
	 public function getActInfoByTids($tids, $column, $param = array(), $master = FALSE, $hashKey="") {
		if(empty($tids)) {
			return FALSE;
		}
		$tids = implode(",", $tids);

		$sqlComm = "select {$column} from {$this->twtable} t2  right join {$this->table} t1 on t1.activity_id = t2.activity_id where t2.twitter_id in ({$tids})";
		if (isset($param['status'])) {
			$sqlData['_status'] = $param['status'];
			$sqlComm .= "  and status = :_status ";
		}
	    $result = array();
		$result = DBActHelper::getConn()->read($sqlComm, $sqlData, $master, $hashKey);
		return $result;
			 
	 }
	 /**
	  * 获得获得加精推的状态,没有删除的
	  */
	public function getActTwitters($tids, $column, $type = 4, $hashkey = "twitter_id", $master = false) {
		if (empty($tids)) {
			return FALSE;	
		}	  
		$tids = implode(",", $tids);
		$sqlComm = "select {$column}  from {$this->twtable} where tids in ({$tids}) ";
		if ($type == 4) {
			$sqlComm .= " and show_type > 1";
		} 
		else {
			$sqlComm .= " and show_type = $type";	
		}
		$result = array();
		$sqlData = array();
		$result = DBActHelper::getConn()->read($sqlComm, $sqlData, $master, $hashKey);
		return $result;
	}
}

