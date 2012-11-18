<?php
namespace Snake\Package\Twitter;

Use \Snake\Package\Twitter\Helper\DBTwitterVerifyHelper;

class TwitterVerifyMapper extends \Snake\Package\Base\Mapper{
	private $twitterVerify = array();

    public function __construct($twitterVerify = array()) {
		$this->twitterVerify = $twitterVerify;
	}

    public function getTwitterVerify() {
        return $this->twitterVerify;
    }   

	public function doInsert($sql, array $sqlData) {
		DBTwitterVerifyHelper::getConn()->write($sql, $sqlData);
		$id = DBTwitterVerifyHelper::getConn()->getInsertId();
		return $id;
	}

	//TODO
	public function doUpdate() {
	}

	/*protected function doCreateCollection(array $twitters) {
		$collection = new TwitterCollection($twitters, $this);
		return $collection;
	}*/

	/* 批量获取twitter内容 */
    public function doGet($sql, array $sqlData) {
		$this->twitterVerify = DBTwitterVerifyHelper::getConn()->read($sql, $sqlData, $this->master, $this->key);
	    return $this->twitterVerify;
    }
    
}
