<?php
namespace Snake\Package\Cpc;
Use \Snake\Package\Cpc\Helper\DBCpcHelper;

class CpcMapper extends \Snake\Package\Base\Mapper{

	private $cpc = array();

    public function __construct($cpc = array()) {
		$this->cpc = $cpc;
	}

    public function getCpc() {
        return $this->cpc;
    }   

	public function doInsert($sql, array $sqlData) {
		DBUrlHelper::getConn()->write($sql, $sqlData);
		return DBUrlHelper::getConn()->getInsertId();
	}

	//TODO
	public function doUpdate() {
	}

	/* 批量获取twitter内容 */
    public function doGet($sql, array $sqlData) {
		$this->cpc = DBCpcHelper::getConn()->read($sql, $sqlData, $this->master, $this->key);
	    return $this->cpc;
    }
    
}
