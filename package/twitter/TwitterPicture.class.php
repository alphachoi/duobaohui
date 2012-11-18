<?php
namespace Snake\Package\Twitter;

/**
 * @author xuanzheng@meilishuo.com
 * @since 2012-07-06
 * @version 1.0
 */

class TwitterPicture implements \Snake\Libs\Interfaces\Iobservable {

	/**
	 * @var string ip
	 * @access protected
	 * opUser's ip
	 */
	protected $ip = '127.0.0.1';

	/**
	 * @var int
	 * @access protected
	 * 发推的时间
	 */
	protected $time = NULL;

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
	 * @var int
	 * @access protected
	 * twitter是否在首页显示
	 */
//	protected $twitterReplyShow = 1;
	
	/**
	 * @var pid
	 * @access protected
	 * 图片pid
	 */
	protected $pid = 0;

	/**
	 * @var string
	 * @access protected
	 * note
	 */
	protected $note = '';

	/**
	 * @var int
	 * @access protected
	 * 宝贝汇报
	 * @todo 现在前端是写死的
	 */
//	protected $twitterPicType = 0;

//	const twitterShowType = 2;
//	const twitterSourceUid = 0;

	/**
	 * @var array tInfo
	 * @access protected
	 * 所发图片推的信息
	 */
	protected $tInfo = array();

	protected $observers = array();

	public function __construct($pid, $uid, $source, $ip) {
		$this->pid = intval($pid);
		$this->twitterAuthorUid = intval($uid);
		$this->twitterSourceCode = $source;	
		$this->ip = trim($ip);
		$this->addObserver(new Twitter());
	}

	public function setNote($note) {
		$this->note = trim($note);
		return TRUE;
	}


	public function createPictureTwitter() {
		if (!$this->convertTwitterData()) {
			return FALSE
		}

		$this->trigger();
		return TRUE;
	}

	private function trigger() {
		foreach ($this->observers as $object) {
			$object->onChanged("twitterPicture", array($this->tInfo));
		}
		return TRUE;
	}


	private function convertTwitterData() {
		if (empty($this->pid)) {
			return FALSE;
		}

		if (!empty($this->note)) {
			$this->tInfo['twitter_content'] = trim($this->note);
		}
		else {
			//TODO : urlModel
			$this->tInfo['twitter_content'] = "<a target='_BLANK' class='goods_name' href=''>{$this->gInfo['goods_title']}</a>";
		}
		$this->tInfo['twitter_htmlcontent'] = $this->tInfo['twitter_content'];
		$this->tInfo['twitter_author_uid'] = $this->twitterAuthorUid;
		$this->tInfo['twitter_content'] = trim($this->note);
		$this->tInfo['twitter_create_ip'] = $this->ip;
		$this->tInfo['twitter_create_time'] = $_SERVER['REQUEST_TIME'];
//		$this->tInfo['twitter_source_uid'] = self::twitterSourceUid;  
		$this->tInfo['twitter_source_code'] = $this->twitterSourceCode;  
		$this->tInfo['twitter_images_id'] = $this->pid;  
//		$this->tInfo['twitter_show_type'] = self::twitterShowType;
		$this->tInfo['twitter_pic_type'] = 0;
		$this->tInfo['twitter_goods_id'] = 0;
//		$this->tInfo['twitter_reply_show'] = $this->twitterReplyShow;
		$this->tInfo['pid'] = $this->pid; 
		return true;
	}

	public function addObserver($observer) {
		$this->observers[] = $observer;
		return TRUE;
	}

	private function __set($name, $value) {
		$this->$name = $value;	
	}



}
