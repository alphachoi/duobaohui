<?php
namespace Snake\Modules\Goods;



Use Snake\Package\Twitter\Twitter;
Use Snake\Package\Goods\Attribute;
Use Snake\Package\Goods\Goods;
Use Snake\Package\Goods\AttrWords;
Use Snake\Package\Topic\Topic;
Use Snake\Package\Cms\CmsIndexWelcome;
Use Snake\Package\Cms\CmsIndexType;
Use Snake\Package\Test\TestO;
Use Snake\Package\Test\Test;
Use Snake\Package\Twitter\TwitterGoods;
Use Snake\Package\Goods\AttrTwitterCtr1;
Use Snake\Package\Goods\SearchImplement;
Use Snake\Package\Goods\GoodsReport;
Use Snake\Package\Goods\TaobaoClick;
Use Snake\Package\Goods\TopTwitter;

class Tt extends \Snake\Libs\Controller {

	public function run() {
		$taobaoClick = new TopTwitter();
		$tt = $taobaoClick->getShowNum();

	}


}
