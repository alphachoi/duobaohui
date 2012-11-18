<?php
namespace Snake\Package\Manufactory;
/*
 *http://www.searchtb.com/2012/06/rolling-curl-best-practices.html
*/
class ClassicCurl {
	//url列表
	private $urls = array();	

	public function __construct(){
	}

	
	public function addUrl($url, $key, $fields = array()) {
		if (!array_key_exists($url, $this->urls)) {
			$this->urls[$url] = array($key, $fields);
		}
		return TRUE;
	}

	public function parallelCurl($delay = 0.5) {
		$queue = curl_multi_init();
		$map = array();
	 
		foreach ($this->urls as $url => $value) {
			list($key, $fields) = $value;
			// create cURL resources
			$ch = curl_init();
			// set URL and other appropriate options
			curl_setopt($ch, CURLOPT_URL, $url);
	 
			curl_setopt($ch, CURLOPT_TIMEOUT, 3);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_NOSIGNAL, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));  
	 
			// add handle
			curl_multi_add_handle($queue, $ch);
			$map[$url] = $ch;
		}
	 
		$active = null;
	 
		// execute the handles
		do {
			$mrc = curl_multi_exec($queue, $active);
		} 
		while ($mrc == CURLM_CALL_MULTI_PERFORM);
	 
		while ($active > 0 && $mrc == CURLM_OK) {
			if (curl_multi_select($queue, 0.5) != -1) {
				do {
					$mrc = curl_multi_exec($queue, $active);
				} 
				while ($mrc == CURLM_CALL_MULTI_PERFORM);
			}
		}
	 
		$responses = array();
		foreach ($map as $url=>$ch) {
			$responses[$this->urls[$url][0]] = json_decode(curl_multi_getcontent($ch), TRUE);//callback(curl_multi_getcontent($ch), $delay);
			curl_multi_remove_handle($queue, $ch);
			curl_close($ch);
		}
	 
		curl_multi_close($queue);
		return $responses;
	}
	
}

