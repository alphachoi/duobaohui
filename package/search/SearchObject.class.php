<?php
namespace Snake\Package\Search;
/**
 * 对search操作封装的一个文件
 * @author xuanzheng@meilishuo.com
 * @version alpha
 */

class SearchObject extends Search{
	private $sphinxClient = NULL;
	private $limit = array();
	private $filters = array();
	private $filterRanges = array();
	private $filterFloatRanges = array();
	private $sortMode = SPH_SORT_RELEVANCE;
	private $sortBy = '';
	private $index = '';
	private $matchMode = SPH_MATCH_ANY;
	private $searchResult = array();
	private $maxQueryTime = 4000;
	private $searchType = "normal";
	private $useCache = FALSE;
	private $keywords = '';
	const max = 12000;
	const daysFilter = 201;

	function __construct($searchType = "normal") {
		parent::__construct();
		$this->searchType = $searchType;
		if ($searchType == "cpc") {
			$this->sphinxClient = parent::_getSecondSphinxClient();
		}
		else {
			$this->sphinxClient = parent::_getSphinxClient();
		} 
	}

	public function __tostring() {
		//排序
		uasort($this->filters, array($this, "cmp"));
		uasort($this->filterRanges, array($this, "cmp"));
		uasort($this->filterFloatRanges, array($this, "cmp"));
		$filtersString = print_r($this->filters, TRUE);	
		$filterRangesString = print_r($this->filterRanges, TRUE);	
		$filterFloatRangesString = print_r($this->filterFloatRanges, TRUE);	
		$keywordsString = print_r($this->keywords, TRUE);
		$limitString = print_r($this->limit, TRUE);
		$key = "filters:{$filtersString}_filterRanges:{$filterRangesString}_filterFloatRanges:{$filterFloatRangesString}_keyWords:{$keywordsString}_limit:{$limitString}";
		return $key;
	}

	public function cmp($a, $b) {
		$bigger = 0;
		if ($a['attribute'] > $b['attribute']) {
			$bigger = 1;
		}	
		else if ($a['attribute'] === $b['attribute']) {
			$bigger = 0;
		}
		else {
			$bigger = -1;
		}
		return $bigger;
	}

	/**
	 * 调用获取SearchObject
	 * @author xuanzheng@meilishuo.com
	 */
//	static public function getSearchClient() {
//		return new SearchObject();
//	}

	/**
	 * 设置limit
	 * @author xuanzheng@meilishuo.com
	 */
	public function setLimit($offset, $limit, $maxMatches = 12000, $cutoff = 0) {
		if ($limit <= 0) {
			$limit = 1;
		}
		$newLimit = array(
			'offset' => $offset,
			'limit' => $limit,
			'maxMatches' => $maxMatches,
			'cutoff' => $cutoff
		);
		$this->limit = $newLimit;
		return TRUE;
	}

	/**
	 * 设置过滤条件，搜索出values中指定的内容
	 * @author xuanzheng@meilishuo.com
	 * @param string attribute
	 * @param array values
	 * @param bool exclude
	 */
	public function setFilter($attribute, $values, $exclude = false) {
		if (empty($attribute)) {
			return FALSE;
		}
		$filter = array('attribute' => $attribute, 'values' => $values, 'exclude' => $exclude);
		array_push($this->filters, $filter);
		return TRUE;	
	}	

	/**
	 * 设置过滤条件，搜索出min,max中指定的范围
	 * @author xuanzheng@meilishuo.com
	 * @param string attribute
	 * @param int min 
	 * @param int max 
	 * @param bool exclude
	 */
	public function setFilterRange($attribute, $min, $max, $exclude = false) {
		if (empty($attribute)) {
			return FALSE;
		}
		$filterRange = array('attribute' => $attribute, 'min' => intval($min), 'max' => intval($max), 'exclude' => $exclude);
		array_push($this->filterRanges, $filterRange);
		return TRUE;
	}


	/**
	 * 设置过滤条件，搜索出min,max中指定的范围
	 * @author xuanzheng@meilishuo.com
	 * @param string attribute
	 * @param float min 
	 * @param float max 
	 * @param bool exclude
	 */
	public function setFilterFloatRange($attribute, $min, $max, $exclude = false) {
		if (empty($attribute)) {
			return FALSE;
		}
		$filterFloatRange = array('attribute' => $attribute, 'min' => (float)$min, 'max' => (float)$max, 'exclude' => $exclude);
		array_push($this->filterFloatRanges, $filterFloatRange);
		return TRUE;
	}



	/**
	 * 设置排序模式,详见文档
	 * @author xuanzheng@meilishuo.com
	 * @param NULL define in API
	 * @param string sortBy
	 */
	public function setSortMode($mode, $sortBy) {
		if (empty($mode)) {
			return FALSE;
		}
		$this->sortMode = $mode;
		$this->sortBy = $sortBy;
		return TRUE;
	}

	/**
	 * SetMatchMode
	 *
	 */
	public function setMatchMode($matchMode) {
		if (empty($matchMode)) {
			return FALSE;
		}
		$this->matchMode = $matchMode;	
		return TRUE;
	}

	/**
	 * 设置所用的搜索索引
	 * @param string 
	 */
	public function setIndex($index) {
		if (empty($index)) {
			return FALSE;
		}
		$this->index = $index;
		return TRUE;
	}

	public function setMaxQueryTime($maxQueryTime) {
		$this->maxQueryTime = $maxQueryTime;
		return TRUE;
	}


	public function setUseCache($useCache = TRUE) {
		$this->useCache = $useCache;
		return TRUE;
	}

	/**
	 * 开始搜索
	 * @author xuanzheng@meilishuo.com
	 * @param keywords
	 */
	public function search($keywords) {
		$this->keywords = $keywords;
		$this->conditionLoad();
		if ($this->useCache) {
			$result = $this->searchFromCache($this);		
			if (empty($result['matches'])) {
				$result = $this->searchFromSphinx($keywords, $this->index);
				$this->putSearchResultIntoCache($result);
			}
		}
		else {
			$result = $this->searchFromSphinx($keywords, $this->index);
		}
		$this->searchResult = $result;	
		return TRUE;
	}

	private function searchFromCache($searchObject) {
		$searchResult = SearchCache::getSearch($this);	
		return $searchResult;
	}

	private function putSearchResultIntoCache($searchResult) {
		$bool = SearchCache::setSearch($this, $searchResult);
		return $bool;
	}

	private function searchFromSphinx($keywords, $index) {
		if ($this->searchType == 'cpc') {
			$result = parent::secondQueryViaValidConnection($keywords, $index);
		}
		else  {
			$result = parent::queryViaValidConnection($keywords, $index);
		}
		return $result;
	}

	public function getSearchResult() {
		return $this->searchResult;	
	} 

	public function conditionLoad() {
		$this->conditionReset();
		$this->maxQueryTimeLoad();
		$this->limitLoad();
		$this->filtersLoad();
		$this->filterRangeLoad();
		$this->filterFloatRangesLoad();
		$this->sortModeLoad();
		$this->matchModeLoad();
		return TRUE;
	}

	public function resetFilters() {
		$this->sphinxClient->ResetFilters();	
		$this->filters = array();
		$this->filterRanges = array();
		$this->filterFloatRanges = array();
		return TRUE;
	}

	/**
	 * TODO 加入limit reset
	 */
	private function conditionReset() {
		$this->sphinxClient->ResetFilters();	
		return TRUE;
	}

	private function maxQueryTimeLoad() {
		$this->sphinxClient->SetMaxQueryTime($this->maxQueryTime);
		return TRUE;
	}

	private function matchModeLoad() {
		$this->sphinxClient->SetMatchMode($this->matchMode);
		return TRUE;
	}

	private function sortModeLoad() {
		$this->sphinxClient->SetSortMode($this->sortMode, $this->sortBy);
		return TRUE;
	}

	private function limitLoad() {
		if (empty($this->limit)) {
			return FALSE;    
		}
		if(!empty($this->limit) && !isset($this->limit['maxMatches'])) {            
			$this->limit['maxMatches'] = self::max;        
		}           
		$this->sphinxClient->SetLimits($this->limit['offset'], $this->limit['limit'], $this->limit['maxMatches'], $this->limit['cutoff']);
		return TRUE;
	}

	private function filtersLoad() {
		foreach ($this->filters as $filter) {
			$this->sphinxClient->SetFilter($filter['attribute'], $filter['values'], $filter['exclude']);	
		}
		return TRUE;
	}

	private function filterRangeLoad() {
		foreach ($this->filterRanges as $filterRange) {
			$this->sphinxClient->SetFilterRange($filterRange['attribute'], $filterRange['min'], $filterRange['max'], $filterRange['exclude']);
		}
		return TRUE;	
	}

	private function filterFloatRangesLoad() {
		foreach ($this->filterFloatRanges as $filterFloatRange) {
			$this->sphinxClient->SetFilterFloatRange($filterFloatRange['attribute'], $filterFloatRange['min'], $filterFloatRange['max'], $filterFloatRange['exclude']);	
		}
		return TRUE;
	}


	public function getSearchString($wordParams) {
		if (empty($wordParams['word_id']) && empty($wordParams['word_name'])) {
			return FALSE;
		}
		$params = array();
		$params['word_name'] = $wordName;
		$params['isuse'] = 1;
		$wordInfo = AttrWords::getWordInfo($params, "/*searchGoods-zx*/word_id,word_name");
		if (!empty($wordInfo)) {
			$searchKeyArr = AttrWords::getSearchString($wordInfo[0]['word_id'], $wordName);
		}
		else{
			$searchKeyArr = array("{$wordName}");	
		}
		$searchString = "(" . implode(")|(", $searchKeyArr) . ")";
		$this->searchString = $searchString;
		return $searchString;
	}

	public function getIndex() {
		return $this->index;
	}

}
