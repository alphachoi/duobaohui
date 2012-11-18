<?php 
namespace Snake\Package\User;

/**微博用户表
 *author gstan
 *email guoshuaitan@meilishuo.com
 *date 2012-08-08
 *version 1.0
 */

class WeiboUser {
	
	private $table = "t_dolphin_weibo_user_info";	
	private static $instances;

	public function getInstance(){
		$class = get_class();
		if (!isset(self::$instances[$class])) {
			self::$instances[$class] = new $class();
		}
	}
	/**
	 * Map Weibo Ids to Meilishuo uids.
	 * param weibo_ids array
	 */
	public function mapWeiboIdsToUids($weibo_ids, $master = FALSE, $hashKey = "uid") {
		if (empty($weibo_ids) || !is_array($weibo_ids)) {
			return FALSE;	
		}
		$sql = "SELECT uid, weibo_id, screen_name FROM {$this->table} WHERE weibo_id IN (" . implode(',', $weibo_ids) . ")";
		$params = array();
		$result = array();
		$result = DBUserHelper::getConn()->read($sql, $params, $master, $hashKey);
		
		return $result;
		
	}
	
}
