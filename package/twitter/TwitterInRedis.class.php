<?php
namespace Snake\Package\Twitter;

Use \Snake\Libs\Redis\Redis AS Redis;

class TwitterInRedis extends Redis{

	const TWITTER_RANK_PRE = 'twitterRank_';

	public function getRedisKey ( $type = 'pop24' ) { 
		return self::TWITTER_RANK_PRE . $type;
	}   	
}
