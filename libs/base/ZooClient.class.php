<?php
namespace Snake\Libs\Base;
/*
 *	author: jianxu@meilishuo.com
 */
class ZooClient {

	private static $client = NULL;

	public static function getClient($user_id = 0) {
		is_null(self::$client) && self::$client = new self($user_id);
		return self::$client;
	}

	private $user_id;
	private $host = IOHOST; 
	private $connect;

	/**
	 * construction
	 **/
	private function __construct($user_id) {
		$this->user_id = $user_id;
		$this->connect = new LinkToZoo($user_id);
	}
	
	/////////////////////
	//twitter operation//
	/////////////////////

	//twitter_like (get, post, delete)
	public function twitter_like($twitter_id, $method, $params = array()) {
		if (empty($twitter_id)) {
			$response['error'] = 'empty necessary parameters: twitter_id';
			return $response;
		}
		$url = $this->host . 'twitter/' . $twitter_id . '/likes';
		$params['twitter_id'] = $twitter_id;

		return $this->connect->$method($url, $params);
	}

	//twitter_stat single twitter (only post)
	public function twitter_stat($params) {
		$url = $this->host . 'twitters/twitter_stat';

		return $this->connect->post($url, $params);
	}

	////////////////
	//twitter stat//
	////////////////

	//twitters_likes_count (get)
	public function twitters_stat($twitter_id = array()) {
		if (empty($twitter_id)) {
			$response['error'] = 'empty necessary parameters: twitter_id';
			return $response;
		}
		$params = array();
		$twitter_ids = implode(',', $twitter_id);
		$params['twitter_id'] = $twitter_ids;
		$url = $this->host . 'twitters/twitter_stat';

		return $this->connect->post($url, $params);
	}

	public function twitter_likes_state($user_id, $twitter_id = array()) {
		if (empty($twitter_id)) {
			$response['error'] = 'empty necessary parameters: twitter_id';
			return $response;
		}
		$params = array();
		$twitter_ids = implode(',', $twitter_id);
		$params['user_id'] = $user_id;
		$params['twitter_id'] = $twitter_ids;
		$url = $this->host . 'twitters/likes_state';

		return $this->connect->get($url, $params);
	}

	public function twitters_likes_count($twitter_id = array()) {
		if (empty($twitter_id)) {
			$response['error'] = 'empty necessary parameters: twitter_id';
			return $response;
		}
		$params = array();
		$twitter_ids = implode(',', $twitter_id);
		$params['twitter_id'] = $twitter_ids;
		$url = $this->host . 'twitters/likes_count';

		return $this->connect->post($url, $params);
	}
	//////////////////////////
	//one user like twitters//
	//////////////////////////

	//twitters_likes_count (get)
	public function user_likes_twitters($user_id = 0, $offset = 0, $limit = 20) {
		if (empty($user_id)) {
			$response['error'] = 'empty necessary parameters: user_id';
			return $response;
		}
		
		$params = array(
			'offset' => $offset,
			'limit' => $limit,
		);
		$url = $this->host . 'user/' . $user_id . '/likes/twitter';

		return $this->connect->get($url, $params);
	}

	/////////////////
	//timeline task//
	/////////////////
	public function user_timeline($params) {
		$url = $this->host . 'user/timeline';

		return $this->connect->post($url, $params);
	}

	////////////////
	//general task//
	////////////////
	public function user_task($params) {
		$url = $this->host . 'user/task';

		return $this->connect->post($url, $params);
	}

	//////////////////////
	//general snake task//
	//////////////////////
	public function user_snake_task($params) {
		$url = $this->host . 'user/snaketask';

		return $this->connect->post($url, $params);
	}

	////////////////////////////////////////
	//push user last login time into queue//
	////////////////////////////////////////
	public function user_login($user_id, $time) {
		if (empty($user_id) || empty($time)) {
			$response['error'] = 'empty necessary parameters';
			return $response;
		}

		$params = array(
			'user_id' => $user_id,
			'time' => $time,
		);

		$url = $this->host . 'user/login_time';

		return $this->connect->post($url, $params);
	}

	////////////
	//push mix//
	////////////
	public function push_group_twitter($group_id, $type = NULL, $tids = array()) {
		if(empty($group_id)) {
			$response['error'] = 'empty necessary parameters';
			return $response;
		}
		$params = array(
			'group_id' => $group_id,
		);

		if (!empty($tids)) {
			$params['type'] = $type;
			$params['twitter_ids'] = implode(',', $tids);
		}

		$url = $this->host . 'group/group_twitter_mix';

		return $this->connect->post($url, $params);
	}

}

class LinkToZoo {

	public $userId = NULL;
	public $url;
	public $host = "";
	public $timeout = 5;
	public $connecttimeout = 5;
	public $ssl_verifypeer = FALSE;
	public $format = 'json';
	public $decode_json = TRUE;
	public $useragent = 'Meilishuo Snake Connect';
	public $debug = FALSE;

	function __construct($userId) {
		$this->userId = $userId;
	}
	
	function get($url, $parameters = array()) {
		$response = $this->ioRequest($url, 'GET', $parameters);
		if ($this->format === 'json' && $this->decode_json) {
			$response = json_decode($response, true);
		}
		return $response;
	}

	function post($url, $parameters = array(), $multi = false) {
		$response = $this->ioRequest($url, 'POST', $parameters, $multi );
		if ($this->format === 'json' && $this->decode_json) {
			return json_decode($response, true);
		}
		return $response;
	}

	function delete($url, $parameters = array()) {
		$response = $this->ioRequest($url, 'DELETE', $parameters);
		if ($this->format === 'json' && $this->decode_json) {
			return json_decode($response, true);
		}
		return $response;
	}

	function ioRequest($url, $method, $parameters, $multi = false) {
		if ('GET' == $method) {
			if (!empty($parameters)) {
				$url = $url . '?' . http_build_query($parameters);
			}
			return $this->http($url, 'GET');
		}
		else {
			$headers = array();
			if (!$multi && (is_array($parameters) || is_object($parameters)) ) {
				$body = http_build_query($parameters);
			} 
			else {
				$body = self::build_http_query_multi($parameters);
				$headers[] = "Content-Type: multipart/form-data; boundary=" . self::$boundary;
			}
			return $this->http($url, $method, $body, $headers);
		}
	}

	function http($url, $method, $parameters = NULL, $headers = array()) {
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
		curl_setopt($ci, CURLOPT_HEADER, FALSE);
		
		switch ($method) {
			case 'POST':
				curl_setopt($ci, CURLOPT_POST, TRUE);
				if (!empty($parameters)) {
					curl_setopt($ci, CURLOPT_POSTFIELDS, $parameters);
					$this->postdata = $parameters;
				}
				break;
			case 'DELETE':
				curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
				if (!empty($parameters)) {
					$url = "{$url}?{$parameters}";
				}
		}

		if (!empty($this->userId)) {
			$headers[] = "MEILISHUO: UID:" . $this->userId;
		}
		else {
			$headers[] = "MEILISHUO: UID:0";
		}
		curl_setopt($ci, CURLOPT_URL, $url);
		curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE);
		$response = curl_exec($ci);
		$this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
		$this->http_info = array_merge($this->http_info, curl_getinfo($ci));
		$this->url = $url;

		if ($this->debug) {
			echo "=====post data======\r\n";
			var_dump($parameters);

			echo '=====info====='."\r\n";
			print_r( curl_getinfo($ci) );

			echo '=====$response====='."\r\n";
			var_dump( $response );
		}
		curl_close ($ci);
		return $response;
	}

	public static function build_http_query_multi($params) {
		if (!$params) return '';
		uksort($params, 'strcmp');
		$pairs = array();
		self::$boundary = $boundary = uniqid('------------------');
		$MPboundary = '--'.$boundary;
		$endMPboundary = $MPboundary. '--';
		$multipartbody = '';

		foreach ($params as $parameter => $value) {
			$multipartbody .= $MPboundary . "\r\n";
			$multipartbody .= 'content-disposition: form-data; name="' . $parameter . "\"\r\n\r\n";
			$multipartbody .= $value."\r\n";
		}

		$multipartbody .= $endMPboundary;
		return $multipartbody;
	}
}
