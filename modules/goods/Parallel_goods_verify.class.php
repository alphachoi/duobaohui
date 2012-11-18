<?php
namespace Snake\Modules\Goods;
Use \Snake\Package\Goods\GoodsVerify;
Use Snake\Package\Base\IdentityObject;

/**
 * 单推页面的主页面展现
 * @package goods 
 * @author weiwang
 * @since 2012.08.24
 * @example curl snake.mydev.com/goods/share_main?tid=74090164
 */
class Parallel_goods_verify extends \Snake\Libs\Controller{
	public function run() {
		$tids = isset($this->request->REQUEST['tids']) ? $this->request->REQUEST['tids'] : array();
		$fields = isset($this->request->REQUEST['fields']) ? $this->request->REQUEST['fields'] : "";
		$col = array("verify_stat","verify_twitter_id");
		if (!empty($fields)) {
			$col = $fields;
		}

		$identityObject = new IdentityObject();
		$identityObject->field('verify_twitter_id')->in($tids);
		$identityObject->col($col);
		$goodsVerifyAssembler =  new GoodsVerify();
		$goodsVerify = $goodsVerifyAssembler->getGoodsVerify($identityObject);
		$goodsVerify = \Snake\Libs\Base\Utilities::changeDataKeys($goodsVerify, 'verify_twitter_id');
		$this->view = $goodsVerify;
	}
}
