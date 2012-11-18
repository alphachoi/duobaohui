<?php
namespace Snake\Package\Msg\Helper;

class RedisUserHomePosterTimeline extends \Snake\Libs\Redis\Redis {
	static $prefix = "UserHomePosterTimeline";

    public static function getLastTid($uId) {
        return self::lGet($uId, 0); 
    }   

    public static function pushTwitters($uId, $twitterIds) {
        if (empty($uId)) return;
        foreach ($twitterIds AS $twitterId) {
            self::lPush($uId, $twitterId);
        }   
    }  	
}
