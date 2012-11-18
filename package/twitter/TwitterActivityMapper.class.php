<?php
namespace Snake\Package\Twitter;

Use \Snake\Package\Twitter\Helper\DBTwitterActivityHelper;

class TwitterActivityMapper extends \Snake\Package\Base\Mapper{
	private $enforce = array('twitter_id','twitter_author_uid','activity_id');	
	private $twitter = array();

    public function __construct($twitter = array()) {
		parent::__construct($this->enforce);
		$this->twitter = $twitter;
	}

    public function getTwitter() {
        return $this->twitter;
    }   

	public function doInsert($sql, array $sqlData) {
		DBTwitterActivityHelper::getConn()->write($sql, $sqlData);
		$id = DBTwitterActivityHelper::getConn()->getInsertId();
		return $id;
	}

	//TODO
	public function doUpdate() {
	}


	/* 批量获取twitter内容 */
    public function doGet($sql, array $sqlData) {
		$this->twitter = DBTwitterActivityHelper::getConn()->read($sql, $sqlData, $this->master, $this->key);
	    return $this->twitter;
    }
    
}
