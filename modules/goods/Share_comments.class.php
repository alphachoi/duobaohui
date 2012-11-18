<?php
namespace Snake\Modules\Goods;
Use \Snake\Package\Base\IdentityObject;
Use \Snake\Package\Twitter\Twitter;
Use \Snake\Package\Twitter\TwitterObject;
Use \Snake\Package\User\User;

/**
 * 获取推得评论
 * @author weiwang
 * @since 2012.09.12
 * @example curl
 * snake.mydev.com/goods/share_comments?tid=74090164&page=0&pagesize=8
 */
class Share_comments extends \Snake\Libs\Controller{

	public function run() {
		$tid = isset($this->request->REQUEST['tid']) ? (int)$this->request->REQUEST['tid'] : 0;
	    $page = isset($this->request->REQUEST['page']) ? (int)$this->request->REQUEST['page']:0;
	    $pagesize = isset($this->request->REQUEST['pagesize']) ? (int)$this->request->REQUEST['pagesize'] : 8;
		$userId = $this->userSession['user_id'];
		$replyTwitters = $this->getTwitterReply($tid, $page, $pagesize);
		$uids = \Snake\Libs\Base\Utilities::DataToArray($replyTwitters, "twitter_author_uid");
		$users = $this->getUsers($uids);
		$response = array();
		foreach ($replyTwitters as $twitter) {
			$twitterObj = new TwitterObject($twitter);	
			$comments['twitter_content'] = $twitterObj->getTwitterContent();
			$comments['twitter_id'] = $twitterObj->getId();
			$comments['twitter_create_time'] = $twitterObj->getShareCreateTime();
			$comments['uinfo'] = $users[$twitterObj->getTwitterAuthor()];
			$response[] = $comments;
		}
		$this->view = $response;
	}

	/**
	 * 获取用户的信息
	 * @return array
	 * @access private 
	 */
	private function getUsers($uids) {
		if (empty($uids)) {
			return $uids;
		}
		$users = array();
		if (!empty($uids)) {
			$userAssembler = new User();
			$users = $userAssembler->getUserInfos($uids, array("nickname","user_id","avatar_c"));
		}
		return $users;
	}

	/**
	 * 获取评论的信息
	 * @return array
	 * @access private 
	 */
	function getTwitterReply($tid, $page, $pagesize) {
		$twitter = array();
		if (!empty($tid)) {
			$twitterAssembler = new Twitter(array("twitter_id","twitter_author_uid","twitter_htmlcontent","twitter_create_time","twitter_source_tid"));
			$twitter = $twitterAssembler->getEachTwitterRecentReplys($tid, $page * $pagesize, $pagesize);
		}
		return $twitter;
	}
}
