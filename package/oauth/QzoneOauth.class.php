<?php
namespace Snake\Package\Oauth;
/**
 * @ignore
 */

class QzoneOauth {
	/**
	 * @ignore
	 */
	public $client_id;
	/**
	 * @ignore
	 */
	public $client_secret;
	/**
	 * @ignore
	 */
	public $access_token;
	/**
	 * @ignore
	 */
	public $openId;
	/**
	 * Contains the last HTTP status code returned.
	 *
	 * @ignore
	 */
	public $http_code;
	/**
	 * Contains the last API call.
	 *
	 * @ignore
	 /
	public $url;
	/**
	 * Set up the API root URL.
	 *
	 * @ignore
	 */
	public $host = "https://graph.qq.com/";
	/**
	 * Set timeout default.
	 *
	 * @ignore
	 */
	public $timeout = 30;
	/**
	 * Set connect timeout.
	 *
	 * @ignore
	 */
	public $connecttimeout = 30;
	/**
	 * Verify SSL Cert.
	 *
	 * @ignore
	 */
	public $ssl_verifypeer = FALSE;
	/**
	 * Respons format.
	 *
	 * @ignore
	 */
	public $format = 'json';
	/**
	 * Decode returned json data.
	 *
	 * @ignore
	 */
	public $decode_json = TRUE;
	/**
	 * Contains the last HTTP headers returned.
	 *
	 * @ignore
	 */
	public $http_info;
	/**
	 * Set the useragnet.
	 *
	 * @ignore
	 */
	public $useragent = 'Qzone oauth 2.0';
	/* Immediately retry the API call if the response was not successful. */
	//public $retry = TRUE;

	/**
	 * print the debug info
	 *
	 * @ignore
	 */
	public $debug = FALSE;

	protected $santor = 0;
	protected $clientIP = NULL;

	function authorizeURL()    { return 'https://graph.qq.com/oauth2.0/authorize'; }
	
	function accessTokenURL()  { return 'https://graph.qq.com/oauth2.0/token'; }

	function openIdURL() { return 'https://graph.qq.com/oauth2.0/me'; }

	/**
	 * construct WeiboOAuth object
	 */
	function __construct($client_id, $client_secret, $access_token = NULL, $openId = NULL, $ip = NULL) {
		$this->client_id = $client_id;
		$this->client_secret = $client_secret;
		$this->access_token = $access_token;
		$this->openId = $openId;
		if (!empty($ip)) {
			$this->clientIP = $ip;
		}
        /*if (!empty($santor)) {
            $this->santor = $santor;
            $cacheObj = Memcache::instance();
            $cacheKey = 'QzoneOauth:IP:' . $this->santor;
            $this->ip = $cacheObj->get($cacheKey);
        }*/
	}

	/**
	 * Get the authorize URL
	 * response_type must be code
	 * scope:
	 * do_like,get_user_info,add_share,add_topic,add_album,list_album,upload_pic
	 * @return string
	 */
     function getAuthorizeUrl($url, $response_type = 'code', $scope = 'default', $state = NULL, $display = '') {
         if ($scope == 'default') {
             $scope = 'do_like,get_user_info,get_info,add_share,add_topic,add_t,add_album,list_album,upload_pic,check_page_fans,get_fanslist,notify_tasksys,add_idol';
         }
         $params = array();
         $params['client_id'] = $this->client_id;
         $params['redirect_uri'] = $url;
         $params['response_type'] = $response_type;
         $params['scope'] = $scope;
		 $params['state'] = $state;
		 $params['display'] = $display;
         return $this->authorizeURL() . "?" . http_build_query($params);
     }    
	/**
	 * Exchange the request token and secret for an access token and
	 * secret, to sign API calls.
	 *
	 * @return array array("oauth_token" => the access token,
	 *                "oauth_token_secret" => the access secret)
	 */
	 function getAccessToken($callbackUrl, $type = 'code', $keys = array(), $state = NULL) {
		if (empty($keys)) {
			return FALSE;
		}
		$params = array();
		$params['client_id'] = $this->client_id;
		$params['client_secret'] = $this->client_secret;
		if ( $type === 'token' ) {
			$params['grant_type'] = 'refresh_token';
			$params['refresh_token'] = $keys['refresh_token'];
		}
		elseif ( $type === 'code' ) {
			$params['grant_type'] = 'authorization_code';
			$params['code'] = $keys['code'];
			$params['redirect_uri'] = $callbackUrl;
			$params['state'] = $state;
		} 
		elseif ( $type === 'password' ) {
			$params['grant_type'] = 'password';
			$params['username'] = $keys['username'];
			$params['password'] = $keys['password'];
		} 
		else {
			//throw new qzoneOAuthException("wrong auth type");
			return FALSE;
		}
		$response = $this->oAuthRequest($this->accessTokenURL(), 'POST', $params);
		$token = $this->parse_parameters($response);
		if (is_array($token) && !empty($token['access_token'])) {
			$this->access_token = $token['access_token'];
		} 
		else {
			$logHandler = new \Snake\Libs\Base\SnakeLog('Qzaccess', 'normal');
			$logHandler->w_log(print_r($token, TRUE));
			//throw new qzoneOAuthException("get access token failed." );
			die('get access token failed');
		}
		return $token;
	}

    function getOpenId() {
		if (empty($this->client_id) || empty($this->client_secret) || empty($this->access_token)) {
			return FALSE;
		}
		$params = array();
		$params['access_token'] = $this->access_token;
		$response = $this->oAuthRequest($this->openIdURL(), 'POST', $params);
 		if (strpos($response, "callback") !== false)
     	{
        	$lpos = strpos($response, '(');
        	$rpos = strrpos($response, ')');
        	$response  = substr($response, $lpos + 1, $rpos - $lpos -1);
     	}
		else {
			$logHandler = new \Snake\Libs\Base\SnakeLog('QzopenId', 'normal');
			$logHandler->w_log($response);
			return FALSE;
		}
		$response = json_decode($response, TRUE);
		return $response;
	}

	/**
	 * 解析 signed_request
	 *
	 * @param string $signed_request 应用框架在加载iframe时会通过向Canvas URL post的参数signed_request
	 *
	 * @return array
	 */
	function parseSignedRequest($signed_request) {
		list($encoded_sig, $payload) = explode('.', $signed_request, 2);
		$sig = self::base64decode($encoded_sig) ;
		$data = json_decode(self::base64decode($payload), true);
		if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') return '-1';
		$expected_sig = hash_hmac('sha256', $payload, $this->client_secret, true);
		return ($sig !== $expected_sig)? '-2':$data;
	}

	/**
	 * @ignore
	 */
	function base64decode($str) {
		return base64_decode(strtr($str.str_repeat('=',(4 - strlen($str) % 4)), '-_', '+/'));
	}

	/**
	 * 从数组中读取access_token和refresh_token
	 * 常用于从Session或Cookie中读取token，或通过Session/Cookie中是否存有token判断登录状态。
	 *
	 * @param array $arr 存有access_token和secret_token的数组
	 * @return array 成功返回array('access_token'=>'value', 'refresh_token'=>'value'); 失败返回false
	 */
	function getTokenFromArray( $arr ) {
		if (isset($arr['access_token']) && $arr['access_token']) {
			$token = array();
			$this->access_token = $token['access_token'] = $arr['access_token'];
			if (isset($arr['refresh_token']) && $arr['refresh_token']) {
				$this->refresh_token = $token['refresh_token'] = $arr['refresh_token'];
			}

			return $token;
		} else {
			return false;
		}
	}

	/**
	 * GET wrappwer for oAuthRequest.
	 *
	 * @return mixed
	 */
	function get($url, $parameters = array(), $source = 'auth') {
		if ($source == 'client') {
			$parameters['access_token'] = $this->access_token;
			$parameters['oauth_consumer_key'] = $this->client_id;
			$parameters['openid'] = $this->openId;
		}
		$response = $this->oAuthRequest($url, 'GET', $parameters);
		if ($this->format === 'json' && $this->decode_json) {
			return json_decode($response, true);
		}
		return $response;
	}

	/**
	 * POST wreapper for oAuthRequest.
	 *
	 * @return mixed
	 */
	function post($url, $parameters = array() , $multi = false, $source = 'auth') {
        if ($source == 'client') {
            $parameters['access_token'] = $this->access_token;
            $parameters['oauth_consumer_key'] = $this->client_id;
            $parameters['openid'] = $this->openId;
        } 
        if ($source == 'qplus') {
            $parameters['openid'] = $this->openId;
            $parameters['access_token'] = $this->access_token;
            $parameters['appid'] = '100' . QZONE_ID;
        } 
		$response = $this->oAuthRequest($url, 'POST', $parameters , $multi );
		if (strpos($url, "notify_tasksys")) {
			return $response;
		}
		if ($this->format === 'json' && $this->decode_json) {
			return json_decode($response, true);
		}
		return $response;
	}

	/**
	 * DELTE wrapper for oAuthReqeust.
	 *
	 * @return mixed
	 */
	function delete($url, $parameters = array()) {
		$response = $this->oAuthRequest($url, 'DELETE', $parameters);
		if ($this->format === 'json' && $this->decode_json) {
			return json_decode($response, true);
		}
		return $response;
	}

	/**
	 * Format and sign an OAuth / API request
	 *
	 * @return string
	 */
	function oAuthRequest($url, $method, $parameters , $multi = false) {

		if (strrpos($url, 'https://') !== 0 && strrpos($url, 'https://') !== 0) {
			$url = "{$this->host}{$url}.{$this->format}";
		}

		switch ($method) {
		case 'GET':
			$url = $url . '?' . http_build_query($parameters);
			return $this->http($url, 'GET');
		default:
			if (!$multi && (is_array($parameters) || is_object($parameters)) ) {
				$parameters = http_build_query($parameters);
			}
			return $this->http($url, $method, $parameters);
		}
	}

    public static function parse_parameters( $input ) {
        if (!isset($input) || !$input) return array();
        $pairs = explode('&', $input);
        $parsed_parameters = array();
        foreach ($pairs as $pair) {
            $split = explode('=', $pair, 2);
            $parameter = urldecode($split[0]);
            $value = isset($split[1]) ? urldecode($split[1]) : '';

            if (isset($parsed_parameters[$parameter])) {
                if (is_scalar($parsed_parameters[$parameter])) {
                    $parsed_parameters[$parameter] = array($parsed_parameters[$parameter]);
                }
                $parsed_parameters[$parameter][] = $value;
            } 
			else {
                $parsed_parameters[$parameter] = $value;
            }
        }
        return $parsed_parameters;
    }

	/**
	 * Make an HTTP request
	 *
	 * @return string API results
	 */
	function http($url, $method, $postfields = NULL) {
		$this->http_info = array();
		$ci = curl_init();
		/* Curl settings */
		curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
		curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
		curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
		curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ci, CURLOPT_ENCODING, "");
		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
		curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
		curl_setopt($ci, CURLOPT_HEADER, FALSE);

		switch ($method) {
		case 'POST':
			curl_setopt($ci, CURLOPT_POST, TRUE);
			if (!empty($postfields)) {
				curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
				$this->postdata = $postfields;
			}
			break;
		case 'DELETE':
			curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
			if (!empty($postfields)) {
				$url = "{$url}?{$postfields}";
			}
		}

		$headers=array();
		if ( isset($this->access_token) && $this->access_token )
			$headers[] = "Authorization: OAuth2 ".$this->access_token;

		$client_ip = '127.0.0.1';
		if(isset($_SERVER['REMOTE_ADDR'])){
			$client_ip = $_SERVER['REMOTE_ADDR'];
		}else{
			$client_ip = rand(1,254) . "." . rand(1,254) . "." . rand(1,254) . "." . rand(1,254);
		}
		/*
         * 当上线时，打开此接口
		 * $client_ip = $this->clientIP;
		 *
		 */	
		$client_ip = $this->clientIP;

		$headers[] = "API-RemoteIP: " . $client_ip;
		curl_setopt($ci, CURLOPT_URL, $url );
		curl_setopt($ci, CURLOPT_HTTPHEADER, $headers );
		curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE );

		$response = curl_exec($ci);
		$this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
		$this->http_info = array_merge($this->http_info, curl_getinfo($ci));
		$this->url = $url;
		if ($this->debug) {
			echo "=====post data======\r\n";
			var_dump($postfields);

			echo '=====info====='."\r\n";
			print_r( curl_getinfo($ci) );

			echo '=====$response====='."\r\n";
			print_r( $response );
		}
		curl_close ($ci);
		return $response;

	}

	/**
	 * Get the header info to store.
	 *
	 * @return int
	 */
	function getHeader($ch, $header) {
		$i = strpos($header, ':');
		if (!empty($i)) {
			$key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
			$value = trim(substr($header, $i + 2));
			$this->http_header[$key] = $value;
		}
		return strlen($header);
	}
}

