<?php
namespace Snake\Package\User;

/**
 * @author yishuliu@meilishuo.com
 * @since 2012-05-14
 * @version 1.0
 */

Use \Snake\Package\User\Helper\RedisUserConnectHelper;
Use \Snake\Package\User\Helper\RedisUserStatisticHelper;
Use \Snake\Package\User\Helper\CacheUserHelper;
Use \Snake\Package\User\Helper\DBUserStatHelper;
Use \Snake\Libs\Base\ZooClient;
Use \Snake\Package\Spam\SpamUser;
Use \Snake\libs\Cache\Memcache;

/**
 * @author yishuliu@meilishuo.com
 * @since 2012-05-14
 * @version 1.0
 */

class QzoneUserStat {

    private static $instance = NULL;
       
    /** 
     * @return userConnect Object
     */
    public static function getInstance() {
        if (empty(self::$instance)) {
            self::$instance = new self(); 
        }   
        return self::$instance;
    }   
   
    private function __construct() {
    }   
    
    //取每天的数据,做趋势图的时候用到
    public function getDayData($date) {
        $curr_date = !empty($date) ? $date : date("Y-m-d"); 
        $curr_date_end = $curr_date.' 23:59:59';
        $sqlComm = "select * from t_dolphin_stat_qzfans where create_time between :_start and :_end";
        $sqlData = array('_start'=>$curr_date, '_end'=>$curr_date_end);
		$result = DBUserStatHelper::getConn()->read($sqlComm, $sqlData);
        return $result; 
    }   

    //取当天最新美丽说平台粉丝数
    public function getCurrentFans($column, $create_time) {
        if (empty($column)) {
            return false; 
        }   
        $sqlComm = "select max($column) as $column from t_dolphin_stat_qzfans where create_time >=:create_time"; 
		$sqlData = array('create_time' => $create_time);
		$result = DBUserStatHelper::getConn()->read($sqlComm, $sqlData);
        return !empty($result) ? $result[0][$column] : false;
    }   
}
