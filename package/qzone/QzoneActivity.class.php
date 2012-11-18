<?php

namespace Snake\Package\Qzone;

use \Snake\Package\Qzone\DBQzoneActivity;
use \Snake\Package\Session\UserSession;
use \Snake\Package\Welfare\SideBar;
use \Snake\Package\Oauth\Qq_sdk\OpenApiV3Client;
use \Snake\libs\Cache\Memcache;

class QzoneActivity {

	private $appId= NULL;
	private $appKey = NULL;
    private $logHelper = NULL;
	private $pf = 'qzone';
	private $qq = 1379986183;
	private $DBQzoneActivityHelper = NULL;
	private $reason = array(
		'我是一个喜欢体验和喜欢尝试的人，会用客观公正的眼光来评判商品的真正价值和性价比，每次体验过后我都会认真的写心得体会，不仅是为了完成体验作业，更多 的是带给大家最公正最客观的体验报告!',
		'希望自己能有机会体验一下!',
		'希望能申请成功，试用过后再和朋友们们分享~(@^_^@)~!',
		'积极努力申请试用产品!',
		'满怀期待等候审核结果!',
		'诚心诚意感受试用产品~~',
		'尽心尽力撰写试用报告~!',
		'哈哈!加油啊!要申请成功哦!',
		'每次申请试用，都带着小小的期待，希望抽中我。任何一份送到我手里的试用品，都会怀着一颗感恩的心去体会、珍惜、享用它，把它的特点介绍给我周围的朋友，让更多人来认识它。给淘友们展示一样商品真实的效用和品质~~~~~',
		'真的希望能够有机会体验好东东。如果有幸得到这次试用机会，我会一如既往认真细致地写好报告。积极申请，希望成功，如若幸运，必将尽心！这是我的申请试用态度。',
		'等啊等啊，终于有我喜欢和需要的商品了~~真好，我肯定争取这个机会，也希望美丽说能看到我为它写的的免费申请理由报告~~',
		'希望能通过我的申请，我会认真、客观的写出试用报告的。谢谢。',
		'任何一件试用宝贝都是商家的心血结晶，任何一份送到我手里的试用品，我都会用心去体会，无论它的价格是低还是高。我都一样的珍视它。我会好好的享用它，我相信我的诚意和努力会让我自己得到这个试用的机会的。谢谢！',
		'感谢商家给我们提供了这样的机会！希望可以试用成功！我对每一次的试用会以十分的认真态度去对待，因为这不仅是对广大没用试用过的朋友负责，更是对产品的负责！如果能够申请成功，本人一定认真做好试用报告！大力推荐给身边的每一位家人和朋友！不辜负商家的这份心意！为商家的试用活动作出贡献！',
		'这款宝贝看起来很不错，希望能申请成功，试用过后和大家分享喜悦，会用客观公正的眼光来评判商品的真正价值和性价比，体验过后我会认真的写心得体会 。',
		'看到自己喜欢、让自己心动的的宝贝，我就会忍不住的想要申请一份，不管结果如何，最重要的重在参与。如果申请成功了，我会把申请成功的宝贝分享给身边的朋友用，让他们来体验一下，分享我的喜悦，独乐乐不如众乐乐，毕竟一个人的体验有限，人多体验才最有价值，才能为宝贝的宣传做得更有价值 ~真的非常希望能得到这个试用的机会~',
		'我非常非常喜欢! '
		);
	public function __construct() {
        $this->logHelper = new \Snake\Libs\Base\SnakeLog('ApiLog', 'normal');
		if (strpos(MEILISHUO_URL, 'wwwtest') || strpos(MEILISHUO_URL, 'newlab')) {
			$this->appId = 100658804;
			$this->appKey = "41d10f2379486ecdd69b97cc739b2b53";
		}
		else {
			$this->appId= 100657684;
			$this->appKey = "29d8926367906274f62c4ca8e95de417";
		}
	}

	public function getActivitiesInfo($start = 0, $limit = 5, $cookie) {
		if (empty($this->DBQzoneActivityHelper)) {
			$this->_setDBHelper();
		}
		$activitiesList = $this->_getActivitiesList($start, $limit);
		foreach ($activitiesList AS $key => $value) {
			$activitiesList[$key] = $this->_addActivityApplyInfo($activitiesList[$key], $cookie);
		}
		return $activitiesList;
		//$activityIds = \Snake\Libs\Base\Utilities::DataToArray($activitiesList, 'activity_id');
	}

	public function getInComingActivities($start = 0, $limit = 1) {
		if (empty($this->DBQzoneActivityHelper)) {
			$this->_setDBHelper();
		}
		$valid = 2;
		$activitiesList = $this->_getActivitiesList($start, $limit, $valid);
		foreach ($activitiesList AS $key => $value) {
			$activitiesList[$key] = $this->_addActivityApplyInfo($activitiesList[$key], "", 0 );
		}
		$activitiesList = $activitiesList[0];
		return $activitiesList;
	}

	public function getActivityInfo($activityId, $start = 0, $limit = 10, $cookie) {
		if (empty($activityId)) {
			return array();
		}
		$this->_setDBHelper();
		$select = '/*qzone-lhz*/activity_id, title, activity_type, summary, products_introduction, products_price, organizer, index_banner, activity_banner, begin_time, end_time, top_banner, trynumber, activity_url, valid';
		$selectUsers = "/*qzone-lhz*/id, activity_id, real_name, reason, img_url, ctime, nickname";
		$activityInfo = $this->DBQzoneActivityHelper->getActivities(array($activityId), $select);
		$activityInfo = $activityInfo[$activityId];
		$activityInfo = $this->_translateActivityInfo($activityInfo, $cookie);
		$users = $this->DBQzoneActivityHelper->getApplyUsers($activityId, array(0,1), $start, $limit, $selectUsers);
		$users = $this->_assembleUserTalks($users);
		$num  = $this->getApplyUsersNum($activityId);
		$result = array(
			'activityInfo' => $activityInfo,
			'users' => $users,
			'totalNum' => $num
		);
		return $result;
	}

	public function getUserInfoFirstTime($openId, $openKey, $cookie, $activityId) {
		if (empty($openId) || empty($openKey)) {
			return FALSE;
		}
		$openParams = array(
			'open_id' => $openId,
			'open_key' => $openKey
		);

		$this->_setCookieForOpenId($openParams, $cookie);
		$firstTime = 1;
		$userInfo = $this->_getUserInfoFromQQ($openId, $openKey, $activityId, $firstTime);
		return $userInfo;
		
	}

	public function getUserInfoSecondTime($cookie, $activityId) {
		$openParams = $this->_getCookieForOpenId($cookie);

		//$this->_setCookieForOpenId($openParams, $cookie);
		$userInfo = $this->_getUserInfoFromQQ($openParams['open_id'], $openParams['open_key'], $activityId);
		return $userInfo;
	}

	public function checkUserIsFans($cookie) {
		$openParams = $this->_getCookieForOpenId($cookie);
		//$result = $this->_setUserIsFans($openParams['open_id']);
		$activityId = 0;
		$firstTime = 1;
		$result = $this->_getUserInfoFromQQ($openParams['open_id'], $openParams['open_key'], $activityId, $firstTime);
		return $result;
	}
	
	private function _getUserInfoFromQQ($openId, $openKey, $activityId, $firstTime = 0) {
		$cacheHelper = Memcache::instance();
		$cacheKey =	"QzoneActivity:UserInfo:" . $openId;
		$result = $cacheHelper->get($cacheKey);
		if (empty($result) || !empty($firstTime)) {
			$clientHelper = new OpenApiV3Client($this->appId, $this->appKey);
			$isFans = $clientHelper->is_fans($openId, $openKey, $this->pf, $this->qq);
			if ($isFans['ret'] != 0) {
				$str = "erro is:\n" . print_r($isFans, TRUE);
				$this->logHelper->w_log($str);
			}
			$isFans = $isFans['is_fans'];
			$userInfo = $clientHelper->get_user_info($openId, $openKey, $this->pf);
			$userInfo['openId'] = $openId;
			$userInfo['openKey'] = $openKey;
			$result = array(
				'isFans' => $isFans,
				'userInfo' => $userInfo
			);
			$cacheHelper->set($cacheKey, $result, 3600 * 24);
		}
		$isValid = $this->_getUserValid($openId, $activityId);
		if (empty($isValid)) {
			$isValid = 0;
		}
		$result['isValid'] = $isValid;
		return $result;

	}

	private function _setUserIsFans($openId) {
		$cacheHelper = Memcache::instance();
		$cacheKey = "QzoneActivity:UserInfo:" . $openId;
		if (strpos(MEILISHUO_URL, 'wwwtest') || strpos(MEILISHUO_URL, 'newlab')) {
			$cacheKey .= ":test";
		}
		$result = $cacheHelper->get($cacheKey);
		if (empty($result)) {
			return FALSE;
		}
		$result['isFans'] = 1;
		$cacheHelper->set($cacheKey, $result, 3600 * 24);
		return TRUE;
	}


	public function getActivitiesNumber() {
		if (empty($this->DBQzoneActivityHelper)) {
			$this->_setDBHelper();
		}
		$number = $this->DBQzoneActivityHelper->getActivitiesNumber();
		$number = $number[0]['count'];
		return $number;
	}

	public function getApplyUsersNum($activityId) {
		if (empty($this->DBQzoneActivityHelper)) {
			$this->_setDBHelper();
		}
		
		$select = "/*qzone-lhz*/count(id) AS count";
		$num = $this->DBQzoneActivityHelper->getApplyUsers($activityId, array(0,1), 0, 1, $select);
		$num = $num[0]['count'];
		return $num;
	}

	public function getTotalApplyData() {
		if (empty($this->DBQzoneActivityHelper)) {
			$this->_setDBHelper();
		}
		$start = 0;
		$limit = 2000;
		$activitiesList = $this->_getActivitiesList($start, $limit);
		$activityIds = \Snake\Libs\Base\Utilities::DataToArray($activitiesList, 'activity_id');
		$numbers = $this->DBQzoneActivityHelper->getApplyUsersNums($activityIds);
		$totalPrice = 0;
		$totalNum = 0;
		foreach ($activitiesList AS $key => $value) {
			$activityId = $activitiesList[$key]['activity_id'];
			$totalPrice += $numbers[$activityId]['count'] * $activitiesList[$key]['products_price'];
			$totalNum  += $numbers[$activityId]['count'];
		}
		$result = array(
			'total_price' => $totalPrice,
			'total_num' => $totalNum
		);
		return $result;
	}
	
	public function test() {
		if (empty($this->DBQzoneActivityHelper)) {
			$this->_setDBHelper();
		}
		$result = $this->DBQzoneActivityHelper->test();
		print_r($result);exit;
	}

	public function insertApply($data, $cookie) {
		if (empty($this->DBQzoneActivityHelper)) {
			$this->_setDBHelper();
		}	
		if (empty($data['openid'])) {
			$openParams = $this->_getCookieForOpenId($cookie);	
			$data['openid'] = $openParams['open_id'];
			$userInfo = $this->_getUserInfoFromQQ($openParams['open_id'], $openParams['open_key'], $data['activity_id']);
			$data['img_url'] = $userInfo['userInfo']['figureurl'];
            $data['nickname'] = $userInfo['userInfo']['nickname'];

		}
		$result = $this->DBQzoneActivityHelper->runInsert($data);
		$this->_setUserUnValid($data['openid'], $data['activity_id']);
		return $result;
	}

	public function getCarouselUsers($limit = 17) {
		if (empty($this->DBQzoneActivityHelper)) {
			$this->_setDBHelper();
		}
		$welfareHelper = new SideBar();
		$allUsers = $welfareHelper->getNewTakeInWelfareInfo($limit, 1);
		$users = array();
		foreach ($allUsers AS $key => $value) {
			$users[$key]['activity_id'] = $allUsers[$key]['activity_id'];
			$users[$key]['real_name'] = $allUsers[$key]['nickname'];
			$users[$key]['user_id'] = $allUsers[$key]['user_id'];
			$users[$key]['ctime'] = $allUsers[$key]['ctime'];
			$users[$key]['reason'] = /*'#' . $allUsers[$key]['activityName'] . '#' . */$this->reason[$key];
			$users[$key]['img_url'] = $allUsers[$key]['avatar'];
		}
		return $users;
		
		print_r($users);
		exit;
		$users = $this->DBQzoneActivityHelper->getCarouselUsers($limit);
		$users = $this->_assembleUserTalks($users);
		return $users;
	}

	public function setUserValidWithCookie($cookie, $number) {
		$openParams = $this->_getCookieForOpenId($cookie);
		$this->_setCookieForOpenId($openParams, $cookie);
		$result = $this->_setUserValid($openParams['open_id'], $number);
		return $result;
	}

	private function _setCookieForOpenId($params, $cookie) {
		$cacheKey = $cookie['SEASHELL'] . ':qzoneActivity';
		if (strpos(MEILISHUO_URL, 'wwwtest') || strpos(MEILISHUO_URL, 'newlab')) {
			$cacheKey .= ':test';
		}
        $cacheHelper = Memcache::instance();
        $cacheHelper->set($cacheKey, $params, 3600*24);
		$userInfo = $this->_getUserInfoFromQQ($params['open_id'], $params['open_key'], $activityId);
		return TRUE;
	}

	private function _getCookieForOpenId($cookie) {
		$cookieName = $cookie['SEASHELL'] . ":qzoneActivity";
		if (strpos(MEILISHUO_URL, 'wwwtest') || strpos(MEILISHUO_URL, 'newlab') || strpos(MEILISHUO_URL, 'newtest')) {
			$cookieName .= ':test';
		}
		$cacheHelper = Memcache::instance();
		$result = $cacheHelper->get($cookieName);
		return $result;
	}

	private function _getUserValid($openId, $activityId) {
		$result = $this->_checkIfApplied($openId, $activityId);
		if ($result == FALSE && !empty($activityId)) {
			return 0;
		}
		$cacheHelper = Memcache::instance();
		$cacheKey = "QzoneActivity:openId:" . $openId;
		$resultOne = $cacheHelper->get($cacheKey);
		if (!empty($resultOne)) {
			return 0;	
		}
		return 1;

	}

	private function _checkIfApplied($openId, $activityId) {
		if (empty($this->DBQzoneActivityHelper)) {
			$this->_setDBHelper();
		}	
		$result = $this->DBQzoneActivityHelper->getUserValid($openId, $activityId);
		if (isset($result[0])) {
			return 0;
		}
		return 1;
	}

	private function _setUserUnValid($openId, $activityId) {
		$number = 1;
		$cacheHelper = Memcache::instance();
		$cacheKey = "QzoneActivity:openId:" . $openId;
		$cacheHelper->set($cacheKey, $number, 24*3600);
		return TRUE;
	}

	private function _setUserValid($openId, $number) {
		if (empty($number)) {
			return FALSE;
		}
		$cacheHelper = Memcache::instance();
		$cacheKey = "QzoneActivity:openId:" . $openId;
		$lastNumber = $cacheHelper->get($cacheKey);
		$number = $lastNumber + $number;
		if ($number >= 4) {
			$cacheHelper->delete($cacheKey);
		}
		else {
			$cacheHelper->set($cacheKey, $number, 24*3600);
		}
		return TRUE;
	}

	private function _assembleUserTalks($users) {
		$activityIds = \Snake\Libs\Base\Utilities::DataToArray($users, 'activity_id');
		$names = $this->_getActivityNamesByIds($activityIds);
		$imgUrl = '/css/images/0.gif';
		$imgUrl = \Snake\Libs\Base\Utilities::getPictureUrl($imgUrl);
		foreach ($users AS $key => $value) {
			$add = "#" . $names[$users[$key]['activity_id']]['title'] . "#";
            if (!empty($users[$key]['nickname'])) {
                $users[$key]['real_name'] = $users[$key]['nickname'];
            }
			$users[$key]['reason'] = $add . $users[$key]['reason'];
			$time = strtotime($users[$key]['ctime']);
			$users[$key]['ctime'] = \Snake\Libs\Base\Utilities::timeStrConverter($time);
			if (empty($users[$key]['img_url'])) {
				$users[$key]['img_url'] = $imgUrl; 
			}
		}
		return $users;
	}

	private function _getActivityNamesByIds($activityIds) {
		$names = $this->DBQzoneActivityHelper->getActivities($activityIds);
		return $names;
	}

	private function _getActivitiesList($start, $limit = 5, $valid = 1) {
		$select = '/*qzone-lhz*/activity_id, title, activity_type, sortno, products_price, products_preview_img, organizer, activity_banner, begin_time, end_time, trynumber,  activity_url, valid';
		$list = $this->DBQzoneActivityHelper->getAllActivities($start, $limit, $select, $valid);
		return $list;
	}

	private function _addActivityApplyInfo($activitiesInfo, $cookie, $incoming = 1) {
		$activitiesInfo = $this->_translateActivityInfo($activitiesInfo, $cookie);
		if (!empty($incoming)) {
			$numbers = $this->DBQzoneActivityHelper->getApplyUsersNums(array($activitiesInfo['activity_id']));
			$userInfos = $this->DBQzoneActivityHelper->getApplyUsers($activitiesInfo['activity_id']);
			//$activitiesInfo['valid'] = $this->_getUserValid($openId, $activitiesInfo['activity_id']);
			$imgUrl = '/css/images/0.gif';
			foreach ($userInfos AS $key => $value) {
				if (empty($userInfos[$key]['img_url'])) {
					$userInfos[$key]['img_url'] = \Snake\Libs\Base\Utilities::getPictureUrl($imgUrl); 
				}
			}
			$activitiesInfo['user_info'] = $userInfos;
			$activitiesInfo['num'] = $numbers[$activitiesInfo['activity_id']]['count']; 
		}
		return $activitiesInfo;
	}
	
	private function _translateActivityInfo($activitiesInfo, $cookie) {
		$userInfo = $this->_getCookieForOpenId($cookie);
		$openId = $userInfo['open_id'];
		$activitiesInfo['activity_banner'] = \Snake\Libs\Base\Utilities::getPictureUrl($activitiesInfo['activity_banner'], '_o');
		$activitiesInfo['products_preview_img'] = \Snake\Libs\Base\Utilities::getPictureUrl($activitiesInfo['products_preview_img'], '_o');
		$activitiesInfo['date'] = date("n月j日", $activitiesInfo['begin_time']);
		$activitiesInfo['products_introduction'] = strip_tags($activitiesInfo['products_introduction'], '');
		$activitiesInfo['valid'] = $this->_checkIfApplied($openId, $activitiesInfo['activity_id']);
		if ($activitiesInfo['end_time'] < time()) {
			$activitiesInfo['valid'] = 2;
		}
		return $activitiesInfo;
	}
	
	private function _setDBHelper() {
		if (empty($this->DBQzoneActivityHelper)) {
			$this->DBQzoneActivityHelper = new DBQzoneActivity();
		}
		return TRUE;
	}
}

