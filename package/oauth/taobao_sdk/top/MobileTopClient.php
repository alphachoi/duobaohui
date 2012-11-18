<?php
class MobileTopClient
{
	public $appkey;
	public $secretKey;
	public $gatewayUrl = "http://api.m.taobao.com/rest/api2.do";
	protected $apiVersion = "*";
	protected $ttid;
	public $imei ;
	public $imsi ;
	
	private function _getPrefixFields(){
		$postFields = array(
			'v'	 => $this->apiVersion,
			'imei' => $this->imei,
			'imsi' => $this->imsi,
			'appkey' => $this->appkey,
			't' => time(),
			'ttid' => $this->ttid
		);
		
		return $postFields;
	}
	
	private function _genSign($postFields, $ecode = ''){
		$sign = md5($ecode . $this->secretKey 
								. $postFields['api'] 
								. $postFields['v'] 
								. $postFields['imei'] 
								. $postFields['imsi']  
								. md5($postFields['data']) 
								. $postFields['t']);
		return $sign;
	}
	
	function userLogin($callback = ''){
		$postFields = $this->_getPrefixFields();
		
		$postFields['api'] =  "com.taobao.wireless.mtop.getLoginUrl";
		$postFields['data'] = json_encode(array(
				'appkey' => $this->appkey,
				'callbackUrl' => $callback
				));
		$postFields['sign'] = $this->_genSign($postFields);
		
		$result = json_decode($this->curl($this->gatewayUrl, $postFields), true);
		
		return $result;
	}
	
	public function  getUserInfo($token){
		$userSession = $this->_getUserSession($token);
		if(isset($userSession['ret']['0'])){
			return $userSession;
		}
		
		return array();
	}
	
	private function _getUserSession($token){
		$postFields = $this->_getPrefixFields();
		
		$postFields['api'] =  "com.taobao.client.mtop.getUserSessionKey";
		$postFields['data'] = json_encode(array(
				'key' => $token,
				'appkey' => $this->appkey,
				));
		$postFields['sign'] = $this->_genSign($postFields);
		
		$result = json_decode($this->curl($this->gatewayUrl, $postFields), true);
		
		return $result;
	}
	
	public function autoLogin($token, $nick, $ecode){
		$postFields = $this->_getPrefixFields();
		
		$postFields['api'] =  "com.taobao.client.sys.autologin";
		$postFields['data'] = json_encode(array(
				'token' => $token,
				'appKey' => $this->appkey,
				'topToken' => md5($this->appkey . md5($this->secretKey) . $nick . $postFields['t'])
				));
				
		$postFields['sign'] = $this->_genSign($postFields, $ecode);
		$result = json_decode($this->curl($this->gatewayUrl, $postFields), true);
		
		return $result;
	}

	protected function curl($url, $postFields = null)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FAILONERROR, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		if (is_array($postFields) && 0 < count($postFields))
		{
			$postBodyString = "";
			$postMultipart = false;
			foreach ($postFields as $k => $v)
			{
				if("@" != substr($v, 0, 1))//判断是不是文件上传
				{
					$postBodyString .= "$k=" . urlencode($v) . "&";
				}
				else//文件上传用multipart/form-data，否则用www-form-urlencoded
				{
					$postMultipart = true;
				}
			}
			unset($k, $v);
			curl_setopt($ch, CURLOPT_POST, true);
			if ($postMultipart)
			{
				curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
			}
			else
			{
				curl_setopt($ch, CURLOPT_POSTFIELDS, substr($postBodyString,0,-1));
			}
		}
		$reponse = curl_exec($ch);

		if (curl_errno($ch))
		{
			throw new Exception(curl_error($ch),0);
		}
		else
		{
			$httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if (200 !== $httpStatusCode)
			{
				throw new Exception($reponse,$httpStatusCode);
			}
		}
		curl_close($ch);
		return $reponse;
	}

    function __construct($akey , $skey, $imei, $imsi, $ttid) {
    	$this->appkey = $akey;
    	$this->secretKey = $skey;
    	$this->ttid = $ttid;
    	$this->imei = $imei;
    	$this->imsi = $imsi;
    }

}