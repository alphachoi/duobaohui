<?php
namespace Snake\Package\Welcome;

/**
 * !!Attention invalid since 2012-06-15
 * Headsection class
 *
 * welcome 推荐达人模块
 *
 * @author jianxu@meilishuo.com
 * @since 2012-05-16
 * @version 1.0
 */
use \Snake\Package\User\User;

class Recommenduser extends Welcome {
	protected $sections = array();

	protected $userIds = array();
	protected $users = array();

	protected $rePageType = 4;
	protected $reDateType = 1;
	protected $reDateTypeExt = 3;
	
	public function __construct() {
		self::setPageType($this->rePageType);
		self::setDateType($this->reDateType);
		self::setDateTypeExt($this->reDateTypeExt);
		self::setLimit($this->reLimit);
	}

	private function getFromSQL() {
		$col = "data_id, data_id_ext, contents, imgurl";
		$this->sections = self::getWelcomeSection($col);
	}

	private function getUserBase() {
		foreach ($this->sections as $section) {
			$this->userIds[] = $section['data_id'];
		}
		$userObj = new User();
		$this->users = $userObj->getUserInfos($this->userIds, $params = array('nickname'));
	}

	public function getRecommendUser() {
		$this->getFromSQL();
		$this->getUserBase();
		$sections = array();
		foreach ($this->sections as $section) {
			$daren = array(
				'pic_url' => self::getPicUrl($section['imgurl']),
				'link' => MEILISHUO_URL . '/person/u/' . $section['data_id'] . '?frm=recom1&user_id=' . $section['data_id'],
				'star' => array(
					'uid' => $section['data_id'],
					'name' => $this->users[$section['data_id']]['nickname'],
					'description' => $section['contents'],
					'isFollow' => 0, 
				),
			);
			$sections[] = $daren;
		}
		return array($sections, $this->users);
	}
}
