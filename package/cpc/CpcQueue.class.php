<?php
namespace Snake\Package\Cpc;

class CpcQueue {
	//单推页面的访问队列
	const   CPCSHAREPVS = 'sharepvs';
	//点击跳转到淘宝的队列
	const   CPCCLICKPVS = 'clickpvs';

    private $ip = NULL;   
	private $key = "";
	private $user_id = 0;

    function __construct($user_id, $key){
		$this->user_id = $user_id;
		$this->ip = abs(\Snake\Libs\Base\Utilities::getClientIP(1));
		$this->key = $key;
    }
	function queueIn($tid){
		$cpc = array('tid' => $tid, 'ip' => $this->ip, 'time' => time(), 'user_id' => $this->user_id);
		GoodsCpcRedis::rPush($this->key, json_encode($cpc));
		return TRUE;
	}
	function queueOut($num = 1){
		$cpc = array();
		for ($i = 0; $i < $num; $i ++) {
			$click = GoodsCpcRedis::lPop($this->key);
			if (empty($click)) {
				break;
			}
			$cpc[] = json_decode($click);
		}
		return $cpc;
	}
}



