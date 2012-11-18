<?php

namespace Snake\Package\Seo;
Use Snake\Package\Manufactory\Poster;
Use Snake\Package\Search\Search AS Search;
Use Snake\Libs\Cache\Memcache AS Memcache;

class DictSearch {

    private static $instance = NULL;
    private $offset = 0;
    private $sortby = 'seo';
    private $max = 2000;
    private $pageSize = 120;

    /**
     * @return SeoModel Object
     */
    public static function getInstance() {
        if (empty(self::$instance)) {
            self::$instance = new SeoModel();
        }
        return self::$instance;
    }

    public function __construct() {
        
    }

    private function setPageSize($pageSize) {
        $this->pageSize = $pageSize;
        return TRUE;
    }

    private function setOffset($offset) {
        $this->offset = $offset;
        return TRUE;
    }

    public function searchResult($searchKey) {
        $data = array();
        $cache = Memcache::instance();
        $cacheKey = 'SNAKE:DICT:' . $searchKey;

        $data = $cache->get($cacheKey);
        if (!empty($data['matches'])) {
            return $data;
        }
        $searchObj = new Search();
        $sc = $searchObj->_getSphinxClient();

        $sc->SetLimits($this->pageSize * $this->offset, $this->pageSize, $this->max);
		//$sc->SetFilter('goods_title', array($searchKey), false); 
        $sc->SetSortMode(SPH_SORT_EXTENDED, '@relevance DESC, @weight DESC');
        $sc->SetMatchMode(SPH_MATCH_EXTENDED);
        $data = $searchObj->queryViaValidConnection($searchKey, 'goods_seo');
        if (!empty($data)) {
            $cache->set($cacheKey, $data, 30*24*3600);
        }
        return $data;
    }
	//完全匹配
    public function searchResultNoCache($searchKey) {
        $data = array();
        $searchObj = new Search();
        $sc = $searchObj->_getSphinxClient();

        $sc->SetLimits($this->pageSize * $this->offset, $this->pageSize, $this->max);
		//$sc->SetFilter('goods_title', array($searchKey), false); 
        $sc->SetSortMode(SPH_SORT_EXTENDED, '@relevance DESC, @weight DESC');
        $sc->SetMatchMode(SPH_MATCH_EXTENDED);
        $data = $searchObj->queryViaValidConnection($searchKey, 'goods_seo');
        return $data;
    }
	
	//模糊匹配
	public function searchResultAny($searchKey) {
        $data = array();
        $searchObj = new Search();
        $sc = $searchObj->_getSphinxClient();
        $sc->SetLimits($this->pageSize * $this->offset, $this->pageSize, $this->max);
        $sc->SetSortMode(SPH_SORT_EXTENDED, '@relevance DESC, @weight DESC');
        $sc->SetMatchMode(SPH_MATCH_ANY);
        $data = $searchObj->queryViaValidConnection($searchKey, 'goods_seo');
        return $data;
	}

	public function serarchResultPhrase($searchKey, $index="good_seo") {
        $data = array();
        $searchObj = new Search();
        $sc = $searchObj->_getSphinxClient();
		if ($index == 'goods_title') {
			$sc->SetFilter('twitter_show_type', array(0), true);	
		}
        $sc->SetLimits($this->pageSize * $this->offset, $this->pageSize, $this->max);
		//$sc->SetFilter('goods_title', array($searchKey), false); 
        $sc->SetSortMode(SPH_SORT_EXTENDED, '@relevance DESC, @weight DESC');
        $sc->SetMatchMode(SPH_MATCH_PHRASE);
        $data = $searchObj->queryViaValidConnection($searchKey, $index);
        return $data;
	}

	public function getTwitterNotDelete($wordname) {
		$wordname = trim($wordname);
        $tids = array();
        $cache = Memcache::instance();
        $cacheKey = 'SNAKE:DICT:TID_1' . $wordname;
        $tids = $cache->get($cacheKey);
        if (empty($tids)) {
		    $ret = $this->searchResultNoCache($wordname);
			if (empty($ret['matches'])) {
			    return 0;
   		    }
	    	$tids = \Snake\Libs\Base\Utilities::DataToArray($ret['matches'], 'attrs');
		    $tids = \Snake\Libs\Base\Utilities::DataToArray($tids, 'twitter_id');
			$cache->set($cacheKey, $tids, 30*24*3600);
		}
        $posterObj = new Poster();
        $posterObj->isShowLike(1);
        $posterObj->isShowClose(1);
		$posterObj->isShowPrice(1);
        $posterObj->setVariables($tids, '');
        $poster = $posterObj->getPoster();
		if (empty($poster)) {
			return 1;	
		}
		else {
			return $poster;	
		}
		
	}

}

?>
