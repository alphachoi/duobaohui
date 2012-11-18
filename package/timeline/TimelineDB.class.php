<?php
namespace Snake\Package\Timeline;

Use \Snake\Package\Timeline\Helper\DBTimelineHelper;

class TimelineDB {

	private static $instance = NULL;
    
    /** 
     * @return timelineDB Object
     */
    public static function getInstance(){
        if (empty(self::$instance)) {
            self::$instance = new TimelineDB(); 
        }   
        return self::$instance;
    }   
	
    private function __construct() {
    }   

    /**  
     * 从数据库获取用户tids 
     * @param $user_id int 用户编号
     * @param $columns array 查询的列
     *
     */
    public function getTimelineFromDB($userId, $columns = '*') {
        if (empty($userId)) {
            return FALSE;
        }   
        $sql = "SELECT {$columns} FROM t_seal_user_twitter WHERE user_id=:_user_id";
        $sqlData['_user_id'] = $userId;
        $result = array();
		$result = DBTimelineHelper::getConn()->read($sql, $sqlData);
        return $result;    
    }

	/**  
     * 把Timeline中的tids写入回写到数据库
     * @param $user_id 用户编号
     * @param $last_tid 最新的tid
     * @param $tids tids
     */
    public function insertTimelineToDB($userId, $lastTid, $tIds) {
        if (empty($userId)) {
            return FALSE;
        }

        $sql = "INSERT INTO t_seal_user_twitter(user_id, last_tid, tids) VALUES (:_user_id, :_last_tid, :_tids)";
        $result = $this->getTimelineFromDB($userId, 'user_id');
        if (!empty($result)) {
            $sql = "UPDATE t_seal_user_twitter SET last_tid=:_last_tid, tids=:_tids WHERE user_id=:_user_id";
        }
        $sqlData = array(
            '_user_id' => $userId,
            '_last_tid' => $lastTid,
            '_tids' => $tIds,
        );
		DBTimelineHelper::getConn()->write($sql, $sqlData);
    }
   
}
