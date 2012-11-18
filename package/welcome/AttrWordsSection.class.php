<?php
namespace Snake\Package\Welcome;

/**
 * @author jianxu@meilishuo.com
 * @since 2012-05-16
 * @version 1.0
 */

use \Snake\Package\Welcome\Helper\DBWelcomeTypeHelper;

/**
 * AttrWordsSection class
 *
 * @author jianxu@meilishuo.com
 * @since 2012-07-23
 * @version 1.0
 */
class AttrWordsSection {

	/**
	 * @var int
	 * @access protected
	 */
	protected $pageType = 8;

	/**
	 * @var array
	 * @access protected
	 */
	protected $attrWordsSections = array();

	/**
	 * @var array
	 * @access protected
	 */
	protected $attrWords = array();

	public function __construct() {}

	/**
	 * 得到welcome的属性词数组
	 * @return array 属性词信息数组
	 * @access public
	 */
	public function getSections() {
		$this->getSql();
		return $this->getAttrsWords();
	}

	private function getSql() {
		$col = "id, type_name, sortno";
		$sql = "SELECT /*welcome-xj*/{$col} FROM t_dolphin_cms_index_type WHERE page_type=:_pageType ORDER BY sortno";
		$sqlData['_pageType'] = $this->pageType;
		$this->attrWordsSections = DBWelcomeTypeHelper::getConn()->read($sql, $sqlData);
	}

	private function getAttrsWords() {
		foreach ($this->attrWordsSections as $wordsSection) {
			$attrObj = new AttrWords($wordsSection['id']);
			$attrInfos = $attrObj->getAttrs();
			$this->attrWords[] = array(
				'type' => $wordsSection['type_name'],
				'words' => $attrInfos,
			);
		}
		return $this->attrWords;
	}
	
}
