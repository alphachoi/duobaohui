<?php
namespace Snake\Package\Welcome;

/**
 * !!Attention invalid since 2012-06-15
 * Headsection class
 *
 * welcome 推荐小组
 *
 * @author jianxu@meilishuo.com
 * @since 2012-05-16
 * @version 1.0
 */

use \Snake\Package\Group\Groups;

class Hotgroup extends Welcome {
	protected $groups = array();
	protected $sections = array();

	protected $rePageType = 2;
	protected $reDateType = 2;
	protected $reLimit = 6;
	
	public function __construct() {
		self::setPageType($this->rePageType);
		self::setDateType($this->reDateType);
		self::setLimit($this->reLimit);
	}

	public function setPicUrl($pic) {
		$this->picUrl = self::getPicUrl($pic);
	}

	private function getFromSQL() {
		$col = "data_id, contents, imgurl";
		$this->sections = self::getWelcomeSection($col);
	}

	private function getGroupIds() {
		if (empty($this->sections)) {
			return FALSE;
		}
		foreach ($this->sections as $section) {
			$this->groups[] = $section['data_id'];
		}
	}

	private function getGroupInfos() {
		$groupHelper = new Groups();
		$this->groups = $groupHelper->getGroupInfo(
			$this->groups, 
			$col = array('group_id', 'name', 'count_member'), 
			$hashKey = 'group_id'
		);
	}

	public function getHotGroup() {
		$this->getFromSQL();
		$this->getGroupIds();
		$this->getGroupInfos();
		return $this->getSections();
	}

	private function getSections() {
		$groupSections = array();
		foreach ($this->sections as $section) {
			$groupSection = array(
				'pic_url' => self::getPicUrl($section['imgurl']),
				'title' => $this->groups[$section['data_id']]['name'],
				'link' => MEILISHUO_URL . '/group/' . $section['data_id'],
				'description' => $section['contents'],
				'follow_num' => $this->groups[$section['data_id']]['count_member'],
			);
			$groupSections[] = $groupSection;
		}
		$sections = array(
			'first' => array_shift($groupSections),
			'other' => $groupSections,
		); 
		return $sections;
	}
}
