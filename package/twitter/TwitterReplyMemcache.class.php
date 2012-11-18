<?php
namespace Snake\Package\Twitter;

Use \Snake\libs\Cache\Memcache;

class TwitterReplyMemcache extends \Snake\Package\Base\MemcacheBase{
	//最近评论数
	const RECENTREPLY = "recent_reply_snake:";
	//存储查询结果的数组
	private $twitter = array();
	//存储的评论数量
	private $replyNum = 1;

    public function getTwitter() {
        return $this->twitter;
    }   

	public function put(\Snake\Package\Base\Collection $twitterCollection) {
		$twitters = array();
		$twitterCollection->rewind();
		while ($twitterCollection->valid()) {
			$twitterObj = $twitterCollection->next();	
			$stid = $twitterObj->getTwitterSourceTid();
			if (count($twitters[$stid]) < $this->replyNum) {
				$twitters[$stid][] = $twitterObj->getRow();
			}
		}

		$replyPutToMemcache = array();
		foreach ($this->memIdentityObject->getSuffix() as $suffix) {
			$key = $this->memIdentityObject->getPrefix() . $suffix;
			$replyPutToMemcache[$key] = array();
			if (isset($twitters[$suffix])) {
				$replyPutToMemcache[$key] = $twitters[$suffix];
			}
		}
		//$this->memcache->setMulti($replyPutToMemcache, 3000);
		$this->memcache->setMulti($replyPutToMemcache, 259200);
		return $twitters;
	}

	public function del() {
	
	}

    
}
