<?php
namespace Snake\Modules\Commerceapp;

USE \Snake\Package\Commerceapp\Helper\MedalStatistic AS MedalStatistic;
USE \Snake\Package\Twitter\Twitter AS Twitter;
USE \Snake\Package\Goods\Goods AS Goods;
USE \Snake\Package\Url\Url AS Url;
USE \Snake\Package\Manufactory\Poster AS Poster;
USE \Snake\Package\Twitterstat\TwitterStat AS TwitterStat;
USE \Snake\libs\Cache\Memcache AS Memcache;

/**
 * 活动海报页
 *
 */
class Commerce_post extends \Snake\Libs\Controller {
	
	private $page = 0;
	private $frame = 0;	
	private $type = 'dangdang';

	private $pageSize = WIDTH_PAGE_SIZE; //20
	private $frameSize = FRAME_SIZE_MAX; //6
	private $userId = 0;

    const isShowClose = 0;
    const isShowLike = 1;
    const isShowComment = 3;
    const isShowPrice = 1;

	private $allowType = array(
		'dangdang',
		'shangpin',
	);

	public function run() {
		if (!$this->_init()) {
			return FALSE;
		}
		
		$tids = $allTids = array();

		if (!empty($this->request->REQUEST['tid'])) {
			$tids = array($this->request->REQUEST['tid']);	
		}
		else {
			//TODO cache
			$tids = MedalStatistic::getCommerceTids($this->type);
			$tidsSize = count($tids);
			$singleCount = 1000;
			$times = ceil($tidsSize / $singleCount);
			$sortValue = TwitterStat::objects()->filter($tids)->get();	
			$hasLike = array();
			$other = array();
			foreach ($sortValue as $tid => $tInfo) {
				if ($tInfo['count_like'] > 0) {
					$hasLike[$tid]['count_like'] = $tInfo['count_like'];
					continue;
				}
				$other[] = $tid;
			}
			$other = array_reverse($other);
			//不是稳定排序
			uasort($hasLike, function($a, $b) { return $a['count_like'] < $b['count_like']; });

			$likeTids = array_keys($hasLike);
			$allTids = array_merge($likeTids, $other);
			$tids = $allTids;
		}
		$poster = array();
		if (!empty($tids)) {

			$offset = $this->pageSize * ($this->frameSize * $this->page + $this->frame);
			$tids = array_slice($tids, $offset, $this->pageSize);

            $posterObj = new Poster();
            $posterObj->isShowLike(self::isShowLike);
            $posterObj->isShowClose(self::isShowClose);
            $posterObj->isShowComment(self::isShowComment);
            $posterObj->isShowPrice(self::isShowPrice);
            $posterObj->setVariables($tids, $this->userId);
            $poster = $posterObj->getPoster();


			if (!empty($poster)) {
				$fields = array('url_id', 'source_link');
				$urlHelper = new Url($fields);
				$urlIds = array();
				foreach ($poster as $_pKey => $_pInfo) {
					$urlIds[] = $_pInfo['ginfo']['goods_url'];	
				}

				$goodsInfo = $urlHelper->getUrlsByUrlIds($urlIds);

				foreach ($goodsInfo as $_key => $_goodsInfo) {
					$urlId = $_goodsInfo['url_id'];	
					$goodsInfo[$urlId] = $_goodsInfo;
					unset($goodsInfo[$_key]);
				}

				foreach ($poster as $key => $info) {
					$_pTid = $info['twitter_id'];
					$url_id = $info['ginfo']['goods_url'];
					$source_link = $goodsInfo[$url_id]['source_link'];
					$frmTbl = '&';
					if (strpos($source_link, '?') === FALSE) {
						$frmTbl = '?';	
					}
					$source_link = $source_link . $frmTbl . '=' . $this->type;
					$poster[$key]['url'] = html_entity_decode($source_link);
				}
			}
		}
		
        $this->view = array(
            'tInfo' => $poster,
			'totalNum' => count($allTids),
        );  
		return TRUE;
	}

	private function _init() {
		!empty($this->request->REQUEST['page']) && $this->page = (int) $this->request->REQUEST['page'];
		!empty($this->request->REQUEST['frame']) && $this->frame = (int) $this->request->REQUEST['frame'];
		!empty($this->request->REQUEST['type']) && $this->type = trim($this->request->REQUEST['type']);	
        if (!empty($this->userSession['user_id'])) {
            $this->userId = $this->userSession['user_id']; 
        }   
		if (!in_array($this->type, $this->allowType)) {
			$this->setError(400, 40001, 'invalid type');
			return FALSE;
		}
		return TRUE;
	}
}
