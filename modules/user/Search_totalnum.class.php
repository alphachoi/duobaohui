<?php
namespace Snake\Modules\User;

USE \Snake\Libs\Cache\Memcache AS Memcache;
USE \Snake\Package\User\UserSearch AS UserSearch;
USE \Snake\Package\User\User AS User;

class Search_totalnum extends \Snake\Libs\Controller {
    //参数
    private $page = 0;
    private $wordName = ''; 

    const pageSize = 60; 
    const useCache = TRUE;
    const ExpiredTime = 600;

	public function run() {
		if (!$this->_init()) {
			return FALSE;
		}		
        $totalNum = 0;

        $maskWords = new \Snake\Package\Spam\MaskWords($this->wordName, 'DFA_register');
        $mask = $maskWords->getMaskWords();
        if (!empty($mask['maskWords'])) {
			$this->view = array(
				'totalNum' => 0,
				'showNum' => 0,
			);
            return TRUE;
        }

        if (self::useCache === TRUE) {
            $cacheHelper = Memcache::instance();
            $cacheKeyNum = 'SearchUserNum:' . $this->wordName;
            $totalNum = $cacheHelper->get($cacheKeyNum);
        }
		
		if (empty($totalNum)) {
            $searchHelper = new UserSearch();
            $searchHelper->setOffset($this->page * self::pageSize);
            $searchHelper->setLimit(self::pageSize);
            $searchHelper->setWordName($this->wordName);

            if ($searchHelper->dataProcess() === FALSE) {
                $responsePosterData = array('uInfo' => FALSE, 'totalNum' => FALSE);
                $this->view = $responsePosterData;
                return TRUE;
            }
            $totalNum = $searchHelper->getTotalNum();
            if (self::useCache === TRUE) {
                $cacheHelper->set($cacheKeyNum, $totalNum, self::ExpiredTime);
            }
		}

		is_null($totalNum) && $totalNum = 0;

		$showNum = $totalNum > 12000 ? 12000 : $totalNum;

		$this->view = array(
			'totalNum' => $totalNum,
			'showNum' => $showNum,
		);
		return TRUE;
	}

    private function _init() {
        $this->setPage();
        if (!$this->setWordName()) {
            return FALSE;
        }
        return TRUE;
    }

    private function setPage() {
        if (!empty($this->request->REQUEST['page'])) {
            $this->page = (int) $this->request->REQUEST['page'];
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
}
