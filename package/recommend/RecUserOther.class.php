<?php 
/**
 * 推荐用户相关信息
 * author gstan
 * email guoshuaitan@meilishuo.com
 * date 2012-08-028
 * version 1.0
 */
namespace Snake\Package\Recommend;
use Snake\Libs\DB\MakeSql;
use Snake\Package\Recommend\Helper\DBRecWhaleHelper;

class RecUserOther  {
	private $table = 't_whale_twitter_recommend';

    public function __construct() {
    }  
 	/**
	 *获得谁喜欢过某人的推的tid信息
	 *uid param 用户
	 */
    function getRecommendList($uid, $start = 0, $length = 20, $colum = '*', $master = false, $hashKey = ""){
		$param = array('table' => $this->table,
					   'colum' => $colum,
					   'where' => array('author_id' => $uid),
					   'limit' => "$start , $length",
					   'order' => 'update_time desc'
		);
		$result = $this->getData($param, $master, $hashKey);
        return $result;
    }   

	public function getData($param, $master = false, $hashKey = "") {
		$sqlHelper = new MakeSql();
		$sql = $sqlHelper->MakeSqlType('select', $param);
		$result = DBRecWhaleHelper::getConn()->read($sql, array(), $master, $hashKey);
		return $result;
	}

}
