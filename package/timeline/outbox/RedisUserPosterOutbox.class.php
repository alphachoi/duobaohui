<?php

namespace Snake\Package\Timeline\Outbox;


class RedisUserPosterOutbox extends \Snake\Libs\Redis\Redis {
	
	protected static $prefix = 'UserPosterOutbox';
	const SIZE = 300;
	
}
