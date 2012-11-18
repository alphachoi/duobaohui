<?php
namespace Snake\Package\Twitter;

Use \Snake\libs\Cache\Memcache;

class TwitterUserMemcache extends \Snake\Package\Base\MemcacheBase{
	//最近评论数
	const USERBOOK = 'USER:BOOKS:NUM:';

	private $twitterUser = array();

    public function getTwitterUser() {
        return $this->twitterUser;
    }   

	public function put(\Snake\Package\Base\Collection $twitterCollection) {
		//遍历集合
		$data = array();
		$twitterCollection->rewind();
		while ($twitterCollection->valid()) {
			$twitterObj = $twitterCollection->next();	
			$data = $twitterObj->getRow();
		}
		$num = 0;
		if (!empty($data['COUNT(*)'])) {
			$num = $data['COUNT(*)'];
		}
		//注意这个地方只有一个suffix
		foreach ($this->memIdentityObject->getSuffix() as $suffix) {
			$key = $this->memIdentityObject->getPrefix() . $suffix;
			$this->memcache->set($key, $num, 3000);
		}
		return $num;
	}

	public function del() {
	
	}
    
}
