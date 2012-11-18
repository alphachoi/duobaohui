<?php
namespace Snake\Modules\Goods;

Use Snake\Package\Goods\NewestViewTrace;

class Newest_view_trace_num extends \Snake\Libs\Controller {


	private $newestViewTrace = NULL;
	private $firstTid = 0;
	private $response = array('change_size' => 0);

	public function run() {
		$this->initialize();
//		if (empty($this->firstTid)) {
//			return $this->response;
//		}
		$changeSize = $this->getChangeSizeFromNewest($this->firstTid);
		if (!empty($changeSize)) {
			$this->response = array('change_size' => (int)$changeSize );	
		}	
		$this->view = $this->response;
		return TRUE;
	}


	private function initialize() {
		$this->newestViewTrace = new NewestViewTrace();	
		$this->firstTid = $this->newestViewTrace->getFirstTidFromCache();
		return TRUE;
	}

	private function getChangeSizeFromNewest($tid) {
		if (empty($tid)) { 
		   	return FALSE;
		}
		return $this->newestViewTrace->getChangeSizeFromTid($tid);
	}

	
}
