<?php
namespace Snake\Package\Url;
Use \Snake\Package\Url\Helper\DBUrlHelper;

class UrlMapper extends \Snake\Package\Base\Mapper{

	private $url = array();

    public function __construct($url = array()) {
		$this->url = $url;
	}

    public function getUrl() {
        return $this->url;
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
		$this->url = DBUrlHelper::getConn()->read($sql, $sqlData, $this->master, $this->key);
	    return $this->url;
    }
    
}
