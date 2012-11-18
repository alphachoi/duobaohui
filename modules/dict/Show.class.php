<?php
/**
 * book页
 */
namespace Snake\Modules\Dict;
Use \Snake\Libs\Base\SnakeLog;
use Snake\Package\Seo\SeoModel;
use Snake\Package\Seo\DictSearch;
Use Snake\Package\Manufactory\Poster;
Use Snake\Libs\Cache\Memcache AS Memcache;

class Show extends \Snake\Libs\Controller {

    const isShowClose = 1;
    const isShowLike = 1;
	const isShowPrice = 1;
	private $args;
	private $wordname;
	private $reason = 1;
	private $tids;

    public function run() {
		$this->head = 200;
		$isTrue = $this->dispatcher();
		if ($this->reason == 3) {
			$this->view = array('code' => 302, 'message' => $isTrue);
			return	FALSE;
		}
		if (!$isTrue) {
			$this->errorMessage(400, 'bad word');	
			return FALSE;
		}
		$this->getTids();
		if (empty($this->tids)) {
			$this->errorMessage(400, 'no data');	
			return FALSE;
		}
		$poster = $this->getData();
		if (empty($poster)) {
			$this->errorMessage(400, 'data delete');	
			return FALSE;
		}
		$this->view = array('tInfo' => $poster, 'totalNum' => 90, 'wordname' => $this->wordname);
    }

	//获得海报墙数据
	private function getData() {
        $posterObj = new Poster();
        $posterObj->isShowLike(self::isShowLike);
        $posterObj->isShowClose(self::isShowClose);
		$posterObj->isShowPrice(self::isShowPrice);
        $posterObj->setVariables($this->tids, '');
        $poster = $posterObj->getPoster();
		if (empty($poster)) {
			return false;
		}
		//确保所有的推都没有被删除
        foreach($poster as $key=> $info){
            if ($info['twitter_show_type'] == 9) unset($poster[$key]);
        }
		if (!empty($poster)) {
        	$poster = array_values($poster);
		}
		return $poster;
	}

	//获取推id
	private  function getTids() {
        $cache = Memcache::instance();
        $cacheKey = 'SNAKE:DICT:TID_1' . $this->wordname;
        $this->tids = $cache->get($cacheKey);
		$cacheKey2 = 'SNAKE:DICT:TID_2' . $this->wordname;
		//如果完全匹配cache中没有数据,取模糊匹配中cache
        if (empty($this->tids)) {
			$this->tids = $cache->get($cacheKey2);
		}
		//如果二者cache都无数据，先完全匹配
		if (empty($this->tids)) {
			$search = new DictSearch();
		    $ret = $search->searchResultNoCache($this->wordname);
			if (!empty($ret['matches'])) {
				$this->tids = \Snake\Libs\Base\Utilities::DataToArray($ret['matches'], 'attrs');
				$this->tids = \Snake\Libs\Base\Utilities::DataToArray($this->tids, 'twitter_id');
				$cache->set($cacheKey, $this->tids, 30*24*3600);
			}
		}
		//搜索任然搜索不到,模糊搜索
		if (empty($this->tids)) {
			$ret = $search->searchResultAny($this->wordname);
			if (empty($ret['matches'])) {
				$this->reason = 5;
				return  FALSE;
			}
			$this->tids = \Snake\Libs\Base\Utilities::DataToArray($ret['matches'], 'attrs');
			$this->tids = \Snake\Libs\Base\Utilities::DataToArray($this->tids, 'twitter_id');
			$cache->set($cacheKey2, $this->tids,  30*24*3600);
		}
		return TRUE;
	}

	private function dispatcher() {
		$this->args = $this->request->path_args[0];	
		if (!is_numeric($this->args)) {
			$this->wordname = trim($this->args);  
			$param['word_name'] = $this->wordname;
			$isTrueWord = SeoModel::getInstance()->selectSeoWords($param, "id,type,elite");
			if (empty($isTrueWord) || $isTrueWord[0]['type'] != 1) {
				$this->reason = 2;
				return false;
			}
		}
		else {
			$id = (int) $this->args;
			$params['id'] = $id;
			$seoword = SeoModel::getInstance()->selectSeoWords($params, "/*seo-sunsl*/word_name,type,elite");
			if(empty($seoword)) $this->reason = 2;

			$this->wordname = $seoword[0]['word_name'];
			//dict 词302跳转
			if ($seoword[0]['elite'] == 1) {
				$book_id = $this->goTrueBook($seoword[0]['word_name']);
				if (!empty($book_id)) {
					$this->reason = 3;
					return $book_id;
				}
				else {
					$this->reason = 4;
					return false;
				}
			}
			if (1 != $seoword[0]['type']) {
				$this->reason = 2;
				return false;
			}
		}
		return true;
	}

	private function goTrueBook($wordname) {
		if (empty($wordname)) {
			return false;	
		}
		$params['word_name'] = $wordname;
		$params['type'] = 1;
		$params['elite'] = 0;
		$bookid = SeoModel::getInstance()->selectSeoWords($params,'id');	
		if (!empty($bookid)) {
			return $bookid[0]['id'];
		}
	}

    private function errorMessage($code, $message) {
        $this->head = 400;
        $this->view = array('code' => $code, 'message' => $message);
        return TRUE;
    }

}

?>
