<?php
namespace Snake\Package\Search;
Use \Snake\Libs\Sphinx\SphinxClient AS SphinxClient;

class Segmentation{

	static $segClient = NULL;
	static $idxes = array();
	static $available_idx = array();
	
	function __construct(){
		for($i=0; $i<count($GLOBALS['SEGMENTATION']); $i++) {
			self::$available_idx[$i] = 1;
		}
	}

	public function _getSegClient() {
		if(empty(self::$segClient)) {
			if (!count(self::$available_idx)) {
				for($i=0; $i<count($GLOBALS['SEGMENTATION']); $i++) {
					self::$available_idx[$i] = 1;
				}
			}
			$addr = self::getSlaveAddr();
			self::$segClient = new SphinxClient();
			self::$segClient->SetServer($addr['HOST'],$addr['PORT']);
			self::$segClient->SetConnectTimeout(2);
			self::$segClient->SetArrayResult(true);
		}
		self::resetHandler(self::$segClient);
		return self::$segClient;
	}

	static  function getSlaveAddr(){
		if (isset($GLOBALS['SEGMENTATION'])){
			self::$idxes[0] = self::$idxes[1] = 0;
			// Get the count of useful/useless servers
			foreach(self::$available_idx as $v) {
				self::$idxes[$v]++;
			}
			// Get one of the useful servers array
			$num = rand(1, self::$idxes[1]);
			$count = 0;
			foreach(self::$available_idx as $key=>$value) {
				if(1 == $value) {
					$count++;
				}
				if($count == $num) {
					self::$idxes['current'] = $key;
					return $GLOBALS['SEGMENTATION'][$key];
				}
			}
		}
		return FALSE;
	}

	static function resetHandler(&$handler){
		$handler->ResetFilters();
		$handler->ResetGroupBy();
		$handler->SetSortMode(SPH_SORT_RELEVANCE);
		$handler->SetMatchMode(SPH_MATCH_ALL);
	}

	public function queryViaValidConnection($key, $index) {
		$sc = self::$segClient;
		$result = $sc->Query($key, $index);
		$sc_error = $sc->GetLastError();
		if (isset($sc_error{1})) {
			if (strpos($sc_error, "errno=111") !== false || strpos($sc_error, "errno=115") !== false ) {
				/* 
				 * If the error/warning string contains:
				 * connection to host:port failed (errno=111, msg=Connection refused)
				 * Then, try to connect a new Sphinx Client
				 * 
				 * self::$idxes[1] means the count of the server working normally
				 */
				while(isset($sc_error{1}) && 0 != self::$idxes[1]) {
					$newSegClient = $this->updateSphinxClient();
					$result = $newSegClient->Query($key, $index);
					$sc_error = $newSegClient->GetLastError();
					if(isset($sc_error{1})) {
						$this->setSphinxLog($sc_error . $key);
					}
				}
			}
			else {
				$this->setSphinxLog($sc_error . $key);
			}
		}
		return $result;
	}

	public function updateSphinxClient() {
		/**
		 * Set the current index of slave server in Available array FALSE
		 **/
		self::$available_idx[self::$idxes['current']] = 0;
		// Get new Slave Server Address
		$addr = self::getSlaveAddr();
		self::$segClient->SetServer($addr['HOST'],$addr['PORT']);
		return self::$segClient;
	}

	public function setSphinxLog($str) {
		// Write the error message randomly for file writing pressure
		if(1) {
			list($usec,$sec) = explode(' ',microtime());
			$milliSec = (int)((float)$usec * 1000);
			$intSec = intval($sec);
			file_put_contents(LOG_FILE_BASE_PATH . '/segmentation.log',
				sprintf("%s:%d %s\n",date('Y-m-d H:i:s', $intSec),$milliSec,$str),FILE_APPEND);
		}
	}
	
}
