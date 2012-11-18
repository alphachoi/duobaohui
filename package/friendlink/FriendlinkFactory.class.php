<?php
namespace Snake\Package\Friendlink;

use \Snake\Package\Friendlink\Friendlink;
use \Snake\Package\Friendlink\Helper\DBFriendlinkHelper;
class FriendlinkFactory {

	protected $conditions = array();
	protected $friendlink = array();

	public function __construct($conditions) {
		$this->conditions = $conditions;
	}

	public function get_friendlink_list($col = '*') {
		$whereStr = '';
		$sqlData = array();
		foreach ($this->conditions as $key => $value) {
			$whereStr .= " AND {$key}=:{$key}";
			$sqlData[$key] = $value;
		}
		$sql = "SELECT $col FROM t_dolphin_friendlink WHERE 1 {$whereStr}";
		$result = DBFriendlinkHelper::getConn()->read($sql, $sqlData);
		return $result;
	}
}
