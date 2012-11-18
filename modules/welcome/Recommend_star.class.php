<?php
namespace Snake\Modules\Welcome;

use \Snake\Package\Welcome\Recommenduser;
use \Snake\Package\User\UserRelation;
use \Snake\libs\Cache\Memcache;

class Recommend_star extends \Snake\Libs\Controller {

	protected $relations = array();
	
	public function run() {
		$cache = Memcache::instance();
		$cacheKey = MEILISHUO_URL . '/wlcrecommendstar';
		list($sections, $users) = $cache->get($cacheKey);
		if (empty($sections)) {
			$RecommendUser = new Recommenduser($this->userSession['user_id']);
			list($sections, $users) = $RecommendUser->getRecommendUser();
			$cache->set($cacheKey, array($sections, $users), 3600);
		}
		if (!empty($this->userSession['user_id'])) {
			$userRelation = new UserRelation();
			$userIds = array();
			foreach ($users as $user) {
				$userIds[] = $user['user_id'];
			}
			$this->relations = $userRelation->getUserRelation($this->userSession['user_id'], $userIds);
		}
		foreach ($sections as &$section) {
			if (!empty($this->relations[$section['star']['uid']])) {
				$section['star']['isFollow'] = 1;
			}
		}

		$this->view = $sections;

	}

}
