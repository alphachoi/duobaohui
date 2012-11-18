<?php 
/**
 * 推荐用户
 * author gstan
 * email guoshuaitan@meilishuo.com
 * date 2012-08-09
 * version 1.0
 */
namespace Snake\Package\Recommend;
use Snake\Package\User\UserConnect;
use Snake\Package\User\User;
use Snake\Package\User\Helper\RedisUserFollow;
use Snake\Package\Recommend\WeiboUser;
use Snake\Libs\DB\MakeSql;
use Snake\Package\Recommend\Helper\DBRecHelper;

class RecommendUser {
	
	public function getRecommendWeiboUser($user_id, $meilishuo_friends){
		$recommend = array();
		if (empty($user_id)) {
			return $recommend;	
		}
		//得到微博的粉丝
		$friends = UserConnect::getUserWeiboBilateral($user_id);
		$friends_user = array();
		//无粉丝
		if(empty($friends)){
			return $recommend;
		}
		//只取女粉丝
		foreach($friends  as $w_user){
			if($w_user['gender'] != 'm'){
				$friends_user[$w_user['id']] = array(
					'weibo_id' => $w_user['id'],
					'weibo_name' => $w_user['nickname'],
					'avatar' => $w_user['avatar']
				);
			}
		}
		//无女粉丝
		if (empty($friends_user)) {
			return $recommend;	
		}
		$weibo_ids = \Snake\Libs\Base\Utilities::DataToArray($friends_user, 'weibo_id');
		//获取在美丽说对应的用户
		$map_ids = WeiboUser::getInstance()->mapWeiboIdsToUids('uid, weibo_id, screen_name',$weibo_ids, false, 'uid');
		if (empty($map_ids)) {
			return $recommend;	
		}
		$meilishuo_ids = \Snake\Libs\Base\Utilities::DataToArray($map_ids, 'uid');
		//获得已经关注美丽说用户
		if (empty($meilishuo_friends)) {
			$meilishuo_friends = RedisUserFollow::getFollow($user_id);
		}
		//在美丽说但没有关注的微博好友
		$nofollow_friends_ids = array_diff($meilishuo_ids, $meilishuo_friends);
		if (empty($nofollow_friends_ids)) {
			return $recommend;	
		}
		//获得美丽说信息
		$meilishuo_info = array();
		$userHelper = new User();
		$param = array('user_id', 'nickname');
		$meilishuo_info = $userHelper->getUserInfos($nofollow_friends_ids, $param, 'user_id');
		//返回推荐的信息
		foreach ($nofollow_friends_ids as $uid) {
			if (isset($map_ids[$uid]['uid']) && !empty($friends_user[$map_ids[$uid]['weibo_id']])) {
				  $w_user = $friends_user[$map_ids[$uid]['weibo_id']];	
				  $recommend['rec_'.$uid] = array('nickname' => $meilishuo_info[$uid]['nickname'],
												  'weibo_name' => $w_user['weibo_name'],
												  'status' => 1
				  );
			}
			
		}
		return $recommend;
	}
	//获得优质用户
	public function getExcelentUser($user_id, $friend_ids, $num = 0 ) {
		$recommend = array();
		$recommend_user= array();
		if ($num != 0) {
			$info = $this->execlentUserInfo("user_id,tag", $num);
		}
		else {
			$info = $this->execlentUserInfo("user_id,tag");
		}
        $uids = $excelent_user = array();
        foreach($info as $value) {
            $uids[] = $value['user_id'];
            $excelent_user[$value['user_id']] = $value['tag'];
        }
		//去除自己
		$uids = array_diff($uids, array($user_id));
		if (empty($friend_ids)) {
			$friend_ids = RedisUserFollow::getFollow($user_id);
		}
		$recommend_user = array_diff($uids, $friend_ids);
		if (empty($recommend_user)) {
			return $recommend;	
		}
		foreach ($recommend_user as $uid) {
			$recommend['rec_'.$uid] = array('status' => 2,
										   'tag' => $excelent_user[$uid]
			);
		}
		return $recommend;
	}
	
	public function execlentUserInfo($colum, $limit = 0) {
		$param = array('table' => 't_dolphin_recommend',
					   'colum' => $colum
		);
		if (!empty($limit)){
			$param['limit'] = $limit;	
		}
						
		$info = $this->getData($param, false, "");
		return $info;
	}

	public function getData($param, $master = false, $hashKey = "") {
		$sqlHelper = new MakeSql();
		$sql = $sqlHelper->MakeSqlType('select', $param);
		$result = DBRecHelper::getConn()->read($sql, array(), $master, $hashKey);
		return $result;
	}
	
}

