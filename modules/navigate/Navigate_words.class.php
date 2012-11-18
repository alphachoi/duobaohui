<?php
namespace Snake\Modules\Navigate;

Use Snake\Package\Goods\KeyWords;
Use Snake\Package\Goods\CatalogAttrMap;
Use Snake\Libs\Cache\Memcache;
Use Snake\Package\Base\IdentityObject;
Use Snake\Package\Goods\AttrWords;

/**
 * @example 
 * curl snake.mydev.com/navigate/navigate_words?cata_id=5000000000000 
 *
 * @author weiwang 
 */
class Navigate_words extends \Snake\Libs\Controller  {

	
	public function run() {
		$cataIds = intval($this->request->REQUEST['cataids']);
		$num = intval($this->request->REQUEST['num']);
		$hot = 9999;
		if (empty($num)) {
			$num = 1000;
		}

		//$nameMap = array($hot => "热门","2000000000000" => "衣服","6000000000000" => "鞋子","5000000000000" => "包包","7000000000000" => "配饰","9000000000000" => "家居","8000000000000" => "美容","2001000000000" => "上衣","2004000000000" => "裙子","2006000000000" => "裤子","2009000000000" => "内衣");
		$nameMap = array($hot => "热门", "2000000000000" => "衣服", "2001000000000" => "上衣", "2004000000000" => "裙子", "2006000000000" => "裤子", "2009000000000" => "内衣", "6000000000000" => "鞋子", "5000000000000" => "包包", "7000000000000" => "配饰", "9000000000000" => "家居", "8000000000000" => "美容");
		$ids = array_keys($nameMap);
		if (!empty($cataIds)) {
			$ids = explode(",", $cataIds);
		}
		
		$identityObject = new IdentityObject();
		$identityObject->field('catalog_id')->in($ids)->orderby("group_sortno asc,word_sortno asc");
		$identityObject->col(array("catalog_id","word_id", "isred", "group_name"));

		$cataAttrMap = new CatalogAttrMap();
		$catalogAttrMapInfo = $cataAttrMap->getCatalogAttrMap($identityObject);
		//$catalogAttrMapInfo = \Snake\Libs\Base\Utilities::changeDataKeys($cpcInfo, "word_id");
		$wordIds = \Snake\Libs\Base\Utilities::DataToArray($catalogAttrMapInfo, "word_id");

		$keywordsIdentityObject = new IdentityObject();
		$keywordsIdentityObject->field('word_id')->in($wordIds);
		$keywordsIdentityObject->col(array("word_name","word_id"));

		$keywords = new KeyWords();
		$keywordsInfo = $keywords->getKeywords($keywordsIdentityObject);
		$keywordsInfo = \Snake\Libs\Base\Utilities::changeDataKeys($keywordsInfo, "word_id");
		$navigate = array();
		foreach($catalogAttrMapInfo as $key => $value) {
			$value['word_name'] = $keywordsInfo[$value['word_id']]['word_name'];
			if (isset($navigate[$nameMap[$value['catalog_id']]]) && count($navigate[$nameMap[$value['catalog_id']]]) > $num) {
				continue;
			}
			if (empty($value['word_name'])) {
				continue;
			}
			$navigate[$nameMap[$value['catalog_id']]][$value['group_name']][] = $value;
		}

		if (in_array($hot, $ids) || empty($cataIds)) {
			$attrs = AttrWords::getPopularAttrWords();	
			$length = min($num, count($attrs));
			for ($i = 0; $i <= $length; $i ++) {
				if (empty($attrs[$i])) {
					continue;
				}
				$hotAttr['catalog_id'] = $hot;
				$hotAttr['word_id'] = 0;
				$hotAttr['isred'] = 0;
				$hotAttr['word_name'] = $attrs[$i];
				$navigate[$nameMap[$hot]][$nameMap[$hot]][] = $hotAttr;
			}
		}
		//保证顺序
		$sorted = array();
		foreach ($nameMap as $id => $name) {
			$sorted[$name] = $navigate[$name];	
		}
		if (!empty($navigate) ) {
			$this->view = $sorted;
		}
		else {
			$this->view = array(); 
		}
	}


	/**
	 *感觉这东西放在controller里好一些,jianxu~~~
	 */
	private function errorMessage($code, $message) {
		self::setError(400, $code, $message);
		//$this->head  = 400;
		//$this->view  = array('code' => $code, 'message' => $message);
		return TRUE;
	}


} 
