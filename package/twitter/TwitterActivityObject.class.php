<?php
namespace Snake\Package\Twitter;
Use \Snake\Package\Manufactory\Face;

class TwitterActivityObject extends \Snake\Package\Base\DomainObject{
	/**
	 * 数据库中的一行纪录
	 * 创建传入这些数据就ok
	 * array('twitter_id','twitter_author_uid','twitter_goods_id','twitter_ctime')
	 * @author xuanzheng@meilishuo.com
	 */

    public function __construct($twitter = array()) {
		$this->row = $twitter;
	}

	public function __set($name, $value) { 
		$this->row[$name] = $value;
    }   

}
