<?php
namespace Snake\Package\Goods;
Use \Snake\libs\Cache\Memcache;

class ShareMainMemcache extends \Snake\Package\Base\MemcacheBase{
	//最近评论数
	const SHAREMAIN = "Goods:ShareMain:share:";
	//超时时间(s)
	private $timeout = 1200;

	/**
	 * 设置
	 * @param array $shareData $shareData[$tid] = array()形式
	 * @return TRUE
	 * @access private 
	 */
	public function put($shareData) {
		foreach ($this->memIdentityObject->getSuffix() as $suffix) {
			$key = $this->memIdentityObject->getPrefix() . $suffix;
			if (!empty($shareData[$suffix])) {
				$putToMemcache[$key] = $shareData[$suffix];
			}
		}
		$this->memcache->setMulti($putToMemcache, $this->timeout);
		return TRUE;
	}

	public function del() {
	
	}

    
}
