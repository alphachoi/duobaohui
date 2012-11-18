<?php
namespace Snake\Package\Goods;
Use \Snake\Libs\Redis\Redis AS Redis;

class GoodsShelfRedis extends Redis{
	static $prefix = "GOODS:UNSHELF";
	static $xsync = TRUE;
}
