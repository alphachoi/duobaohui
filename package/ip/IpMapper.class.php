<?php
namespace Snake\Package\Ip;
Use \Snake\Package\Ip\Helper\DBIpHelper;

class IpMapper extends \Snake\Package\Base\Mapper{

	private $ip = array();

    public function __construct($ip = array()) {
		$this->ip = $ip;
	}

    public function getIp() {
        return $this->ip;
    }   

	public function doInsert($sql, array $sqlData) {
		DBUrlHelper::getConn()->write($sql, $sqlData);
		return DBIpHelper::getConn()->getInsertId();
	}

	//TODO
	public function doUpdate() {
	}

    public function doGet($sql, array $sqlData) {
		$this->ip = DBIpHelper::getConn()->read($sql, $sqlData, $this->master, $this->key);
	    return $this->ip;
    }
    
}
