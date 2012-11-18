<?php
namespace Snake\Libs\Base;

class Utilities {

	public static function parseRequestHeaders() {
		$headers = array();
		foreach($_SERVER as $key => $value) {
			if (substr($key, 0, 5) <> 'HTTP_') {
				continue;
			}
			$header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
			$headers[$header] = $value;
		}
		return $headers;
	}

	/**
	 * Jsonize data and indents the flat JSON string to make it more
	 * human-readable.
	 *
	 * @link
	 * http://recursive-design.com/blog/2008/03/11/format-json-with-php/
	 *
	 * @param mixed $data The data to be jsonized.
	 * @return string Indented version of the original JSON string.
	 */
	public static function jsonEncode($data, $pretty_print = FALSE, $options = 0) {
		$json = json_encode($data, $options);
		if (json_last_error()) {
			$log = new \Snake\Libs\Base\SnakeLog("json_error", "normal");
			$log->w_log(print_r($data, TRUE));
		}
		if (!$pretty_print) {
			return $json;
		}

		$result        = '';
		$pos           = 0;
		$str_len       = strlen($json);
		$indent_str    = '    ';
		$new_line      = "\n";
		$prev_char     = '';
		$out_of_quotes = TRUE;

		for ($i = 0; $i <= $str_len; $i++) {

			// Grab the next character in the string.
			$char = substr($json, $i, 1);

			// Are we inside a quoted string?
			if ($char == '"' && $prev_char != '\\') {
				$out_of_quotes = !$out_of_quotes;
			}
			else if (($char == '}' || $char == ']') && $out_of_quotes) {
				// If this character is the end of an element, 
				// output a new line and indent the next line.
				$result .= $new_line;
				$pos--;
				for ($j = 0; $j < $pos; $j++) {
					$result .= $indent_str;
				}
			}

			// Add the character to the result string.
			$result .= $char;

			// If the last character was the beginning of an element, 
			// output a new line and indent the next line.
			if (($char == ',' || $char == '{' || $char == '[') && $out_of_quotes) {
				$result .= $new_line;
				if ($char == '{' || $char == '[') {
					$pos ++;
				}

				for ($j = 0; $j < $pos; $j++) {
					$result .= $indent_str;
				}
			}

			$prev_char = $char;
		}

		return $result;
	}

	
    //用header跳转
    public static function headerToUrl($destUrl, $extHeader=array()) { 
        $destUrl = htmlspecialchars_decode($destUrl);

        if( !empty($extHeader) ) { 
            foreach($extHeader as $v) {
                header($v);
            }   
        }   
        header("Location: {$destUrl}");
    }   

	public static function createUrl($url, $params = array()) {
		$delimiter = FALSE === strpos($url, '?') ? '?' : '&';
		$args = array();
		foreach ($params AS $key => $value) {
			$args[] = "{$key}=" . urlencode($value);
		}
		$args = implode('&', $args);
		return "{$url}{$delimiter}{$args}";
	}

	public static function isSearchEngine($agentStr = '') {
		if (empty($agentStr)) { 
			$agentStr = $_SERVER['HTTP_USER_AGENT'];
		}    
		$kw_spiders = 'Indy|Mediapartners|Python-urllib|Yandex|alexa.com|Yahoo!|Googlebot|Bot|Crawl|Spider|spider|slurp|sohu-search|lycos|robozilla|ApacheBench';
		if (preg_match("/($kw_spiders)/i", $agentStr)) { 
			return true;
		}
		else {
			return false;
		}    
	}

	public static function getUniqueId() {
		return md5(uniqid(mt_rand(), TRUE) . $_SERVER['REQUEST_TIME']);
	}

	public static function getMemUsed() {
		return intval(memory_get_usage() / 1024);
	}

	public static function getBrowerAgent() {
		$trans = array(
			"[" => "{", 
			"]" => "}"
		);
		$agentStr = empty($_SERVER['HTTP_USER_AGENT']) ? "" : $_SERVER['HTTP_USER_AGENT'];
		return strtr($agentStr, $trans);
	}


	public static function getPosterImageHeight($oWidth, $oHeight, $pwidth = 180) { 
		// 原图宽度不足180的时候, 只对高度做裁剪
		if ($oWidth <= $pwidth) {
			$height = $oHeight;
			$width = $oWidth;
		}
		else {
			$factor = $oWidth / $pwidth;
			$height = $oHeight / $factor;
			$height = floor($height);
			$width = $pwidth;
		}
		$height = $height <= 800 ? $height : 800;
		return array($height, $width);
	}


	public static function DataToArray($dbData, $keyword) {
		//	print_r( $dbData );die();
		$retArray = array ();
		if (is_array ( $dbData ) == false or empty ( $dbData )) {
			return $retArray;
		}
		foreach ( $dbData as $oneData ) {
			if (isset ( $oneData [$keyword] ) and empty ( $oneData [$keyword] ) == false) {
				$retArray [] = $oneData [$keyword];
			}
		}
		//	return array_unique($retArray);
		return $retArray;
	}


	public static function changeDataKeys($data, $keyName, $toLowerCase=false) {
		$resArr = array ();
		if(empty($data)){
			return false;
		}
		foreach ( $data as $v ) {
			$k = $v [$keyName];
			if( $toLowerCase === true ) {
				$k = strtolower($k);
			}
			$resArr [$k] = $v;
		}
		return $resArr;
	}

	public static function getClientIP($mode = "string") {
		$ip = \Snake\Libs\Base\HttpRequest::getSnakeRequest()->ip;
		if ($mode != "string") {
			$ip = ip2long($ip);
		}
		return $ip;
	}

	public static function MlscacheLog($level, $str){
	   list($usec, $sec) = explode(' ',microtime());
	   $milliSec = (int)((float)$usec * 1000000);
	   $intSec = intval($sec);
	   $ret = file_put_contents('/home/work/webdata/logs/mlscache.' . date('YmdH',$intSec) . '.log',
	       sprintf("%s %s:%d:%d %s\n", $level, date('Y-m-d H:i:s', $intSec), $milliSec, 0, $str), FILE_APPEND);
	}

	
	public static function unmark_amps($get) {
		if (!empty($get)) {
			foreach ($get as $param => $value) {
				if (preg_match('/^amp\;(.*)$/i', $param)) {
					$paramNew = preg_replace('/^amp\;(.*)$/i', '$1', $param);
					unset($get[$param]);
					if ($paramNew != '') {
						$get[$paramNew] = $value;
					}
				}
			}
		}
		return $get;
	}

	public static function zaddslashes($string, $force = 0, $strip = FALSE) {
		if (!defined("MAGIC_QUOTES_GPC")) { 
			define("MAGIC_QUOTES_GPC", "");
		} 
		if (!MAGIC_QUOTES_GPC || $force) {
			if (is_array($string)) { 
				foreach ($string as $key => $val) {
					$string[$key] = \Snake\libs\base\Utilities::zaddslashes($val, $force, $strip);
				}
			}
			else {
				$string = ($strip ? stripslashes($string) : $string);
				$string = htmlspecialchars($string);
			}    
		}    
		return $string;
	}

	/**
	 * 时间转换函数，将时间转换成几分钟前，几小时前的形式。
	 * @param timestamp $createTime
	 * @return 	string $str
	 */
   public static function  timeStrConverter($createTime) {
		$now = date("Y-m-d");
		$yearNow = date("Y");
		$yearLast = date("Y" , $createTime);
		if( $yearNow == $yearLast){
			$timeValue = ceil ( (time() - $createTime) / 60 );
			if ($timeValue < 0) {
				$timeValue = 0 - $timeValue;
				$str = "0分钟前";
			}
			elseif ($timeValue < 20) {
				$timeValue = ltrim( $timeValue, '-' );
				$str = " {$timeValue}分钟前 ";
			}
			elseif (date("m" , $createTime) == date("m") && date("d" , $createTime) == date("d")) { //一个月以内的时间
				$str = '今天 ' . date ( "G:i", $createTime );
			}
			elseif(date("m" , $createTime) == date("m") && date("d" , $createTime) == date("d")-1){
				$str = '昨天 ' . date ( "G:i", $createTime );
			}
			else{//今年内的时间 并且 一天以上的时间
				$str = date ( "m月d日 G:i", $createTime );
			}
		}else{ //一年以上的时间
			$str = date ( "Y年m月d日 G:i", $createTime );
		}
		return $str;
	}

	/**
	 * 通用图片转换函数
	 * @param string $uri
	 * @return string $url
	 */
	public static function getPictureUrl($key, $type = "_o") {
		if(empty($key) || empty($type)){
			return '';
		}
		$type = strtolower($type);	
		$key = str_replace('/_o/', '/' . $type . '/', $key);

		$key = trim($key);
		if (strncasecmp($key, 'http://', strlen('http://')) == 0) {
			return $key;
		}

		$key = ltrim($key, '/');
		$hostPart = self::getPictureHost($key);
		if (empty($key)) {
			return  $hostPart . '/css/images/noimage.jpg';
		}
		return $hostPart . '/' . $key;

	} 

	public static function convertPicture($key) {

		if (strncasecmp($key, 'http://', strlen('http://')) == 0) {
			return $key;
		}

		$key = ltrim($key, '/');
		$hostPart = self::getPictureHost($key);
		if (empty($key)) {
			return $hostPart . '/css/images/0.gif';
		}
		return $hostPart . '/' . $key;
	}	

	private static function getPictureHost($key) {
		if (empty($key)) {
			return $GLOBALS['PICTURE_DOMAINS']['a'];
		}
		$remain = crc32($key) % 100; 
		$remain = abs($remain);
		$hashKey = $GLOBALS['PICTURE_DOMAINS_ALLOCATION'][$remain];
		return $GLOBALS['PICTURE_DOMAINS'][$hashKey];
	}

	public static function timetoWeek($time) {
		$weekarray = array('日','一','二','三','四','五','六');	
		$key = date('w', $time);
		$week = "星期" . $weekarray[$key];
		return $week;
	}

	/**
	 * @params NULL
	 * @author ZhengXuan
	 * @return int unix_timestamp change per 10 mins
	 */
	public static function timeByTenMin() {
		$todayTime = strtotime(date("Y-m-d"));
		$timeDetail = localtime(time(), TRUE);
		$whichTen = ($timeDetail['tm_hour'] * 3600 + $timeDetail['tm_min'] * 60 + $timeDetail['tm_sec']) / 600;
		$whichTen = floor($whichTen);
		return $todayTime + $whichTen * 600;
	}   

    /** 
     * @params string $nginxUid, from nginx header　
     * @author Chen Hailong
     * @return string $keyStr
     */
    public static function getGlobalKey($seaShell = '') {
        $code = md5($seaShell);
        $str = substr($seaShell, 8, 8); 
        $splited = str_split($str, 2); 
        $splited = array_reverse($splited);
        $str = implode('', $splited);
        $int = hexdec($str);
        $timeStr = date('ymdHis', $int);
        $keyStr = substr($code, 0, 17) . $timeStr . substr($code, -6, 3);   
        return $keyStr; 
    } 

	public static function objectToArray($obj) {
		if (is_object($obj)) { 
			$obj = get_object_vars($obj);
		} 
		if (is_array($obj)) { 
			return array_map(array('self',  __FUNCTION__), $obj);
		} 
		return $obj;
	}

	/**
	 * php in_array is too slow when array is large, this is optimized one
	 * @author Chen Hailong
	 */
	public static function inArray($item, $array) {
		$flipArray = array_flip($array);
		return isset($flipArray[$item]);
	}

}
