<?php
namespace Snake\Modules\Goods;

Use Snake\Package\Goods\MatchShow;
Use Snake\Libs\Cache\Memcache;
Use \Snake\Package\Goods\Tag;

class Dressing_match_totalnum extends \Snake\Libs\Controller {
	private $frame = 0;
	private $page = 0;
	private $offset = 0;
	private $useCache = TRUE;
	private $userId = 0;
	const pageSize = WIDTH_PAGE_SIZE;
	const maxFrame = FRAME_SIZE_MAX;
	const isShowClose = 0;
	const isShowLike = 1;

	public function run () {
		/*
		$cacheHelper = Memcache::instance();
		$cacheKeyForPosters = "CacheKey:dressing_match_totalnum";
		$responsePosterData = $cacheHelper->get($cacheKeyForPosters);
		$matchShow = new MatchShow();	
		$matchShow->totalNumDataProcess();
		$totalNum = $matchShow->getTotalNum();

		$responsePosterData = array('totalNum' => $totalNum);
		$cacheHelper->set($cacheKeyForPosters, $responsePosterData, 600);
		 */
		$responsePosterData = array('totalNum' => 10800);
		$this->view = $responsePosterData;
		return TRUE;
	}
}
