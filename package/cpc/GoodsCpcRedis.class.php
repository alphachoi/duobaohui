<?php
namespace Snake\Package\Cpc;
Use \Snake\Libs\Redis\Redis AS Redis;

class GoodsCpcRedis extends Redis{
	static $prefix = "GoodsCpc";
	static $xsync = true;
}
