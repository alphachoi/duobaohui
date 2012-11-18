<?php 
namespace Snake\Package\Recommend;

/**微博用户表
 *author gstan
 *email guoshuaitan@meilishuo.com
 *date 2012-08-08
 *version 1.0
 */
use Snake\Package\Recommend\Helper\DBRecHelper;
use Snake\Libs\DB\MakeSql;
class WeiboUser {
	
	private $table = "t_dolphin_weibo_user_info";	
	private static $instances;

	public static function getInstance(){
		$class = get_class();
		if (!isset(self::$instances[$class])) {
			self::$instances[$class] = new $class();
		}
		return self::$instances[$class];
	}
	/**
	 * Map Weibo Ids to Meilishuo uids.
	 * param weibo_ids array
	 */
	public function mapWeiboIdsToUids($colum, $weibo_ids, $master = false, $hashkey = '') {
		if (empty($weibo_ids) || !is_array($weibo_ids)) {
			return FALSE;	
		}
		$weibo_ids = implode(',', $weibo_ids);
		$param = array('table' => $this->table,
					   'colum' => $colum,
					   'where_in' => array('weibo_id' => $weibo_ids)
		);
		$result  = $this->getData($param, $master, $hashkey);
		return $result;
	}
	//构造sql，获取数据
	public function getData($param, $master = FALSE, $hashKey = '' ) {
		$sqlHelper = new MakeSql();
		$sql = $sqlHelper->MakeSqlType('select', $param);
		$result = DBRecHelper::getConn()->read($sql, array(), $master, $hashKey);
		return $result;
	}
	
}
