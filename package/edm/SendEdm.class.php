<?php
namespace Snake\Package\Edm;

class SendEdm implements \Snake\Libs\Interfaces\Iobserver {
	public function __construct() {

	}

	public function onChanged($sender, $args) {
		switch($sender) {
			case 'Register_action' :
				$this->sendEdmForReg($args);
				break;
			case 'Register_verify' :
				$this->sendEdmForRegVerify($args);
				break;
			default:
				break;
		}
	}
	//为男性用户发送验证邮件
	public function sendEdmForRegVerify($args) {
		$info['nickname'] = $args['nickname'];
		$info['email'] = $args['email'];
		$project = 343;
		$type = array();
		$this->post($info, $type, $project);
	}
	//为注册发送邮件，构造一下值
	public function sendEdmForReg($args) {
		$type = array('activecode');
		$info['nickname'] = $args['nickname'];
		$info['email'] = $args['email'];
		$info['activecode'] = $args['activatecode'];
		$project = 250;
		$this->post($info, $type, $project);
	}

	/**
	 * 发送edm到mail系统,$type为所需传的参数
	 */
	public function post($info, $type, $project) {
		if (empty($info) || empty($project)) return false;
		$data = array();
		$data['app_key'] = EDM_APP_KEY;
		$data['username'] = $info['nickname'];
		$data['email'] = $info['email'];
		$data['project'] = $project;
		if (!empty($type)) {
			foreach ($type as $typekey) {
				$data['mail_data'][$typekey] = $info[$typekey];
			}
		}
		$data = json_encode($data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, EDM_URL);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: text/plain', 'Charset: utf-8') );
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		$return_data = curl_exec($ch);
		curl_close($ch);
		return $return_data;
	}
}
