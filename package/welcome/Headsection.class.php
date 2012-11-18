<?php
namespace Snake\Package\Welcome;

/**
 * @author jianxu@meilishuo.com
 * @since 2012-05-16
 * @version 1.0
 */

/**
 * Headsection class
 *
 * welcome顶部推荐达人
 *
 * @author jianxu@meilishuo.com
 * @since 2012-05-16
 * @version 1.0
 */
class Headsection extends Welcome {

	protected $rePageType = 1;
	
	public function __construct() {
		self::setPageType($this->rePageType);
	}

	public function getFromSQL() {
		$col = "imgurl, linkurl, sortno";
		$infos = self::getWelcomeSection($col);
		return $infos;
	}

	public function getHeadSection() {
		$infos = $this->getFromSQL();
		$section = array();
		foreach ($infos as $info) {
			$section[] = array($info['linkurl'], self::getPicUrl($info['imgurl']));
		}

		return $section;
	}

}
