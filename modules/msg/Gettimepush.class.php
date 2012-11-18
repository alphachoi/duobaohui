<?php
namespace Snake\Modules\Msg;

USE \Snake\Package\Msg\TimePush AS TimePush;

/**
 * 搜狗精选 sogou_select 5 条
 */
class Gettimepush extends \Snake\Libs\Controller {

	private $type;
	private $time;
	private $token;
	const TOKEN = 'a7b876ad7';
	private $allowTypes = array(
		'sogou_select', 
		'sogou_tip',
	);

	public function run() {
		if (!$this->_init()) {
			return FALSE;
		}

		$result = array();
		$tipResult = array();
		$timePush = new TimePush();
		$fields = array('id', 'contents', 'weibo_type', 'pushtime', 'callback_url', 'imgurl');
		$limit = 5;
		$result	 = $timePush->getMsgInfo('sogou_select', $this->time, $limit, $fields);
		if (empty($result)) {
			//没有数据，取最新的5个
			$result = $timePush->getMsgInfoByID('sogou_select', $limit, $fields);
		}
		foreach ($result as $key => $item) {
			$result[$key]['imgurl'] = \Snake\Libs\Base\Utilities::convertPicture($result[$key]['imgurl']); 
			$result[$key]['contents'] = trim($result[$key]['contents']);
			$result[$key]['unix_time'] = strtotime($result[$key]['pushtime']);
		}
		$startTime = date('Y-m-d', time() - 86400) . ' 19:00:00';
		$endTime = date('Y-m-d', time()) . ' 19:00:00';
		$limit = 1;
		$tipResult = $timePush->getPeriodMsgInfo('sogou_tip', $startTime, $endTime, $limit, $fields);			
		if (empty($tipResult)) {
			//没有数据，取最新的1个
			$tipResult = $timePush->getMsgInfoByID('sogou_tip', $limit, $fields);
		}
		foreach ($tipResult as $_key => $_item) {
			$tipResult[$_key]['unix_time'] = strtotime($tipResult[$_key]['pushtime']);
		}
		$this->view = array('sogou_select' => $result, 'sogou_tip' => $tipResult);
		return TRUE;
	}

	private function _init() {
		$this->token = $this->request->REQUEST['token'];
		if ($this->token != self::TOKEN) {
			$this->setError(400, 40002, 'token invalid');
			return FALSE;
		}
		/*
		//抓取时间在[9-24)抓取d那天的数据，抓取时间在[24-9)抓取d-1那天数据
		$time = date('Y-m-d', time());	
		$hour = date('G', time());
		if ($hour >= 0 && $hour < 9) {
			$time = date("Y-m-d",strtotime("-1 day"));
		}
		//$time .= ' 09:00:00';
		*/
		/*
		$hour = date('G', time());
		$this->time = substr_replace($time, '00:00', 14);
		//24时至9至之间的非抓取时间
		if ($hour >= 0 && $hour < 9) {
			$this->time = substr_replace($time, '00:00:00', 11);	
		}
		 */

		$time = date('Y-m-d', time());	
		$hour = date('G', time());
		if ($hour < 19) {
			$time = date("Y-m-d",strtotime("-1 day"));
		}

		$this->time = $time;
		/*
		$this->type = $this->request->REQUEST['type'];
		if (empty($this->type) || !in_array($this->type, $this->allowTypes)) {
			$this->setError(400, 40002, 'type invalid');
			return FALSE;
		}
		*/
		return TRUE;
	}
}
