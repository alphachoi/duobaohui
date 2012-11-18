<?php
namespace Snake\Package\Twitter;

/**
 * @author jianxu@meilishuo.com
 * @since 2012-07-04
 * @version 1.0
 */
Use \Snake\Package\Twitter\Helper\DBTwitterRecommendHelper;

class TwitterRecommend implements \Snake\Libs\Interfaces\Iobserver {

	private $tid = NULL;

	private $opUser = array();

	private $tUser = array();

	private $time = NULL;

	private $tInfo = array();

	private $sqlTable = 't_whale_twitter_recommend';

	public function __construct() {
		//a empty construct function
	}

	public function onChanged($sender, $args) {
		$this->tid = $args['tid'];
		$this->opUser = $args['opUser'];
		$this->time = $args['time'];
		$this->tInfo = $args['tInfo'];
		$this->RecommendTwitter();
	}

	public function RecommendTwitter() {
		if ($this->opUser['user_id'] == $this->tInfo['author_uid']) {
			return FALSE;
		}
		$recommendInfo = $this->getRecommendInfo();
		if (!empty($recommendInfo)) {
			$this->updateRecommendTime();
		}
		else {
			$this->insertRecommend();
		}
	}

	private function getRecommendInfo() {
		$sql = "SELECT /*recommend-xj*/* FROM {$this->sqlTable} WHERE twitter_id = :twitter_id AND user_id = :user_id";
		$sqlData = array(
			'twitter_id' => $this->tid,
			'user_id' => $this->opUser['user_id'],
		);
		$values = DBTwitterRecommendHelper::getConn()->read($sql, $sqlData);
		return $values;
	}

	public function updateRecommendTime() {
		$sql = "UPDATE {$this->sqlTable} SET update_time = :update_time WHERE twitter_id = :twitter_id AND user_id = :user_id";
		$sqlData = array(
			'update_time' => $this->time,
			'twitter_id' => $this->tid,
			'user_id' => $this->opUser['user_id'],
		);
		DBTwitterRecommendHelper::getConn()->write($sql, $sqlData);
	}

	public function insertRecommend() {
		$sql = "INSERT {$this->sqlTable} (twitter_id, user_id, author_id, update_time) VALUES ({$this->tid}, {$this->opUser['user_id']}, {$this->tInfo['author_uid']}, {$this->time})";
		$sqlData = array();
		DBTwitterRecommendHelper::getConn()->write($sql, $sqlData);
	}

	/**
	 * 查询达人页小红心榜
	 * @author yishuliu@meilishuo.com
	 *
	 */
    public function getTopMm($start, $end) {
        $sql = "SELECT COUNT(twitter_id) as heart_number, author_id as twitter_author_uid FROM {$this->sqlTable} where update_time > :_start and update_time < :_end GROUP BY author_id ORDER BY heart_number DESC limit 0, 50";
        $sqlData = array('_start' => $start, '_end' => $end);
        $result = DBTwitterRecommendHelper::getConn()->read($sql, $sqlData);
        return $result;
    }  
}
