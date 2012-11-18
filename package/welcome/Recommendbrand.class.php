<?php
namespace Snake\Package\Welcome;

/**
 * !!Attention invalid since 2012-06-15
 * Headsection class
 *
 * welcome 推荐品牌
 *
 * @author jianxu@meilishuo.com
 * @since 2012-05-16
 * @version 1.0
 */
class Recommendbrand extends Welcome {

	/**
	 * @var array brands 
	 * @access protected
	 */
	protected $sections = array();

	/**
	 * @var int page_type 
	 * @access protected
	 */
	protected $rePageType = 6;

	/**
	 * @var string orderby 
	 * @access protected
	 */
	protected $reOrderBy = 'sortno';
	
	public function __construct() {
		self::setPageType($this->rePageType);
		self::setDateType($this->reDateType);
		self::setOrderBy($this->reOrderBy);
	}

	private function getFromSQL() {
		$col = "contents, imgurl, sortno";
		$this->sections = self::getWelcomeSection($col);
	}

	/**
	 * 得到welcome 的推荐品牌
	 * @return array 品牌词信息数组link, image
	 * @access public
	 */
	public function getBrands() {
		$this->getFromSQL();
		return $this->getSections();
	}

	private function getSections() {
		$sections = array();
		foreach ($this->sections as $section) {
			$brand = array(
				'pic_url' => self::getPicUrl($section['imgurl']),
				'link' => $section['contents'],
			);
			$sections[] = $brand;
		}
		return $sections;
	}

}
