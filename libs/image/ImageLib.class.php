<?php
namespace Snake\Libs\Image;

class ImageLib {

	public function __construct() {
	}

	public static function ImageLog($level,$str) {
	   list($usec,$sec) = explode(' ',microtime());
	   $milliSec = (int)((float)$usec * 1000);
	   $intSec = intval($sec);
	   file_put_contents(LOG_FILE_BASE_PATH . '/imgget.' . date('YmdH',$intSec) . '.log',
		   sprintf("%s %s:%d %s\n",$level,date('Y-m-d H:i:s', $intSec),$milliSec,$str),FILE_APPEND);
	}

	public static function ImageDebug($level,$str) {
		self::ImageLog('DEBUG',$str);
	}

	public static function ComposeImgServiceReqUrl($uri, $request_type = IMAGE_SERVICE_DEFAULT) {
		$server_addr = $GLOBALS['IMAGE_SERVICE']['SERVERS'][rand() % count($GLOBALS['IMAGE_SERVICE']['SERVERS'])];
		switch ($request_type) {
		case IMAGE_SERVICE_GET_RECORD:
		if (isset($GLOBALS['IMAGE_SERVICE']['RECORD_GET_SERVERS'])) {
			$server_addr = $GLOBALS['IMAGE_SERVICE']['RECORD_GET_SERVERS'][rand() % count($GLOBALS['IMAGE_SERVICE']['RECORD_GET_SERVERS'])];
		}
		break;
		case IMAGE_SERVICE_SET_RECORD:
		if (isset($GLOBALS['IMAGE_SERVICE']['RECORD_SET_SERVERS'])) {
			$server_addr = $GLOBALS['IMAGE_SERVICE']['RECORD_SET_SERVERS'][rand() % count($GLOBALS['IMAGE_SERVICE']['RECORD_SET_SERVERS'])];
		}
		break;
		case IMAGE_SERVICE_GET_IMAGE:
		if (isset($GLOBALS['IMAGE_SERVICE']['IMAGE_GET_SERVERS'])) {
			$server_addr = $GLOBALS['IMAGE_SERVICE']['IMAGE_GET_SERVERS'][rand() % count($GLOBALS['IMAGE_SERVICE']['IMAGE_GET_SERVERS'])];
		}
		break;
		case IMAGE_SERVICE_SET_IMAGE:
		if (isset($GLOBALS['IMAGE_SERVICE']['IMAGE_SET_SERVERS'])) {
			$server_addr = $GLOBALS['IMAGE_SERVICE']['IMAGE_SET_SERVERS'][rand() % count($GLOBALS['IMAGE_SERVICE']['IMAGE_SET_SERVERS'])];
		}
		break;
		case IMAGE_SERVICE_DOWNLOAD_IMAGE:
		if (isset($GLOBALS['IMAGE_SERVICE']['IMAGE_DOWNLOAD_IMAGE_SERVERS'])) {
			$server_addr = $GLOBALS['IMAGE_SERVICE']['IMAGE_DOWNLOAD_IMAGE_SERVERS'][rand() % count($GLOBALS['IMAGE_SERVICE']['IMAGE_DOWNLOAD_IMAGE_SERVERS'])];
		}
		break;
		case IMAGE_SERVICE_DEFAULT:
		default:
		break;
		}
		return $server_addr . $uri;
	}

	/**
	 * 判断是否是合法的图片扩展名.
	 * @param extName
	 * @return : TRUE or FALSE.
	 */
	public static function isValidImageExt($extName)
	{
		$lower = strtolower($extName);
		$EXT_ARR = array('jpg','jpeg','png','gif');
		foreach($EXT_ARR as $ext){
			if(strcmp($lower,$ext) == 0){
				return TRUE;
			}
		}
		return FALSE;
	}
}

