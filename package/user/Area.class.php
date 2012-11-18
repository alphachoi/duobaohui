<?php
namespace Snake\Package\User;

Use \Snake\Package\User\Helper\DBUserHelper AS DBUserHelper;

class Area {
	private $table_p = 't_dolphin_dictionary_province';
	private $table_c = 't_dolphin_dictionary_city';	

	public function __construct() {

	}

	/**
	 * 获取省份信息
	 * @param $fields array 要查询的字段
	 * @param $params array 条件
	 *
	 */
	public function getProvinceInfo($fields = array('N_PROVID', 'S_PROVNAME'), $params = array()) {
		if (empty($fields) || !is_array($fields)) {
			return FALSE;
		}
		$fields = implode(',', $fields);
		$sqlData = array();
		$sqlComm = "SELECT {$fields} FROM {$this->table_p} WHERE 1=1 ";
		if (isset($params['N_PROVID'])) {
			$sqlComm .= " AND N_PROVID=:_N_PROVID";
			$sqlData['_N_PROVID'] = $params['N_PROVID'];
		}
		if (isset($params['S_PROVNAME'])) {
			$sqlComm .= " AND S_PROVNAME=:S_PROVNAME";
			$sqlData['S_PROVNAME'] = $params['S_PROVNAME'];
		}
		$result = array();
		$result = DBUserHelper::getConn()->read($sqlComm, $sqlData, FALSE);
		return $result;
	}

	/**
	 * 获取城市信息
	 * @param $fields array 查询的字段
	 * @param $params array 查询条件
	 */
	public function getCityInfo($fields = array('N_CITYID', 'S_CITYNAME', 'N_PROVID'), $params = array()) {
		if (empty($fields) || !is_array($fields)) {
			return FALSE;
		}
		$fields = implode(',', $fields);
		$sqlData = array();
		$sqlComm = "SELECT {$fields} FROM {$this->table_c} WHERE 1=1 ";
		if (isset($params['N_CITYID'])) {
			$sqlComm .= " AND N_CITYID=:_N_CITYID";
			$sqlData['_N_CITYID'] = $params['N_CITYID'];
		}
		if (isset($params['S_CITYNAME'])) {
			$sqlComm .= " AND S_CITYNAME=:S_CITYNAME";
			$sqlData['S_CITYNAME'] = $params['S_CITYNAME'];
		}
		if (isset($params['N_PROVID'])) {
			$sqlComm .= " AND N_PROVID=:N_PROVID";
			$sqlData['N_PROVID'] = $params['N_PROVID'];
		}
		$result = array();
		$result = DBUserHelper::getConn()->read($sqlComm, $sqlData, FALSE);
		return $result;
	}

}
