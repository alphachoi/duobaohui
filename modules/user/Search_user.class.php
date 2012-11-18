<?php
namespace Snake\Modules\User;

USE \Snake\Package\User\UserSearch AS UserSearch;
USE \Snake\Package\User\User AS User;
USE \Snake\Package\User\UserCommonList AS UserCommonList;
USE \Snake\Libs\Cache\Memcache AS Memcache;
USE \Snake\Package\mall\Mall AS Mall;
USE \Snake\Libs\Base\Utilities AS Utilities;

class Search_user extends \Snake\Libs\Controller {
	
	//参数
	private $page = 0;
	private $frame = 0;
	private $wordName = '';
	private $userId = 0;

	const pageSize = 20;
	const pageFrame = 6;
	const useCache = TRUE;
	const ExpiredTime = 600;

	public function run() {
		if (!$this->_init()) {
			return FALSE;
		}

		$uids = $poster = array();
		$totalNum = 0;

		$maskWords = new \Snake\Package\Spam\MaskWords($this->wordName, 'DFA_register');
		$mask = $maskWords->getMaskWords();
		if (!empty($mask['maskWords'])) {
			$this->view = array(
				'user' =>  
					array(
						'uInfo' => array(), 
						'totalNum' => 0,
					),
			);
			return TRUE;		
		}

		if (self::useCache === TRUE) {
			$cacheHelper = Memcache::instance();
			$cacheKey = 'SearchUser:' . $this->wordName . ':' . $this->page . ':' . $this->frame;
			$cacheKeyNum = 'SearchUserNum:' . $this->wordName;
			$uids = $cacheHelper->get($cacheKey);
			$totalNum = $cacheHelper->get($cacheKeyNum);
		}
		$userCommon = new UserCommonList();
		//self::useCache的作用是立刻停止从cache读取数据
		if (!empty($uids) && !empty($totalNum) && self::useCache === TRUE) {
			$poster = $userCommon->getSearchCommonInfo($uids, $this->userId);	
		}
		else {
			$searchHelper = new UserSearch();
			$offset = ($this->page * self::pageFrame + $this->frame) * self::pageSize;
			$searchHelper->setOffset($offset);
			$searchHelper->setLimit(self::pageSize);
			$searchHelper->setWordName($this->wordName);

			if ($searchHelper->dataProcess() === FALSE) {
				$responsePosterData = array('uInfo' => FALSE, 'totalNum' => FALSE);
				$this->view = $responsePosterData;
				return TRUE;
			}
			$uids = $searchHelper->getUids();
			$uids = $this->getPopUser($this->wordName, $uids);
			$totalNum = $searchHelper->getTotalNum();
			$userCommon = new UserCommonList();
			$poster = $userCommon->getSearchCommonInfo($uids, $this->userId);
			if (self::useCache === TRUE) {
				$cacheHelper->set($cacheKey, $uids, self::ExpiredTime);
				$cacheHelper->set($cacheKeyNum, $totalNum, self::ExpiredTime);
			}
		}

		is_null($totalNum) && $totalNum = 0;
		$this->view = array(
			'user' => 
				array(
					'uInfo' => array_values($poster), 
					'totalNum' => $totalNum,
				),
		);

		return TRUE;
	}

	private function _init() {
		$this->setPage();
		$this->setFrame();
		if (!$this->setWordName()) {
			return FALSE;
		}
		if (!empty($this->userSession['user_id'])) {
			$this->userId = $this->userSession['user_id'];
		}
		return TRUE;
	}

	private function setPage() {
		if (!empty($this->request->REQUEST['page'])) {
			$this->page = (int) $this->request->REQUEST['page'];	
		}
		return TRUE;
	}

	private function setFrame() {
		if (!empty($this->request->REQUEST['frame']) && $this->request->REQUEST['frame'] < self::pageFrame) {
			$this->frame = (int) $this->request->REQUEST['frame']; 
		}
		return TRUE;
	}

	private function setWordName() {
		$wordName = htmlspecialchars_decode(urldecode($this->request->REQUEST['word_name']));
		if (trim($wordName) === '') {
			$this->setError(400, 20150, 'empty wordName');
			return FALSE;
		}
		$this->wordName = $wordName;
		return TRUE;
	}

	/**
	 * 获取品牌商，放置头部
	 * @param $wordName 搜索词
	 * @param $uids array 搜索出的用户编号
	 *
	 */
	private function getPopUser($wordName, $uids) {
		$mall = new Mall();
		$userResult = $mall->getMallBySearchWord($wordName, 'user_id');
		if (empty($userResult)) {
			return $uids;
		}		
		$popIds = Utilities::DataToArray($userResult, 'user_id');
		foreach ($popIds as $pId) {
			if ($this->page == 0 && $this->frame == 0) {
				array_unshift($uids, $pId);	
				$uids = array_unique($uids);
			}
			else {
				if (in_array($pId, $uids)) {
					foreach ($uids as $key => $uid) {
						if ($pId == $uid) {
							unset($uids[$key]);	
						}
					}	
				}
			}
		}
		return $uids;
	}
}
