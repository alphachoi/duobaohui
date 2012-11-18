<?php
namespace Snake\Package\Welcome;

/**
 * @author jianxu@meilishuo.com
 * @since 2012-05-16
 * @version 1.0
 */
use \Snake\Package\Goods\AttrWords;
use \Snake\Package\Manufactory\Attrmix;

/**
 * Attr class
 * 
 * welcome页属性词模块的一组属性词
 *
 * @author jianxu@meilishuo.com
 * @since 2012-05-16
 * @version 1.0
 */
class Attr extends Welcome {

	/**
	 * @var int page_type 
	 * @access protected
	 */
	protected $rePageType = 7;

	/**
	 * @var int date_type 
	 * @access protected
	 */
	protected $reDateType = 4;

	/**
	 * @var int date_type_ext 
	 * @access protected
	 */
	protected $reDateTypeExt = 5;

	/**
	 * @var array data in db 
	 * @access protected
	 */
	protected $sqlSection = array();

	/**
	 * @var int attrIds 
	 * @access protected
	 */
	protected $attrIds = array();

	/**
	 * @var int attr infos 
	 * @access protected
	 */
	protected $attrWords = array();

	protected $mark = "welcome";
	
	public function __construct($twitterType) {
		self::setPageType($this->rePageType);
		self::setDateType($this->reDateType);
		self::setDateTypeExt($this->reDateTypeExt);
		self::setTwitterType($twitterType);
	}

	private function getFromSQL() {
		$col = "data_id_ext, sortno";
		$this->sqlSection = self::getWelcomeSection($col);
	}

	private function setAttrIds() {
		if (empty($this->sqlSection)) {
			return FALSE;
		}
		foreach ($this->sqlSection as $section) {
			$this->attrIds[] = $section['data_id_ext'];
		}
	}

	private function getAttrWords() {
		if (empty($this->attrIds)) {
			return "";
		}
		$attrMix = new Attrmix($this->attrIds, $this->mark);
		$attrMixs = $attrMix->getAttrMix();
		return array_values($attrMixs);
	}

	/**
	 * 得到welcome的一组九宫格信息
	 * @return array 九宫格数组信息
	 * @access public
	 */
	public function getAttrs() {
		$this->getFromSQL();
		$this->setAttrIds();
		return $this->getAttrWords();
	}

}
