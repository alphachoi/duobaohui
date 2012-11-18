<?php
namespace Snake\Package\Welcome;

/**
 * !!Attention invalid since 2012-06-15
 * Hotactivity class
 *
 * welcome热门活动
 *
 * @author jianxu@meilishuo.com
 * @since 2012-05-16
 * @version 1.0
 */
class Hotactivity extends Welcome {
	protected $sections = array();

	protected $rePageType = 3;
	protected $reDateType = 3;
	protected $reLimit = 4;
	
	public function __construct($sourceUid) {
		self::setPageType($this->rePageType);
		self::setDateType($this->reDateType);
		self::setLimit($this->reLimit);
	}

	private function getFromSQL() {
		$col = "title, contents, imgurl, linkurl";
		$this->sections = self::getWelcomeSection($col);
	}

	public function getActivity() {
		$this->getFromSQL();
		$activitySections = array();
		foreach ($this->sections as $section) {
			$activitySection = array(
				'pic_url' => self::getPicUrl($section['imgurl']),
				'title' => $section['title'],
				'link' => $section['linkurl'],
				'description' => $section['contents'],
			);
			$activitySections[] = $activitySection;
		}
		$sections = array(
			'first' => array_shift($activitySections),
			'other' => $activitySections,
		);
		return $sections;
	}
}
