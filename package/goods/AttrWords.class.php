<?php
namespace Snake\Package\Goods;

Use Snake\Package\Goods\Helper\DBAttrHelper; 
Use Snake\Package\Base\IdentityObject;
Use Snake\Package\Base\DomainObjectAssembler; 
Use Snake\Package\Cms\CmsIndexWelcome;

class AttrWords {
	static $brandlabel = 15;
	static $tab_map  = array(
		2000000000000 => '衣服', 2001000000000 => '上衣',
		2004000000000 => '裙子', 2006000000000 => '裤子',
		2009000000000 => '内衣', 6000000000000 => '鞋子',
		5000000000000 => '包包', 7000000000000 => '配饰',
		8000000000000 => '美容', 9000000000000 => '家居');


	/**
	 *获取类目页的keywords
	 */
	static function getkeywords($cataId, $wordId) {
		$sub = self::getCatalogKeyWords($cataId);
		if (empty($sub)) {
			if (array_key_exists($cataId, self::$tab_map)) {
				return array('currentWord' => self::$tab_map[$cataId]); 
			}
			else {
				return array('currentWord' => '');
			}
		}
		$maxgroupId   = 0;
		$wordIds	  = array();
		foreach ($sub as $key => $value) {
			if ($value['group_sortno'] > $maxgroupId) {
				$maxgroupId = $value['group_sortno'];
			}
			$wordIds[] = $value['word_id'];
		}

		$params = array();
		$params['word_id'] = $wordIds;
		$wordInfo = self::getWordInfo($params, "word_name,word_id");
		$wordInfo = \Snake\Libs\Base\Utilities::changeDataKeys($wordInfo, "word_id");
		$subInfo  = array();



		foreach	($sub as $k => $v) {
			if (empty($wordInfo[$v['word_id']])) { 
				continue;
			}
			$group_name = $v['group_name'];
			$wordInfo[$v['word_id']]['isred'] = $v['isred'];
			$subInfo['group_keywords'][$group_name][] = $wordInfo[$v['word_id']];
			$subInfo['group_info'][$group_name]['group_sortno'] = $v['group_sortno'];
			$subInfo['group_info'][$group_name]['column']		= $v['group_sortno'];
			$subInfo['group_info'][$group_name]['catalog_id']	= $v['catalog_id'];
		}
		if (is_array($subInfo['group_keywords'])) {
			$subInfo['group_map'] = array_keys($subInfo['group_keywords']);
		}
		else {
			$subInfo['group_map'] = array();
		}

		$catalogGroupIco = new CatalogGroupIco();
		$icos = $catalogGroupIco->getCatalogGroupIcoByCidAndGroupName($cataId, $subInfo['group_map']);
		foreach ($icos as $ico) {
			if (empty($subInfo['group_info'][$ico['group_name']])) {
				continue;
			}	
			$subInfo['group_info'][$ico['group_name']]['ico_sortno'] = $ico['ico_sortno'];
		}

		$keywords['sub'] = $subInfo;
		$keywords['currentWord'] = empty($wordInfo[$wordId]['word_name']) ? self::$tab_map[$cataId] : $wordInfo[$wordId]['word_name'];
		return $keywords;
	}


	static function getWordInfo($params = array(), $selectComm = "*") {
		$selectComm = explode(",", $selectComm);

		if (empty($params)) {
			return array();
		}

		//无标签 && 无word_name && 无word_id && != array('all') && 无same_to
		if ((empty($params['label_id']) || (empty($params['label_id_begin']) && empty($params['label_id_end']))) &&  empty($params['word_id']) && empty($params['word_name']) && $params != array('all') && empty($params['same_to'])) {
			return array();
		}

		$identityObject = new IdentityObject();
		if (isset($params['isuse'])) {
			$identityObject->field('isuse')->eq($params['isuse']);
		}
		if (!empty($params['word_id'])) {
			if (is_array($params['word_id'])) {
				$identityObject->field('word_id')->in($params['word_id']);
			}
			else {
				$identityObject->field('word_id')->eq($params['word_id']);
			}
		}
		if (!empty($params['word_name'])) {
			if (is_array($params['word_name'])) {
				foreach($params['word_name'] as &$p) {
					$p = "'{$p}'";
				}
				$identityObject->field('word_name')->in($params['word_name']);
			}
			else {
//				$params['word_name'] = "'{$params['word_name']}'";
				$identityObject->field('word_name')->eq($params['word_name']);
			}
		}
		if (!empty($params['label_id_begin']) && !empty($params['label_id_end'])) {
			$params['label_id_begin'] -= 1;
			$params['label_id_end'] += 1;
			$value = array($params['label_id_begin'], $params['label_id_end']);
			$identityObject->field('label_id')->between($value);
		}

		if (!empty($params['label_id'])) {
			$identityObject->field('label_id')->eq($params['label_id']);
		}
		if (!empty($params['same_to'])) {
			$identityObject->field('same_to')->eq($params['same_to']);
		}
		//设置需要获取的列
		$identityObject->col($selectComm);

		$domainObjectAssembler = new DomainObjectAssembler(KeywordsPersistenceFactory::getFactory('\Snake\Package\Goods\KeywordsPersistenceFactory'));
		$keywordsCollection = $domainObjectAssembler->mysqlFind($identityObject);

		while ($keywordsCollection->valid()) {
			$keywordsObj = $keywordsCollection->next();	
			$keywords[] = $keywordsObj->getRow();
		}

		$log = new \Snake\Libs\Base\SnakeLog('getWordInfo', 'normal');
		$log->w_log(count($keywords));
		if (count($keywords) > 1000) {
			$log->w_log(print_r($params, TRUE));	
		}
		return $keywords;

	}


	static function getSameWord($wordId) {
		if (empty($wordId)) {
			return array();
		}
		$params = array();
		$params['same_to'] = $wordId;
		$params['isuse']   = 1;
		$sameInfo = self::getWordInfo($params, "/*AttrWords-zx*/word_name,word_id");
		return $sameInfo;
	}


	static function getCatalogKeyWords($wordId) {
		$sql = "SELECT /*AttrWords-zx*/* FROM t_dolphin_catalog_attr_map WHERE catalog_id = :_catalog_id ORDER BY group_sortno asc,word_sortno asc";
		$sqlData = array();
		$sqlData['_catalog_id'] = $wordId;
		$wordInfo = DBAttrHelper::getConn()->read($sql, $sqlData);
		return $wordInfo;
	}


	static function getSearchString($wordId, $word_name, $filter = ""){
		//得到所有的attr的同义词
		$searchString = array();
		if (empty($wordId)) {
			if (empty($word_name)) {
				return array(); 
			}
			else {
				$searchString[] = $word_name;
				return $searchString;
			}
		}
		$params = array();
		$params['same_to'] = $wordId;
		$params['isuse'] = 1;
		$sameInfo = self::getWordInfo($params, "/*AttrWords-zx*/word_name");
		$searchString = \Snake\Libs\Base\Utilities::DataToArray($sameInfo, 'word_name');
		if (!in_array($word_name, $searchString)) {
			$searchString[] = $word_name;
		}
		if (!empty($filter)) {
			foreach ($searchString as &$word) {
				$word = $filter . $word;
			}
		}
		return $searchString;
	}


	static function getAttrBlackList($param = array(), $selectComm = "*") {

		$selectComm = explode(",", $selectComm);

		if (empty($params)) {
			return array();
		}

		$identityObject = new IdentityObject();

		if (isset($param['data_type'])) {
			$identityObject->field('data_type')->in($params['data_type']);
		}
		//这个地方如果需要根据word_id进行搜索的话一定要传递data_type字段进来，否则将用不到索引
		if (isset($param['word_id'])) {
			$identityObject->field('word_id')->eq($params['word_id']);
		}
		//设置需要获取的列
		$identityObject->col($selectComm);
		$domainObjectAssembler = new DomainObjectAssembler(KeywordsBlackListPersistenceFactory::getFactory('\Snake\Package\Goods\KeywordsBlackListPersistenceFactory'));
		$keywordsBlackListCollection = $domainObjectAssembler->mysqlFind($identityObject);

		while ($keywordsBlackListCollection->valid()) {
			$keywordsBlackListObj = $keywordsBlackListCollection->next();	
			$keywords[] = $keywordsBlackListObj->getRow();
		}

		return $keywords;
	}

	/**
	 * 取t_dolphin_attr_weight数据
	 *
	 * 通过传进的word_id和type_id获取相关的word_id
	 * type_id : 3 => 亲属, 4 => 风格, 5 => 最佳款式, 6 => 搭配, 7 => 品牌
	 *
	 * @param array $param
	 * @param string $selectComm
	 * @return array 
	 *
	 * TODO:由于or功能没有完善，取得时候需要word1和word2都取一边
	 */
	static function getRelativeWords($params = array(),$selectComm = "*") {

		if (empty($params)) {
			return array();
		}

		$selectComm = explode(",", $selectComm);
		$identityObject = new IdentityObject();
		if (isset($params['word1'])) {
			$identityObject->field('word1')->eq($params['word1']); 
		}
		if (isset($params['word2'])) {
			$identityObject->field('word2')->eq($params['word2']); 
		}
		if (isset($params['type_id'])){
			$identityObject->field('type_id')->eq($params['type_id']);
		}
		$identityObject->col($selectComm);
		$domainObjectAssembler = new DomainObjectAssembler(AttrWeightPersistenceFactory::getFactory('\Snake\Package\Goods\AttrWeightPersistenceFactory'));
		$attrWeightCollection = $domainObjectAssembler->mysqlFind($identityObject);

		while ($attrWeightCollection->valid()) {
			$attrWeightObj = $attrWeightCollection->next();
			$words[] = $attrWeightObj->getWords();
		}
		return $words;
	} 

	/**
	 * getRelativeWordsInfosByAttr
	 * 
	 * 通过属性词id 和 type 获取相应的属性词信息
	 *
	 * @param int $wordId
	 * @param int $type
	 * @return array
	 * @todo 1.待将getWordInfo的获取字段作为参数传入 2.return的wordInfos，按weight配序
	 *			
	 */
	static function getRelativeWordsInfosByAttr($wordId, $type = 0) {
		if (empty($wordId) || empty($type)) {
			return array();
		}	

		//取一遍word1
		$params = array();
		$params['word1'] = $wordId;
		$params['type_id'] = $type;
		$word1 = self::getRelativeWords($params, '/*AttrWords-zx*/word1,word2,type_id,weight');

		//取一遍word2
		$params = array();
		$params['word2'] = $wordId;
		$params['type_id'] = $type;
		$word2 = self::getRelativeWords($params, '/*AttrWords-zx*/word1,word2,type_id,weight');

		if (!empty($word1)  && !empty($word2)) {
			$wordsTmp = array_merge($word1, $word2);
		}
		else if (!empty($word1) && empty($word2)) {
			$wordsTmp = $word1;
		} 
		else if (!empty($word2) && empty($word1)) {
			$wordsTmp = $word2;
		} 

		$words = array();
		if (empty($wordsTmp)) {
			return array();
		}
		foreach ($wordsTmp as $word) {
			$words[$word['word1']] = $word['word1'];				
			$words[$word['word2']] = $word['word2'];				
		}
		$words = array_diff($words, array($wordId));

		//取wordInfo
		$words = array_values($words);
		if (empty($words)) {
			return array();
		}
		$params = array();
		$params['word_id'] = $words;
		$params['isuse'] = 1;
		$wordInfos = self::getWordInfo($params, '/*AttrWords-zx*/word_id,word_name,label_id,isred');

		return $wordInfos;

	}

	public static function getAttrChildrenIds($begin, $end, $selectComm="*") {
		$params = array();
		$params['isuse'] = 1;
		if( $begin == $end){
			$params['label_id'] = $begin;
		}
		else{
			$params['label_id_begin'] = $begin;
			$params['label_id_end'] = $end;
		}
		$return = self::getWordInfo($params, $selectComm);
		return $return;
	}


	/*   
	 * 判断是否为品牌词
	 **/
	static function IsBrandWordsByLabel($str){
		if( substr($str,0,2) == self::$brandlabel ) {
			return true;
		}else{
			return false;
		}
	}    

	/**
	 * 取Guang的热门搜索属性词
	 * @author xuanzheng@meilishuo.com
	 */
	static function getPopularAttrWords() {
		$params = array();
		$params['page_type'] = 21;
		$params['orderby'] = " sortno asc";
		$cmsDatas = CmsIndexWelcome::getCmsData($params, "/*attrWords-zx*/title, sortno");
		$attrs = array();
		if (is_array($cmsDatas)) {
			foreach($cmsDatas as $v){
				$attrs[] = $v['title'];
			}
		}
//		$fAttrs = array('欧美', '复古', '日系','韩系', '甜美' ,'个性');
//		$attrs = array_merge($fAttrs, $attrs);
		return $attrs;
	}

}
