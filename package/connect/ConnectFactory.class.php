<?php
namespace Snake\Package\Connect;

class ConnectFactory {

    function __construct(){
    }  

	public function LoginSuccess($type, $userId, $params = array()) {
		$class = ucwords($type . 'Auth');
		$method = $type . 'Login';
		$class =  __NAMESPACE__ . "\\$class";
		$Obj = new $class();
		$result = $Obj->$method($userId, $params);
		//$result = call_user_func_array(array($class, $method), array('userId' => $userId));
		return $result;
	}

	/**
	 * @param $type String for example, 'weibo', 'qzone'; args[0] <br/>
	 * @param $params array
	 * 包括httpRequest信息，包括oauth_code,santorini_mm等信息<br/>
	 */
	public function Auth($type, $params = array()) {
		$class = ucwords($type . 'Auth');
		$method = $type . 'Auth';
		$class =  __NAMESPACE__ . "\\$class";
		$Obj = new $class();
		$result = $Obj->$method($type, $params);
		//$result = call_user_func_array(array($class, $method), array('refer' => $refer, 'frm' => $frm, 'type' => $type, 'params' => $params));
		return $result;
	}

	public function LoginFail($type, $params = array()) {
		$class = ucwords($type . 'Auth');
		$method = $type . 'Fail';
		$class =  __NAMESPACE__ . "\\$class";
		$Obj = new $class();
		$result = $Obj->$method($params);
		//$result = call_user_func_array(array($class, $method), array('userId' => $userId));
		return $result;
	}

	/**
	 * 更新token
	 */
	public function UpdateToken($type, $userId, $token, $auth, $ttl) {
		$class = ucwords($type . 'Auth');	
		$method = $type . 'UpdateToken';
		$class = __NAMESPACE__ . "\\$class";
		$obj = new $class();
		$obj->$method($userId, $token , $auth, $ttl);
	}

	public function SyncBind($type, $userId, $token, $auth, $ttl) {
		$class = ucwords($type . 'Auth');
		$method = $type . 'SyncBind';
		$class = __NAMESPACE__ . "\\$class";
		$obj = new $class();
		$obj->$method();		
	}
}
