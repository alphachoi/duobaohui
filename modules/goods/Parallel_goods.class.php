<?php
namespace Snake\Modules\Goods;
Use \Snake\Package\Goods\Goods;

/**
 * 单推页面的主页面展现
 * @package goods 
 * @author weiwang
 * @since 2012.08.24
 * @example curl snake.mydev.com/goods/share_main?tid=74090164
 */
class Parallel_goods extends \Snake\Libs\Controller{
	public function run() {
		$gids = isset($this->request->REQUEST['gids']) ? $this->request->REQUEST['gids'] : array();
		$fields = isset($this->request->REQUEST['fields']) ? $this->request->REQUEST['fields'] : "";

		$col = array('goods_id','goods_price','goods_title','goods_pic_url','goods_url');
		if (!empty($fields)) {
			$col = $fields;
		}
		$goodsAssembler = new Goods($col);
		$goodsInfo = $goodsAssembler->getGoodsByGids($gids);
		$goodsInfo = \Snake\Libs\Base\Utilities::changeDataKeys($goodsInfo, 'goods_id');
		$this->view = $goodsInfo;
	}
}
