<?php
namespace Snake\Package\User;

USE \Snake\Package\User\Helper\RedisUserFans AS RedisUserFans;
USE \Snake\Package\User\Helper\RedisUserFollow AS RedisUserFollow;
/**
 * 改变Redis关注列表
 */
class ChangeRedisUserFans implements \Snake\Libs\Interfaces\Iobserver {

	public function __construct() {

	}

	public function onChanged($sender, $args) {
		switch ($sender) {
			case 'Follow' :
				$this->addFollow($args['user_id'], $args['other_id']);
				break;
			case 'CancelFollow' :
				$this->removeFollow($args['user_id'], $args['other_id']);
				break;
			case 'Register_action' :
				$this->addFollow($args['user_id'], $args['other_id']);
				$this->addFollow($args['other_id'], $args['user_id']);
				break;
			default :
				break;
		}
	}	

	/**
	 * 添加关注人到关注列表,并添加自已到关注人的粉丝列表
	 * @param $userId 用户编号 int
	 * @param $followId 关注人编号 int
	 */
	private function addFollow($userId, $followId) {
		//更新关注人的粉丝
        RedisUserFans::addFans($followId, $userId);
    	//更新自已的关注人
    	RedisUserFollow::addFollow($userId, $followId);
	}

	/**
	 * 将关注人从关注列表移除，并把自已从对方的粉丝列表去除
	 * @param $userId 用户编号 int
	 * @param $followId 关注人编号 int
	 */
	private function removeFollow($userId, $followId) {
		//更新关注人的粉丝
		RedisUserFans::removeFans($followId, $userId);
		//更新自已的关注人
		RedisUserFollow::removeFollow($userId, $followId);	
	}
}
