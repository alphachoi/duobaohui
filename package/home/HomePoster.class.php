<?php
namespace Snake\Package\Home;

Use \Snake\Package\Session\UserSession			  AS UserSession;
Use \Snake\Libs\Base\Utilities                    AS Utilities;
Use \Snake\Package\User\User					  AS User;
Use \Snake\Package\Msg\Msg					  	  AS Msg;
Use \Snake\Package\Timeline\Timeline			  AS Timeline;
Use \Snake\Package\Timeline\TimelineDB			  AS TimelineDB;
Use \Snake\Package\Timeline\Helper\RedisUserHomePosterTimeline AS UserHomePosterTimeline;
Use \Snake\Libs\Cache\Memcache      AS Memcache;

class HomePoster {
	private $homePoster = array();
    private $frame = 0;
    private $page = 0;
	private $request = NULL;

    private $offset = 0;
    private $tids = NULL;
    private $pageSize = 0;
	private $baseUrl = NULL;

	protected $_userid = 0;
	private $white_list = array(49, 79, 81, 1178834, 4655422, 1110628, 574, 157, 88,213, 1751, 1753, 2348722, 5091506, 142, 1947660, 1713841, 1755240, 4651150, 765);

	/** 
     * constructor
     * @param $page integer 当前页
	 * @param $frame integer 当前帧
	 * @param $offset integer 偏移量
	 * @param $pageSize integer 每帧个数
	 * @param $request object 请求对象
	 *
     */
    public function __construct($page = 0, $frame = 0, $offset = 0, $pageSize = 0, \Snake\Libs\Base\HttpRequest $request = NULL) {
		$this->homePoster['page'] = $page;
		$this->homePoster['frame'] = $frame;
		$this->homePoster['offset'] = $offset;
		$this->homePoster['pageSize'] = $pageSize;
		$this->request = $request;
    }

	public function getTotalNum($userId) {
		$totalNum = UserHomePosterTimeline::getTimelineSizeByUid($userId);
		return $totalNum;
	}

	/**
	 * 我的首页海报数据流生成
	 *
	 */
	public function getHomePostersByUid($userId) {
		$totalNum = UserHomePosterTimeline::getTimelineSizeByUid($userId);
		$totalNum = $totalNum >= UserHomePosterTimeline::SIZE ? UserHomePosterTimeline::SIZE : $totalNum;
        //取出六页timeline数据放入Cache,过期时间:半小时
		$param = array();
        $cacheObj = Memcache::instance();
        $cacheKey = 'USER:TIMELINE:' . $userId;
        if ($this->homePoster['page'] == 0 && $this->homePoster['frame'] == 0) {
            $allTids = UserHomePosterTimeline::getTimelineByUid($userId, 0, 720);
            $cacheObj->set($cacheKey, $allTids, 1800);
			$param['total_last_tid'] = $allTids[0];
        }

		$tids = array();
        if ($this->homePoster['page'] <= 5) {
			$tidsCache = $cacheObj->get($cacheKey);
			$tids = array();
			if (!empty($tidsCache)) {
				$tids = array_slice($tidsCache, $this->homePoster['offset'], $this->homePoster['pageSize']);
			}
            if (empty($tids)) {
                $allTids = UserHomePosterTimeline::getTimelineByUid($userId, 0, 720);
                $cacheObj->set($cacheKey, $allTids, 1800);
                $tids = array_slice($allTids, $this->homePoster['offset'], $this->homePoster['pageSize']);
				$param['total_last_tid'] = $allTids[0];
            }
        }
		else {
            $tids = UserHomePosterTimeline::getTimelineByUid($userId, $this->homePoster['offset'], $this->homePoster['pageSize']);
        }
		
		$result = array('tids' => $tids, 'param' => $param, 'total' => $totalNum);
		return $result;
    }

	
}
