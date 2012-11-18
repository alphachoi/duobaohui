<?php
namespace Snake\Package\Twitter;
Use \Snake\Package\Manufactory\Face;

class TwitterObject extends \Snake\Package\Base\DomainObject{

	private $enforce = array('twitter_id', 'twitter_author_uid', 'twitter_create_ip', 'twitter_create_time', 'twitter_goods_id', 'twitter_images_id', 'twitter_source_uid', 'twitter_source_tid', 'twitter_source_code', 'twitter_show_type', 'twitter_options_show', 'twitter_content', 'twitter_htmlcontent', 'twitter_options_num', 'twitter_reply_show', 'twitter_pic_type');

    public function __construct($twitter = array()) {
		$this->row = $twitter;
	}

	private function enforce($twitter) {
		foreach ($twitter as $key=>$row) {
			if (!in_array($key, $this->enforce)) {
				unset($twitter[$key]);	
			}
		}
		return $twitter;	
	}

	public function getRawTwitter() {
		$this->row = $this->enforce($this->row);
		return $this->row;
	}

	public function set($name, $value) {
		$this->row[$name] = $value;
	} 

	public function __set($name, $value) { 
		$this->row[$name] = $value;
    }   
	
    public function getRow() {
		//需要对外转换的东西请在此转换
		$this->row['twitter_htmlcontent'] = $this->getTwitterContent();
        return $this->row;
    }   

	public function isDeleted() {
		if ($this->row['twitter_show_type'] < 2) {
			return TRUE;	
		}	
		return FALSE;
	}

	public function setId($tid) {
		$this->row['twitter_id'] = $tid;
	}

	public function getId() {
		return $this->row['twitter_id'];
	}

	public function getTwitterImageId() {
		return $this->row['twitter_images_id'];
	}

	public function getTwitterShowType() {
		return $this->row['twitter_show_type'];
	}

	public function getTwitterSourceTid() {
		return $this->row['twitter_source_tid'];
	}

	public function getTwitterSourceUid() {
		return $this->row['twitter_source_uid'];
	}

	public function getTwitterGoodsId() {
		return $this->row['twitter_goods_id'];
	}

	public function getTwitterAuthor() {
		return $this->row['twitter_author_uid'];
	}

	public function getTwitterHtmlcontent() {
		return $this->row['twitter_htmlcontent'];
	}

	public function getShareCreateTime() {
		$timeConverter = new \Snake\Package\Manufactory\TimeConverter($this->row['twitter_create_time']);
		return $timeConverter->convert();
	}

	public function	getTwitterContent() {
		if (!isset($this->row['twitter_htmlcontent'])) {
			return "";
		}
		$tmpArr = explode("<br/>" , $this->row['twitter_htmlcontent']);
        $str = $tmpArr[0];
		$str = Face::getInstance()->_getFaceCode($str);
        return trim($str);
	} 
}
