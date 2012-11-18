<?php
namespace Snake\Package\Msg;

USE \Snake\Package\Msg\Helper\DBStatHelper AS DBStatHelper;

class WeiboPush {
	private $table = 't_dolphin_stat_pushedWeiboInfo';	

	public function __construct() {

	}

	/**
	 * 获取特定类型的消息
	 * @param $type 消息类型
	 * @param $limit 查询个数
	 * @param $fields 查询字段
	 */
	public function getMsgInfo($platform, $limit, array $fields) {
		if (empty($platform) || empty($limit) || empty($fields)) {
			return FALSE;
		}
		$fields = implode(',', $fields);
		$sqlComm = "SELECT {$fields} FROM {$this->table} WHERE platform=:_platform ORDER BY id DESC LIMIT :_limit ";
		$sqlData = array('_platform' => $platform, '_limit' => $limit);
		$result = array();
		$result = DBStatHelper::getConn()->read($sqlComm, $sqlData);
		return $result;
	}

}
