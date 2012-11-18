<?php
namespace Snake\Package\Twitter;

/**
 * @author xuanzheng@meilishuo.com
 * @since 2012-07-06
 * @version 1.0
 */

class TwitterGoods implements \Snake\Libs\Interfaces\Iobservable {

	/**
	 * @var int gid
	 * @access protected
	 * 所发宝贝id
	 */
	protected $gid = 0;

	/**
	 * @var string ip
	 * @access protected
	 * opUser's ip
	 */
	protected $ip = '127.0.0.1';

	/**
	 * @var int
	 * @access protected
	 * 若是活动的推，需要设置
	 */
	protected $activityId = 0;

	/**
	 * @var int
	 * @access protected
	 * author_uid
	 */
	protected $twitterAuthorUid = 0;

	/**
	 * @var str
	 * @access protected
	 * 来源
	 */
	protected $twitterSourceCode = '';

	/**
	 * @var string
	 * @access protected
	 * note
	 */
	protected $note = '';

	/**
	 * @var array tInfo
	 * @access protected
	 * 所发宝贝推的信息
	 */
	protected $tInfo = array();

	protected $observers = array();

	public function __construct($gid, $uid, $source, $ip) {
		$this->gid = intval($gid);
		$this->twitterAuthorUid = intval($uid);
		$this->ip = trim($ip);
		$this->twitterSourceCode = $source;	
		$this->addObserver(new Twitter());
	}

	public function setNote($note) {
		$this->note = trim($note);
		return TRUE;
	}

	public function createGoodsTwitter() {
		if (!$this->convertGoodsDataToTwitterData()) {
			return FALSE
		}
		$this->trigger();
		return TRUE;
	}

	private function trigger() {
		foreach ($this->observers as $object) {
			$object->onChanged("twitterGoods", array($this->tInfo));
		}
		return TRUE;
	}


	private function convertGoodsDataToTwitterData() {
		$goodsObj = new Goods(array("*"), array($this->gid));
		$gInfo = $goodsObj->getGoodsByGids(array($this->gid));
		if (empty($gInfo)) {
			return FALSE;
		}
		if (!empty($this->note)) {
			$this->tInfo['twitter_content'] = trim($this->note);
		}
		else {
			//TODO : urlModel
			$this->tInfo['twitter_content'] = "<a target='_BLANK' class='goods_name' href=''>{$this->gInfo['goods_title']}</a>";
		}
		$this->tInfo['twitter_htmlcontent'] = trim($gInfo['goods_author_note']);
		$this->tInfo['twitter_author_uid'] = $this->twitterAuthorUid;
		$this->tInfo['twitter_create_ip'] = $this->ip;
		$this->tInfo['twitter_create_time'] = $_SERVER['REQUEST_TIME'];
		$this->tInfo['twitter_goods_id'] = $gInfo['goods_id'];
//		信息发布来源 手机/页面 
		if (!empty($this->twitterSourceCode)) {
			$this->tInfo['twitter_source_code'] = $this->twitterSourceCode;
		}
		else {
			$this->tInfo['twitter_source_code'] = 'web';
			if( $gInfo['goods_source_type'] == 2 ){
				$this->tInfo['twitter_source_code'] = 'igg';
			}
			elseif ($data['goods_source_type'] == 3) {
				$this->tInfo['twitter_source_code'] = 'pickup';
			}
		}
//		图片编号 
		$this->tInfo['twitter_images_id'] = $gInfo['goods_picture_id'];
		return TRUE;
	}


	private function addObserver($observer) {
		$this->observers[] = $observer;
		return TRUE;
	}

	private function __set($name, $value) {
		$this->$name = $value;	
	}



}
