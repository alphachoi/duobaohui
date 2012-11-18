<?php
namespace Snake\Package\Welcome;

/**
 * @author jianxu@meilishuo.com
 * @since 2012-08-12
 * @version 1.0
 */

/**
 * Recommendlist class
 *
 * welcome顶部推荐列表
 *
 * @author jianxu@meilishuo.com
 * @since 2012-08-12
 * @version 1.0
 */
class Recommendlist extends Welcome {

	protected $rePageType = 9;
	protected $reLimit = 8;
	
	public function __construct() {
		self::setPageType($this->rePageType);
		self::setLimit($this->reLimit);
	}

	public function getFromSQL() {
		$col = "title, linkurl, data_id_ext";
		$infos = self::getWelcomeSection($col);
		return $infos;
	}

	public function getSection() {
		$infos = $this->getFromSQL();
		$sections = array();
		foreach ($infos as $info) {
			$section['title'] = $info['title'];
			$section['url'] = $info['linkurl'];
			$section['num'] = $info['data_id_ext'];
			$sections[] = $section;
		}

		return $sections;
	}

}
