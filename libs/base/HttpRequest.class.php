<?php
namespace Snake\Libs\Base;

class HttpRequest {

	private $request_data = NULL;

	public static function getSnakeRequest() {
		static $singleton = NULL;
		is_null($singleton) && $singleton = new HttpRequest();
		return $singleton;
	}

	private function __construct() {
		// initialize HTTP data
		$this->request_data['protocol']  = $_SERVER['SERVER_PROTOCOL'];
		$this->request_data['domain']    = $_SERVER['SERVER_NAME'];
		$this->request_data['uri']       = $_SERVER['REQUEST_URI'];
		$this->request_data['path']      = $this->getRequestPath();
		$this->request_data['path_args'] = explode('/', $this->path);
		$this->request_data['method']    = $this->getRequestMethod();
		$this->request_data['GET']       = \Snake\Libs\Base\Utilities::zaddslashes(\Snake\Libs\Base\Utilities::unmark_amps($_GET));
		$this->request_data['POST']      = \Snake\Libs\Base\Utilities::zaddslashes(\Snake\Libs\Base\Utilities::unmark_amps($_POST));
		$this->request_data['COOKIE']    = \Snake\Libs\Base\Utilities::zaddslashes($_COOKIE);
		$this->request_data['REQUEST']   = \Snake\Libs\Base\Utilities::zaddslashes($_REQUEST);
		$this->request_data['headers']   = \Snake\Libs\Base\Utilities::parseRequestHeaders();
		$this->request_data['requri']    = isset($this->request_data['headers']['Requrl']) ? $this->request_data['headers']['Requrl'] : "";
		$this->request_data['base_url']  = $this->detectBaseUrl();
		$this->request_data['agent']     = \Snake\Libs\Base\Utilities::getBrowerAgent();
		$this->request_data['channel']   = $this->getRequestChannel();
		$this->request_data['refer']     = isset($this->request_data['headers']['Referer']) ? $this->request_data['headers']['Referer'] : "";
		$this->request_data['ip']        = $this->getIP(); 
		$this->request_data['time']      = $_SERVER['REQUEST_TIME'];
		$this->request_data['seashell']  = isset($_SERVER['HTTP_SEASHELL']) ? $this->getSeashell($_SERVER['HTTP_SEASHELL']) : null;
	}

	public function __get($name) {
		if (!isset($this->request_data[$name])) {
			return 0;
		}
		return $this->request_data[$name];
	}

    private function getSeashell($string) {
        $pairs = explode("=", $string);
        return $pairs[1];
    }

	/**
	 * Returns the requested URL path.
	 * E.g., for http://io.meilishuo.com/a/b it returns "a/b".
	 */
	private function getRequestPath() {
		// only parse $path once in a request lifetime
		static $path;

		if (isset($path)) {
			return $path;
		}

		if (isset($_SERVER['REQUEST_URI'])) {
			// extract the path from REQUEST_URI
			$request_path = strtok($_SERVER['REQUEST_URI'], '?');
			$base_path_len = strlen(rtrim(dirname($_SERVER['SCRIPT_NAME']), '\/'));

			// unescape and strip $base_path prefix, leaving $path without a leading slash
			$path = substr(urldecode($request_path), $base_path_len + 1);

			// $request_path is "/" on root page and $path is FALSE in this case
			if ($path === FALSE) {
				$path = '';
			}

			// if the path equals the script filename, either because 'index.php' was
			// explicitly provided in the URL, or because the server added it to
			// $_SERVER['REQUEST_URI'] even when it wasn't provided in the URL (some
			// versions of Microsoft IIS do this), the front page should be served
			if ($path == basename($_SERVER['PHP_SELF'])) {
				$path = '';
			}
		}

		return $path;
	}

	private function getRequestMethod() {
		static $method;

		if (isset($method)) {
			return $method;
		}

		$method = strtolower($_SERVER['REQUEST_METHOD']); 
		// make sure $method is valid and supported
		in_array($method, array('get', 'post', 'delete')) || $method = 'get';

		return $method;
	}

	private function detectBaseUrl() {
		$protocol = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
		$host = $_SERVER['SERVER_NAME'];
		$port = ($_SERVER['SERVER_PORT'] == 80 ? '' : ':' . $_SERVER['SERVER_PORT']);
		$uri = preg_replace("/\?.*/", '', $_SERVER['REQUEST_URI']);

		return "$protocol$host$port";
	}

	private function getIP() {
		static $ip;

		if (isset($ip)) {
			return $ip;
		}

		if (empty($this->request_data['headers']['Clientip'])) {
			$ip = "127.0.0.1";
		}
		elseif (!strpos($this->request_data['headers']['Clientip'], ",")) {
			$ip = $this->request_data['headers']['Clientip'];
		}
		else {
			$hosts = explode(',', $this->request_data['headers']['Clientip']);
			foreach ($hosts as $host) {
				$host = trim($host);
				if ($host != "unknown") {
					$ip = $host;
					break;
				}
			}
		}
		if (empty($ip)) {
			$log = new \Snake\Libs\Base\SnakeLog("iperror", "normal");
			$log->w_log($this->request_data['headers']['Clientip']);
		}

		return $ip;
	}

	private function getRequestChannel() {
		if (isset($_COOKIE['CHANNEL_FROM'])) {
			return $_COOKIE['CHANNEL_FROM'];
		}
		$url = isset($this->request_data['refer']) ? $this->request_data['refer'] : 0;
		if (empty($url)) {
			return 0;
		}
        if (strpos($url, 'renren.com') !== false || strpos($url, 'kaixin.com') !== false ) { 
            $channel = 1;
        }
		elseif (strpos($url, 't.sina.com.cn') !== false ) { 
            $channel = 3;
        }
		elseif (strpos($url , 'weibo.com') !== false ) { 
            $channel = 3;
        }
		elseif (strpos($url, 'baidu.com') !== false  ) { 
            $channel = 4;
        }
		elseif (strpos($url, 'google.com') !== false  ) { 
            $channel = 5;
        }
		elseif (strpos($url, 'taobao') !== false){
            $channel = 6;
        }
		elseif (strpos($url, 'douban.com') !== false){
            $channel = 7;
        }
		elseif (strpos($url, 'dangdang') !== false){
            $channel = 8;
        }
		elseif (strpos($url, 'hao123') !== false ) { 
            $channel = 11;
        }
		elseif (strpos($url, '2345') !== false ) {
            $channel = 14;
        }
		elseif (strpos($url, 'tuan.360.cn') !== false) {
            $channel = 15;
        }
		elseif (strpos($url, 'hao.360.cn') !== false) {
            $channel = 16;
        }
		elseif (strpos($url, 'pengyou.com') !== false) {
            $channel = 17;
        }
		elseif (strpos($url, 'qzone.qq.com') !== false) {
            $channel = 18;
        }
		elseif (strpos($url, 't.qq.com') !== false || strpos($url, 'url.cn')) {
            $channel = 19;
        }
		else {
            $channel = 0;
        }
		setcookie('CHANNEL_FROM', $channel, $_SERVER['REQUEST_TIME'] + 24 * 3600 * 30, '/' , DEFAULT_COOKIEDOMAIN);
		return $channel;
	}
}
