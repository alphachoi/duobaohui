<?php
namespace Snake\Package\Person;

Use Snake\Package\Person\Helper\DBPersonHelper 	   AS DBPersonHelper;

class Person {
	
	private static $instance = NULL;
	private $table = 'tb_user_goods';
    
    /** 
     * @return Person Object
     */
    public static function getInstance(){
        if (empty(self::$instance)) {
            self::$instance = new Person(); 
        }
        return self::$instance;
    }


	public function addLike($userId, $goodsId) {
        if (empty($userId) || empty($goodsId)) {
            return FALSE;
        }
        $sqlComm = "INSERT IGNORE INTO {$this->table} VALUES (:user_id, :goods_id, :is_red, :create_time)";

		$sqlData = array();
        $sqlData['user_id']			= $userId;
        $sqlData['goods_id']		= $goodsId;
        $sqlData['is_like']			= 1;
        $sqlData['is_forward']		= 1;
        $sqlData['create_time']		= time();
		$isWrite = DBPersonHelper::getConn()->write($sqlComm, $sqlData);
		return $isWrite;
    }
}
