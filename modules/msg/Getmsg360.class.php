<?php
namespace Snake\Modules\Msg;

use \Snake\Package\Msg\GetUserMsg AS GetUserMsg;
use \Snake\libs\Cache\Memcache AS Memcache;
use \Snake\Package\Msg\GetNewShareMsg AS GetNewShareMsg;

class Getmsg360 extends \Snake\Libs\Controller {
	
	public function run() {

		$baseUrl = 'http://www.meilishuo.com';
		$userId = $this->request->REQUEST['user_id'];
		$session = $this->request->REQUEST['session'];
		if (empty($userId) || empty($session)) {
			$this->setError(400, 40150, 'empty user_id or session');
			return FALSE;
		}

		$cacheHelper = MemCache::instance();
		$cacheKey = 'LogOn360:' . $userId;
		$sessionCache = $cacheHelper->get($cacheKey);

		$msgInfo = array(
			'fans_num' => 0,
			'atme_num' => 0,
			'pmsg_num' => 0,
			'recommend_num' => 0,
			'sysmesg' => 0,
			'newshare' => 0,
		);

		$code = 200;
		$codeMsg = '成功';

		if ($session == $sessionCache) {
			$msgHelper = new GetUserMsg($userId);
			$msgHelper->getInfoByUid();
			$msgInfo = $msgHelper->getMsgInfo();

			$newShareHelper = new GetNewShareMsg($userId, '', '360');
			$newShareHelper->getUserNewShareMsg();
			$msgInfo['newshare'] = $newShareHelper->getMsgInfo();
		}
		else {
			$code = 401;
			$codeMsg = '访问受限';
		}

		$msg = array(
			array(
				'msgtypeid' => 'fans_num',
				'msgtypename' => '新粉丝',
				'navigateurl' => $baseUrl . '/ur/fans/' . $userId,
				'count' => (int) $msgInfo['fans_num'],
			),	
			
			array(
				'msgtypeid' => 'atme_num',
				'msgtypename' => '@我',
				'navigateurl' => $baseUrl . '/atme',
				'count' => (int) $msgInfo['atme_num'],
			),	

			array(
				'msgtypeid' => 'pmsg_num',
				'msgtypename' => '私信',
				'navigateurl' => $baseUrl . '/msg/main/user',
				'count' => (int) $msgInfo['pmsg_num'],
			),	

			array(
				'msgtypeid' => 'recommend_num',
				'msgtypename' => '新喜欢我的',
				'navigateurl' => $baseUrl . '/atme/recommend/' . $userId,
				'count' => (int) $msgInfo['recommend_num'],
			),

			array(
				'msgtypeid' => 'sysmesg',
				'msgtypename' => '系统消息',
				'navigateurl' => $baseUrl . '/msg/main/syser',
				'count' => (int) $msgInfo['sysmesg'],
			),	

			array(
				'msgtypeid' => 'newshare',
				'msgtypename' => '新分享',
				'navigateurl' => $baseUrl . '/ihome',
				'count' => (int) $msgInfo['newshare'],
			),
		);

		$this->view = array(
			'result' => array( 
				'code' => $code,
				'msg' => $codeMsg,
			),	
			'msg' => array(
				array(
					'categoryid' => 'category1',
					'categoryname' => '默认分类',
					'content' => $msg,
				),
			),
		);

	}
}
