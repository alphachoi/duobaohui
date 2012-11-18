<?php
namespace Snake\Modules\Goods;
Use Snake\Package\Group\Groups;
Use Snake\Package\Twitter\Twitter;

/**
 * share页面的杂志社推荐内容
 * @author xuanzheng
 * @package goods
 * @request_url http://snake.meilishuo.com/goods/Share_gropu_recommend?tid=***
 * @request_method GET
 * @request_param tid : 请求的twitter_id
 */

class Share_group_recommend extends \Snake\Libs\Controller {

	/**
	 * twitter_id
	 * @var int
	 */
	private $tid = 0;
	private $uid = 0;
	private $groupInfos = array();


	/**
	 * get the requst params
	 * @return TRUE
	 */
	private function initialized() {
		$this->tid = (int)$this->request->REQUEST['tid'];
		$this->uid = $this->userSession['user_id'];
		return TRUE;
	}


	/**
	 * begin to run
	 */
	public function run() {
		$this->initialized();
		if (empty($this->tid)) {
			$this->view = array();
			return TRUE;
		}

		$fields = array('twitter_id', 'twitter_author_uid');
		$tids = array($this->tid);
		$twitterHelper = new Twitter($fields, array()); 
		$tinfo = $twitterHelper->getTwitterByTids($tids);
		
		$authorUid = 0;

		if (!empty($tinfo) && !empty($tinfo[0]['twitter_author_uid'])) {
			$authorUid = $tinfo[0]['twitter_author_uid'];	
		}

		$groupHelper = new Groups();
		$groupInfos = $groupHelper->getShareRightGroups($this->tid, $this->uid, $authorUid);
		if (!empty($groupInfos)) {
			$this->groupInfos = $groupInfos;
		}
		$this->view = $this->groupInfos;
		return TRUE;
	}
}
