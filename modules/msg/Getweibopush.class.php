<?php
namespace Snake\Modules\Msg;

USE \Snake\Package\Msg\Helper\RedisWeiboPush AS RedisWeiboPush;

/**
 * 渠道页数据
 * 5分钟跑一次数据，从redis中抓取
 */
class Getweibopush extends \Snake\Libs\Controller {

	const QQ_PLATFORM = 'qq';

	public function run() {
		$infoObj = RedisWeiboPush::getWeiboPush(self::QQ_PLATFORM);
		$weiboInfo = array();
		$exp = '/https?:\/\/[\w-.%#=~?\/\\\]+/i';
		foreach ($infoObj as $info) {
			$pInfo['content'] = preg_replace_callback($exp, array($this, 'expurl'), $info['content']); 
			$pInfo['imgurl'] = $info['imgurl'];
			$weiboInfo[] = $pInfo;
		}
		$this->view = array_reverse($weiboInfo);
	}

	private function expurl($mathces) {
	    if (is_array($mathces)) {
			foreach ($mathces as $url) {
				return "<a href='$url'>$url</a>";
			}
    	}
	}

}
