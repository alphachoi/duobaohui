<?php
namespace Snake\Modules\Goods;
/**
 * @author xuanzheng@meilishuo.com
 */
Use Snake\Package\Goods\AttrWords;

/**
 * Guang页面左侧热门属性词
 * @author xuanzheng@meilishuo.com
 *
 */
class Popular_keywords extends  \Snake\Libs\Controller {


	public function run() {
		$attrs = AttrWords::getPopularAttrWords();
		$this->view = $attrs;
		return TRUE;
	}



}
