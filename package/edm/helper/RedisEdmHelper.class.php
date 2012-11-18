<?php
namespace Snake\Package\Edm\Helper;
/**
 * 用户id与对应的qq的号,用户id的前5位构造hash key, 每个hash内部包含3位的key, 
 */

class  RedisEdmHelper extends  \Snake\Libs\Redis\Redis  {

    static $prefix = "qq";
    //添加用户对应的qq号
    public static function addUserQQ($user_id, $qq ) {
        if(empty($user_id) || empty($qq)) {
            return false;   
        }
        list($hkey, $bkey) = RedisEdmHelper::gethashkey($user_id);
        return  self::hSet($hkey, $bkey, $qq);
    }
    //得到这个用户是否存在qq号
    public static function getUserQQ($user_id) {
        if (empty($user_id)) return false;
        list($hkey, $bkey) = RedisEdmHelper::gethashkey($user_id);
        return self::hGet($hkey, $bkey);
    }
    //删除这个用户的qq
    public static function delUserQQ($user_id) {
        if (empty($user_id)) return false;
        list($hkey, $bkey) = RedisEdmHelper::gethashkey($user_id);
        return self::hDel($hkey, $bkey);
    }
    //得到hash key
    public  static function  gethashkey($user_id) {
        $user_id = intval($user_id);
        $hkey = intval($user_id/1000);
        $bkey = intval($user_id%1000);
        return array($hkey, $bkey);
    }


    
    
    
}

