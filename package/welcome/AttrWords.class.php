<?php
namespace Snake\Package\Welcome;

/**
 * @author jianxu@meilishuo.com
 * @since 2012-05-16
 * @version 1.0
 */

/**
 * Attr class
 * 
 * welcome页属性词模块的一组属性词
 *
 * @author jianxu@meilishuo.com
 * @since 2012-05-16
 * @version 1.0
 */
class AttrWords extends Welcome {

	/**
	 * @var int page_type 
	 * @access protected
	 */
	protected $rePageType = 8;

	protected $sqlSection = array();

	public function __construct($data_id) {
		self::setPageType($this->rePageType);
		self::setDataId($data_id);
		$this->getFromSQL();
	}

	private function getFromSQL() {
		$col = "data_id_ext, title, sortno";
		$this->sqlSection = self::getWelcomeSection($col);
	}

	public function getAttrs() {
		foreach ($this->sqlSection as &$section) {
			if (empty($section['data_id_ext'])) {
				$section['red'] = 0;
			}
			else {
				$section['red'] = 1;
			}
			unset($section['data_id_ext']);
			unset($section['sortno']);
		}
		return $this->sqlSection;
	}

}
