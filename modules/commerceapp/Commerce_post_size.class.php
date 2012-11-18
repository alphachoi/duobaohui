<?php
namespace Snake\Modules\Commerceapp;

USE \Snake\Package\Commerceapp\Helper\MedalStatistic AS MedalStatistic;

/**
 * 活动海报页
 *
 */
class Commerce_post_size extends \Snake\Libs\Controller {
	
	private $type = 'dangdang';

	private $allowType = array(
		'dangdang',
		'shangpin',
	);

	public function run() {
		if (!$this->_init()) {
			return FALSE;
		}	

		$count = MedalStatistic::lSize($this->type);

		empty($count) && $count = 0;

		$this->view = array('totalNum' => $count);
	}

	private function _init() {
		!empty($this->request->REQUEST['type']) && $this->type = trim($this->request->REQUEST['type']);	
		if (!in_array($this->type, $this->allowType)) {
			$this->setError(400, 40001, 'invalid type');
			return FALSE;
		}
		return TRUE;
	}
}
