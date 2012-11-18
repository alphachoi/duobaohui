<?php
namespace Snake\Package\Relation;

use \Snake\libs\Cache\Memcache AS Memcache;

class GroupUserRedis implements \Snake\Libs\Interfaces\Iobserver{

    public function __construct() {

    }

	public function onChanged($user_id, $group_id, $role) {
		
	}

}
