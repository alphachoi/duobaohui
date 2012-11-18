<?php
namespace Snake\Package\Goods;

Use Snake\Package\Search\Search;
Use Snake\Package\Search\UserExpr;
Use Snake\Package\Search\MainExpr;
Use Snake\Package\Search\BracketExpr;
Use Snake\Package\Search\BusExpr;
Use Snake\Package\Search\CpcBusExpr;
Use Snake\Package\Search\CpcBusTestExpr;
Use Snake\Package\Search\CpcUserTestExpr;
Use Snake\Package\Search\CataExpr;
Use Snake\Libs\Cache\Memcache;
Use Snake\Package\Goods\AttrWords;
Use Snake\Package\Recommend\Recommend;
Use Snake\Package\Twitter\Twitter;
Use Snake\Package\Group\Groups;

class AttributeBase {

	protected $attrs = NULL;
	protected $offset = NULL;
	protected $pageSize	= 20;
	protected $orderby = "weight";
	protected $excludeIds = array();
	protected $filter = array();
	protected $expr	= NULL;
	protected $daysFilter = 200;
	protected $tids = array();
	protected $totalNum = 0;
	protected $maxQueryTime = 8000;
	protected $filterWord = '';

	public function getGoodsByAttr($attrs, $offset = 0, $pageSize = 20, $orderby = 'weight', $excludeIds = array(), $filter = array(), $expr = "") {
		$this->attrs = $attrs;
		$this->offset = $offset;
		$this->pageSize = $pageSize;
		$this->orderby = $orderby;
		$this->excludeIds = $excludeIds;
		$this->filter = $filter;
		$this->excludeIds = $excludeIds;
		$this->expr = $expr;

		$searchResult = $this->getGoodsByAttrFromSearch();
		return $searchResult;
	}

	protected function getGoodsByAttrFromSearch() {
		if (empty($this->attrs)) {
			return array();
		}
		$cacheKey = implode("_", $this->attrs) . "_" . $this->offset . "_" . $this->pageSize . "_" . $this->orderby . "_" . $this->filter['verify_stat']['field'];
		$cache = Memcache::instance();
		$useCache = 0;
		$hasPriceFilter = 0;
		foreach ($this->filter as $filter) {
			if ($filter['field'] == "goods_price"){
				$hasPriceFilter = 1;	
			} 
		}
		if ( !$hasPriceFilter ) {
			$useCache = 1;
			$cacheContent = $cache->get($cacheKey);
			if (!empty($cacheContent['matches'])) {
				return $cacheContent;
			}
		}


		$max = 12000;
		$searchObj = new Search();
		$sc = $searchObj->_getSphinxClient();
		$sc->SetMaxQueryTime($this->maxQueryTime);
		$size = 20;
		if (empty($this->pageSize)){
			$size = 1;
		}
		else{
			$size = $this->pageSize;
		}
		$sc->SetLimits($this->offset * $size, $size, $max);

		//设置过滤
		//目前删除操作是将索引中goods_id_attr置为0,所以搜索的时候将goods_id_attr=0的过滤掉
		$sc->SetFilter("goods_id_attr", array(0), TRUE);
		//TODO 过滤黑名单中的推,不知道在这里过滤的效率高，还是在建索引的时候过滤掉效率高。有待测试.
		$this->excludeIds[] = 0;
		if( !empty($this->excludeIds) ) {
			$sc->SetFilter('twitter_id', $this->excludeIds, true);
		}

		//如果为id，设置过滤(过滤时间而已)
		$searchTime = time();
		$begin_time = $searchTime - $this->daysFilter * 86400;
		if (count($filter)) {
			foreach ($this->filter AS $filter) {
				if($filter['field'] == "goods_price"){
					$sc->SetFilterFloatRange($filter['field'], $filter['from'], $filter['to']);
				}
				else{
					$sc->SetFilterRange($filter['field'], $filter['from'], $filter['to']);
				}
			}
		}

		$sc->SetMatchMode(SPH_MATCH_EXTENDED);
		$keywords = "";
		foreach ($this->attrs as $key => $word) {
			$word = trim($word);
			if (!empty($word)) {
				$keywords = $keywords . "($word)|";
			}
		}
		$keywords = rtrim($keywords, "|");

		$exprBuilder = new CataExpr(new BracketExpr(new BusExpr(new UserExpr(new MainExpr(), ""), "+"), ""), "*");
		$exprBuilder->setSearchKey($keywords);
		$searchExpr = $exprBuilder->getExpr();

		if ($this->orderby == 'id') {
			$sc->SetSortMode(SPH_SORT_EXTENDED, 'goods_id_attr DESC');
			$sc->SetLimits($this->offset * $size, $size, $this->offset * $size + $size , $max);
			//设置关键词和搜索方式
			$returnInfo = $searchObj->queryViaValidConnection($keywords, 'goods_id_distributed');
			$searchObj->adjustResult($returnInfo, $max);
			if ($useCache && !empty($returnInfo['matches'])) {
				$cache->set($cacheKey, $returnInfo, 600);
			}
			return $returnInfo;
		}
		else if ($this->orderby == 'like') {
			$sc->SetSortMode( SPH_SORT_EXPR, 'rank_like' );
		}
		//如果是weight,设置时间为2个月
		else if ($this->orderby == 'weight'){
			$sc->SetFilterRange('goods_author_ctime', $begin_time, $searchTime);
			if (empty($this->expr)) {
				$sc->SetSortMode(SPH_SORT_EXPR, "$searchExpr");
			}
			else {
				$sc->SetSortMode(SPH_SORT_EXPR, $this->expr);
			}
		}
		
		//设置关键词和搜索方式
		$returnInfo = $searchObj->queryViaValidConnection($keywords, 'goods_id_dist');

		$cpcExprBuilder = new CataExpr(new BracketExpr(new CpcBusExpr(new UserExpr(new MainExpr(), ""), "+"), ""), "*");
		$cpcExprBuilder->setSearchKey($keywords);
		$searchCpcExpr = $cpcExprBuilder->getExpr();

		$offset = $sc->_offset;
		$sc->SetLimits(0, $max - 1, $max);
		$sc->SetSortMode(SPH_SORT_EXPR, "$searchCpcExpr");
		$sc->SetFilter('verify_stat', array(1,2));
		$cpcRes = $searchObj->queryViaValidConnection($keywords, "goods_id_business");
		$mergeRes = $searchObj->mergeCpc($returnInfo, $cpcRes, $offset);
		if ($this->pageSize == 0) {
			unset($mergeRes['matches']);
		}
		if ($useCache && !empty($mergeRes['matches'])) {
			$cache->set($cacheKey, $mergeRes, 600);
		}
		return $mergeRes;
	}
}
