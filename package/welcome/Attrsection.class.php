<?php
namespace Snake\Package\Welcome;

/**
 * @author jianxu@meilishuo.com
 * @since 2012-05-16
 * @version 1.0
 */

use \Snake\Package\Welcome\Helper\DBWelcomeTypeHelper;

/**
 * Attrsection class
 *
 * welcome页属性词6个大的分类
 * 输出分类名称，链接，文字属性词
 * 循环调用Attr类，每个属性词输出8个属性词九宫格
 *
 * @author jianxu@meilishuo.com
 * @since 2012-05-16
 * @version 1.0
 */
class Attrsection {

	/**
	 * @var int
	 * @access protected
	 */
	protected $pageType = 5;

	/**
	 * @var array
	 * @access protected
	 */
	protected $typeSections = array();

	/**
	 * @var array
	 * @access protected
	 */
	protected $attrSections = array();

	public function __construct() {}

	/**
	 * 得到welcome的属性词数组
	 * @return array 属性词信息数组
	 * @access public
	 */
	public function getSections() {
		$this->getSql();
		return $this->getAttrs();
	}

	private function getSql() {
		$col = "id, type_name, type_attr";
		$sql = "SELECT /*welcome-xj*/{$col} FROM t_dolphin_cms_index_type WHERE page_type=:_pageType ORDER BY sortno";
		$sqlData['_pageType'] = $this->pageType;
		$this->typeSections = DBWelcomeTypeHelper::getConn()->read($sql, $sqlData);
	}

	private function getAttrs() {
		$i = 1;
		foreach ($this->typeSections as $typeSection) {
			$attrObj = new Attr($typeSection['id']);
			$attrInfos = $attrObj->getAttrs();
			foreach ($attrInfos as &$attrInfo) {
				$attrInfo['number'] = rand(10000, 30000) + ($_SERVER['REQUEST_TIME'] % 10000) + 69;
			}
			$words = array();
			$attrWords = explode(';', $typeSection['type_attr']);
			$attrWords = array_slice($attrWords, 0, 13);
			foreach($attrWords as $attrWord) {
				$attrPair = explode(':', $attrWord);
				if (!empty($attrPair[1])) {
					$words[] = array(
						'name' => $attrPair[1], 
						'link' => MEILISHUO_URL . '/attr/show/' . $attrPair[0],
					);
				}
			}
			$this->attrSections[] = array(
				'title' => $typeSection['type_name'],
				'words' => $words,
				'attrs' => array_values($attrInfos),
				'link_url' => $this->getLink($i),
			);
			$i++;
		}
		return $this->attrSections;
	}

	private function getLink($i) {
		$url = MEILISHUO_URL . '/goods/catalog/';
		switch ($i) {
			case 1:
				$url .= 'dress/2000000000000';
				break;
			case 2:
				$url .= 'shoes/6000000000000';
				break;
			case 4:
				$url .= 'access/7000000000000';
				break;
			case 3:
				$url .= 'bag/5000000000000';
				break;
			case 6:
				$url .= 'beauty/8000000000000';
				break;
			case 5:
				$url .= 'jiaju/9000000000000';
				break;
			default:
				break;
		}
		$url .= '?frm=welcome';
		return $url;
	}
	
}
