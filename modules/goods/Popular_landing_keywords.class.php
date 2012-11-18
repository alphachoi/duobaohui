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
class Popular_landing_keywords extends  \Snake\Libs\Controller {


	public function run() {
		$attrs = array('新款','裙','雪纺','欧美','衫','包包','蕾丝','夏装','百搭','甜美','蝴蝶结','时尚','糖果','修身','显瘦','平底鞋','厚底','牛仔','日系','可爱','牛皮','宽松','棉','单肩包','短袖','气质','外套','拼接');
		$this->view = $attrs;
		return TRUE;
	}



}
