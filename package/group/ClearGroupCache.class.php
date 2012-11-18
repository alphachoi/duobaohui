<?php
namespace Snake\Package\Group;

use \Snake\libs\Cache\Memcache AS Memcache;

class ClearGroupCache implements \Snake\Libs\Interfaces\Iobserver{

	public function __construct() {

	}
	public function clearUserGroupCache($user_id, $group_id = 0) {
        $cacheObj = Memcache::instance();
        $cacheKeyNum = 'person:group:num:follow:' . $user_id;
        $cacheKey = 'person:group:follow:' . $user_id;
        $cacheObj->delete($cacheKeyNum);
        $cacheObj->delete($cacheKey);
        $cacheKeyNum = 'person:group:num:editor:' . $user_id;
        $cacheKey = 'person:group:editor:' . $user_id;
        $cacheObj->delete($cacheKeyNum);
        $cacheObj->delete($cacheKey);
        $cacheKey = 'person:group:admin:' . $user_id;
        $cacheObj->delete($cacheKey);
        $cacheKey = 'TOPIC_GROUP_RANK_' . $user_id;
        $cacheObj->delete($cacheKey);
        if (!empty($group_id)) {
            $cacheKey = "GROUP_INSIDE_PAGE_HEAD_CACHE_" . $group_id;
            $cacheObj->delete($cacheKey);
        }
	}

    public function onChanged($sender, $params) {
        print_r("running ClearCacheOb!\n");
        $this->clearUserGroupCache($params['user_id'], $params['group_id']);
    }   

}
