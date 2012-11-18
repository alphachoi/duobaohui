<?php
namespace Snake\Modules\Goods;

Use Snake\Package\Act\Act;
/**
 * 单推页面的主页面展现
 * @package goods
 * @author weiwang
 * @since 2012.09.03
 * @example curl 
 */
class Parallel_act extends \Snake\Libs\Controller{

	public function run() {
		$atids = isset($this->request->REQUEST['atids']) ? $this->request->REQUEST['atids'] : array();

		if (!empty($atids)) {
			$act = new Act();
			$column = 't1.activity_id,t1.activity_title,t2.twitter_id';
			$param['status'] = 1;
			$actInfo = $act->getActInfoByTids($atids, $column, $param, false, 'twitter_id');
		}

		$this->view = $actInfo;
		return TRUE;
	}

}
