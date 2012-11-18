<?php
namespace Snake\Package\Msg;

USE \Snake\Package\Msg\Helper\DBMsgHelper AS DBMsgHelper;

class TimePush {
	private $table = 't_dolphin_weibo_timepush';	

	public function __construct() {

	}

	/**
	 * 获取整点的特定类型的消息
	 * @param $type 消息类型
	 * @param $time 发送时间(整点) 
	 * @param $limit 查询个数
	 * @param $fields 查询字段
	 */
	public function getMsgInfo($type, $time, $limit, array $fields) {
		if (empty($type) || empty($time) || empty($fields)) {
			return FALSE;
		}
		$fields = implode(',', $fields);
		$sqlComm = "SELECT {$fields} FROM {$this->table} WHERE weibo_type =:type AND LEFT(pushtime, 10) =:time ORDER BY id DESC LIMIT :_limit ";
		$sqlData = array('type' => $type, 'time' => $time, '_limit' => $limit);
		$result = array();
		$result = DBMsgHelper::getConn()->read($sqlComm, $sqlData);
		return $result;
	}

	/**
	 * 获取最新的特定类型的消息
	 * @param $type 消息类型
	 * @param $limit 查询个数
	 * @param $fields 查询字段
	 */
	public function getMsgInfoByID($type, $limit, array $fields) {
		if (empty($type) || empty($limit) || empty($fields)) {
			return FALSE;
		}
		$fields = implode(',', $fields);
		$sqlComm = "SELECT {$fields} FROM {$this->table} WHERE weibo_type =:type ORDER BY id DESC LIMIT :_limit ";
		$sqlData = array('type' => $type, '_limit' => $limit);
		$result = array();
		$result = DBMsgHelper::getConn()->read($sqlComm, $sqlData);
		return $result;
	}

	/**
	 * 获取一段时间内特定类型的消息
	 * @param $type 消息类型 
	 * @param $startTime 发送时间起端
	 * @param $endTime 发送时间末端
	 * @param $limit 查询个数
	 * @param $fields 查询字段
	 */
	public function getPeriodMsgInfo($type, $startTime, $endTime, $limit, array $fields) {
		if (empty($type) || empty($startTime) || empty($endTime) || empty($limit) || empty($fields)) {
			return FALSE;
		}
		$fields = implode(',', $fields);
		$sqlComm = "SELECT {$fields} FROM {$this->table} WHERE weibo_type =:type AND pushtime BETWEEN :start AND :end ORDER BY id DESC LIMIT :_limit";
		$sqlData = array('type' => $type, 'start' => $startTime, 'end' => $endTime, '_limit' => $limit);
		$result = array();
		$result = DBMsgHelper::getConn()->read($sqlComm, $sqlData);
		return $result;
	}

	/**
	 * 批量更新是否发送
	 * @param $ids 更新的编号
	 * @param $issend 1 已发送 0 未发送
	 */
	public function setMsgIssend($ids, $issend = 1) {
		if (empty($ids) || !is_array($ids)) {
			return FALSE;
		}	
		$ids = implode(',', $ids);
		$sqlComm = "UPDATE {$this->table} SET issend =:issend WHERE id IN (:id)";
		$sqlData = array('issend' => $issend, 'id' => $ids);
		$log = new \Snake\Libs\Base\SnakeLog('gc_msg', 'normal');
		$log->w_log(print_r(array($sqlComm, $sqlData), true));
		DBMsgHelper::getConn()->write($sqlComm, $sqlData);
		return TRUE;
	}
}
