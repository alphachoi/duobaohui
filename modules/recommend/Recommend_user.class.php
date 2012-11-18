<?php
/**
 *推荐人
 *author gstan
 *version 1
 *email guoshuaitan@meilishuo.com
 *date 2012-08-08
 */
namespace Snake\Modules\Recommend;
use Snake\Package\Group\Helper\RedisUserGroupFollower;
use Snake\Libs\Cache\Memcache;
use Snake\Package\Recommend\Recommend;
use Snake\Package\Recommend\RecommendUser;
use Snake\Package\User\Helper\RedisUserFollow;
use Snake\Package\User\User;
use Snake\Package\User\UserStatistic;
class Recommend_user extends \Snake\Libs\Controller {
	
	private $num ;
	private $user_id;
	private $type ;
	private static $cache;
    public static $cachekey = 'MEILISHUO_RECOMMEND_';

	public function _init() {
		$this->num = isset($this->request->path_args[0]) ? $this->request->path_args[0] : 15;
		$this->type = isset($this->request->REQUEST['type']) ? $this->request->REQUEST['type']  : "list";
		$this->user_id = $this->userSession['user_id'];
		if (!is_object(self::$cache)) {
			self::$cache =  Memcache::instance();
		}
	}
	public function run() {
		$this->_init();
		if (empty($this->user_id)) {
			$this->setError(404,400402, "no login in");
			return FALSE;
		}
		$key = self::$cachekey . "index_" . $this->user_id . "_" . $this->num;
		$quit = TRUE;
		//弹窗的推荐
		switch ($this->type) {
			case "alert" :
				$key = self::$cachekey . $this->user_id . "_" . $this->num;
				$quit = $this->recommendForAlert($key);
				break;
			default :
				break;
		}
		if (!$quit) return FALSE;
		$ip = \Snake\Libs\Base\Utilities::getClientIP(2);
		//$ip = ip2long($user_ip);
		//$datas = self::$cache->get($key);
		if(!empty($datas)) {
			$this->view = $datas;
			return TRUE;
		}
		//获得用户的粉丝
		$fans = RedisUserFollow::getFollow($this->user_id);
		$recommendHelper = new RecommendUser();
		//得到微博粉丝
		$weibo_user = array();
		$weibo_user = $recommendHelper->getRecommendWeiboUser($this->user_id, $fans);
		//得到优质用户
		$execlent_user = array();
		$execlent_user =  $recommendHelper->getExcelentUser($this->user_id, $fans);
		//兴趣相似的用户
		$reHelper = new Recommend();
		$similar_user = $reHelper->getReUserByAidArr(array(), 101, $this->num, $this->user_id);
		$similar_user_ids = \Snake\Libs\Base\Utilities::DataToArray($similar_user, 'user_id');
		//去除已经关注过的用户
		$similar_user_ids = array_diff($similar_user_ids, $fans);
		$similar_recommend = array();
		if (!empty($similar_user_ids)) {
			foreach($similar_user_ids as $uid) {
				$user_id = $uid['user_id'];
				$similar_recommend['rec_'.$user_id]['status'] = 0;
			}
		}
		$user_list = array();
		//防止不是数组报错
		if (is_array($execlent_user) && is_array($similar_recommend)) {
			$user_list = $execlent_user + $similar_recommend;
		}
		$user_list = $this->shuffle_assoc($user_list);
		//防止不是数组报错
		if (is_array($user_list) && is_array($weibo_user)){
			$user_list =  $weibo_user + $user_list;
		}
		
		//处理用户信息
		$datas = $this->assemleInfo($user_list);
		//达不到需要推荐的量就不推荐
		if (count($datas) < $this->num) {
			return false;
		}
        self::$cache->set($key, $datas, 3600);
		$this->view = $datas;
	}

	/**处理用户信息逻辑
	 *status 0兴趣相似的用户 1微博用户 2 优质用户
	 *credit 0 不是认证买家 1美丽说心级买家认证,2美丽说皇砖买家,3 超级主编 
	 */
	private function assemleInfo ($user_list) {
		if (empty($user_list)) {
			return false;
		}	
		$user = array();
		$userHelper = new User();
		$i = 1;
		foreach ($user_list as $key => $value) {
			$uid = ltrim($key, 'rec_');
			$param = array('user_id','nickname','avatar_c','is_taobao_buyer','verify_icons');
			$userInfo = $userHelper->getUserInfo($uid, $param);
			$user['user_id'] = $uid;
            if (strpos($userInfo['avatar_c'] ,'/css/images/0.gif') || empty($userInfo['avatar_c'])) {
				continue;
            }
			$user['avatar_c'] = $userInfo['avatar_c'];
			$user['nickname'] = strtok($userInfo['nickname'], '#');
			if ($value['status'] == 1) {
				$user['weibo_name'] = $value['weibo_name'];	
			}
            if ($value['status'] == 2) {
                $user['tag'] = $value['tag'];
            }
            $user['credit'] = $userInfo['is_taobao_buyer'] ? $userInfo['is_taobao_buyer'] : 0;
            $verify_icons = $userInfo['verify_icons'];
            if (strpos($verify_icons, 'e') !== FALSE) {
                 $user['credit'] = 3;
            }
            $user['status'] = $value['status'];
            $user['rank'] = mt_rand(80, 97);
			if ($this->type == "alert") {
				$staticHelper = new UserStatistic();
				$userStatics = $staticHelper->getUserStatisticByUid($uid);
                $user['static']['fans'] = $userStatics[0]['follower_num'];
                $user['static']['share'] = $userStatics[0]['twitter_num'];
                $user['static']['heart'] = $userStatics[0]['heart_num'];

			}
            $datas[] = $user;
			$i++;
			if ($i > $this->num) {
				break;	
			}
		}
		return $datas;
		
	}
	//是否弹窗推荐
	private function recommendForAlert($key) {

		//注册2天内不推荐
		$checktime = (time() > strtotime($this->userSession['ctime'] . '+2 day')) ? TRUE : FALSE;
		if (!$checktime) {
			$this->setError(404, 400403, "register below 2 day");
			return FALSE; 
		}
		//未激活用户不推荐
		if (!isset($this->userSession['is_actived']) || $this->userSession['is_actived'] == 2) {
			$this->setError(404, 400404, "no active user");
			return FALSE;
		}
		//关注杂志超过200不推荐
		$group_follow_num = RedisUserGroupFollower::getFollowGroupCount($this->user_id);
		if ($group_follow_num > 200) {
			$this->setError(404, 400405, "follow up to 200");
			return FALSE;
		}
		//如果点击关闭按钮，一天内不再推荐
		$datas = self::$cache->get($key);
		if ($datas == "empty") {
			$this->setError(404, 400406, "click X buttom");
			return FALSE;
		}
		return TRUE;
		
	}
	//随即二维数组
    private function shuffle_assoc($list) {
        if (!is_array($list)) {
            return $list;
		}
        $keys = array_keys($list);
        shuffle($keys);
        $random = array();
        foreach ($keys as $key) {
            $random[$key] = $list[$key];
		}
        return $random;
	}
	
	
	
}

