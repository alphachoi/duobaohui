<?php
namespace Snake\Modules\Goods;
Use \Snake\Package\Cpc\Cpc;
Use Snake\Package\Base\IdentityObject;

/**
 * 单推页面的主页面展现
 * @package goods 
 * @author weiwang
 * @since 2012.08.24
 * @example curl snake.mydev.com/goods/share_main?tid=74090164
 */
class Parallel_cpc extends \Snake\Libs\Controller{
	public function run() {
		$tids = isset($this->request->REQUEST['tids']) ? $this->request->REQUEST['tids'] : array();
		$fields = isset($this->request->REQUEST['fields']) ? $this->request->REQUEST['fields'] : "";

		$col = array('goods_url','twitter_id');
		if (!empty($fields)) {
			$col = $fields;
		}
		$identityObject = new \Snake\Package\Base\IdentityObject();
		$identityObject->field('twitter_id')->in($tids);
		$identityObject->col($col);
		$cpcAssembler = new Cpc();
		$cpcs = $cpcAssembler->getCpcInfo($identityObject);
		$cpcs = \Snake\Libs\Base\Utilities::changeDataKeys($cpcs, 'twitter_id');
		$this->view = $cpcs;
	}
}
