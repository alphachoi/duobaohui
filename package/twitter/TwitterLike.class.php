<?php
namespace Snake\Package\Twitter;

/**
 * @author jianxu@meilishuo.com
 * @since 2012-07-04
 * @version 1.0
 */
Use \Snake\Libs\Cache\Memcache;

require_once(PLATFORM_SERVICE_PATH . '/MlsStorageService.class.php');

/**
 * TwitterLike class
 * 
 * 点击喜欢的操作逻辑，包括写推，更新用户相关信息，调用zoo等
 *
 * @author jianxu@meilishuo.com
 * @since 2012-05-16
 * @version 1.0
 */
class TwitterLike implements \Snake\Libs\Interfaces\Iobservable {

	/**
	 * @var int stid 
	 * @access protected
	 * 被喜欢的tid，如果是此推为小红心，则往上找一层
	 */
	protected $stid = NULL;

	/**
	 * @var array stInfo
	 * @access protected
	 * 被喜欢的推信息
	 */
	protected $stInfo = array();

	/**
	 * @var array opUser
	 * @access protected
	 * 操作用户的信息，由module传入 
	 */
	protected $opUser = NULL;

	/**
	 * @var bool legel
	 * @access protected
	 * 传入是否合法
	 */
	protected $legel = FALSE;

	/**
	 * @var handle ZooClient
	 * @access protected
	 * 到zoo的句柄
	 */
	protected $client = NUll;

	/**
	 * @var string ip
	 * @access protected
	 * opUser's ip
	 */
	protected $ip = '127.0.0.1';

	/**
	 * @access private
	 * abservers
	 */
	private $observers = array();

	private $source = 'web';

	/**
	 * @access public
	 * @param int 传入tid
	 * @param array 传入userInfo
	 */

	public function __construct($stid, $userInfo, $ip = '127.0.0.1', $source = 'web') {
		$this->stid = $this->convertSourceTwitter($stid);
		$this->opUser = $userInfo;
		if (!empty($this->stid) && !empty($this->opUser)) {
			$this->legel = TRUE;
		}
		if ($this->opUser['level'] == 5) {
			$this->legel = FALSE;
		}
		$this->ip = $ip;
		$this->source = $source;
		$this->client = \Snake\Libs\Base\ZooClient::getClient($userInfo['user_id']);
		$this->addObserver(new TwitterRecommend());
		$this->addObserver(new Twitter());
	}

	public function addObserver($observer) {
		$this->observers[] = $observer;
	}

	/**
	 * @access public
	 * 接口逻辑，实现添加/删除喜欢
	 */
	public function twitterLike() {
		if (!$this->legel) {
			return array(
				'type' => 3,
			);
		}
		//TODO spam logic
		//
		$hasLiked = $this->client->twitter_likes_state($this->opUser['user_id'], array($this->stid));
		if (!empty($hasLiked[$this->stid])) {
			//delete a liked twitter
			$this->client->twitter_like($this->stid, 'delete');
			$out = array(
				'str' => '-1',
				'type' => 1,
			);
		}
		else {
			//add a like
			$ret = $this->client->twitter_like($this->stid, 'post', array('ip' => ip2long($this->ip)));
			foreach ($this->observers as $obs) {
				$obs->onChanged('twitterLike', array(
					'tid' => $this->stid, 
					'opUser' => $this->opUser, 
					'time' => $_SERVER['REQUEST_TIME'], 
					'tInfo' => $this->stInfo,
					'ip' => $this->ip,
					'source' => $this->source,
				));
			}

			$out = array(
				'str' => '+1',
				'type' => 2,
				'a_id' => $this->stInfo['author_uid'],
			);
		}
		//clear twitter page cache
		$this->clearTwitterCache();

		return $out;
	}

	private function convertSourceTwitter($tid) {
		$col = array('twitter_id', 'twitter_show_type', 'twitter_source_tid', 'twitter_goods_id', 'twitter_images_id', 'twitter_author_uid');
		$twitterAssembler = new Twitter($col);
		$twitterInfo = $twitterAssembler->getTwitterByTids(array($tid));
		if (empty($twitterInfo)) {
			return FALSE;
		}
		else {
			$this->stInfo = $twitterInfo[0];
		}
		if ($twitterInfo[0]['twitter_show_type'] == 9) {
			return $twitterInfo[0]['twitter_source_tid'];
		}
		else {
			return $tid;
		}
	}

	private function clearTwitterCache() {
        $cache = Memcache::instance();
		$cacheKey = "PAGE_CACHE_SHARE_" . $this->stInfo['twitter_id'];
		$cache->delete($cacheKey);
	}
}
