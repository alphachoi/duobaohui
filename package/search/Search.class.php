<?php
namespace Snake\Package\Search;
Use \Snake\Libs\Sphinx\SphinxClient AS SphinxClient;
Use \Snake\Package\Goods\CataWords AS CataWords;

class search{

	static $sphClient;
	static $secondSphClient;
	static $sphClientMaster;
	/**
	 * The following field means the state of the sphinx:
	 * available_idx = {0,1,1,1,0} means 1st&5th are useless,
	 * the others is useful Sphinx server.
	 * idxes = {0=>2, 1=>3, current=>3} means count(useless)=2,
	 * count(usefull)=3, current Sphinx Client is the 4th one
	 * in $GLOBALS['SPHINX']['SLAVE'].
	 **/
	static $available_idx = array();
	static $idxes = array();
	private $days_filter = 200;
	private $filter = array();
	private $sortby = NULL;
	private $offset = NULL;

	function __construct(){
		for($i=0; $i<count($GLOBALS['SPHINX']['SLAVE']); $i++) {
			self::$available_idx[$i] = 1;
		}
	}


	public function _getSphinxClient() {
		if( empty( self::$sphClient ) ) {
			if (!count(self::$available_idx)) {
				for($i=0; $i<count($GLOBALS['SPHINX']['SLAVE']); $i++) {
					self::$available_idx[$i] = 1;
				}
			}
			$addr = self::getSlaveAddr();
			self::$sphClient = new SphinxClient();
			self::$sphClient->SetServer($addr['HOST'],$addr['PORT']);
			//self::$sphClient->SetConnectTimeout(2);
			self::$sphClient->SetArrayResult(true);
		}
		self::resetHandler(self::$sphClient);
		return self::$sphClient;
	}

	public function _getSecondSphinxClient() {
		if( empty( self::$secondSphClient) ) {
			if (!count(self::$available_idx)) {
				for($i=0; $i<count($GLOBALS['SPHINX']['SLAVE']); $i++) {
					self::$available_idx[$i] = 1;
				}
			}
			$addr = self::getSlaveAddr();
			self::$secondSphClient = new SphinxClient();
			self::$secondSphClient->SetServer($addr['HOST'],$addr['PORT']);
			self::$secondSphClient->SetArrayResult(true);
		}
		self::resetHandler(self::$secondSphClient);
		return self::$secondSphClient;
	}


	static  function getSlaveAddr(){
		if(isset($GLOBALS['SPHINX']['SLAVE'])){
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
					return $GLOBALS['SPHINX']['SLAVE'][$key];
				}
			}
		}
		return FALSE;
	}

	static function resetHandler(&$handler){
		$handler->ResetFilters();
		$handler->ResetGroupBy();
		$handler->SetSortMode( SPH_SORT_RELEVANCE );
		$handler->SetMatchMode( SPH_MATCH_ALL );
	}

	public function queryViaValidConnection($key, $index) {
		$sc = self::$sphClient;
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
					$newSphClient = $this->updateSphinxClient();
					$result = $newSphClient->Query($key, $index);
					$sc_error = $newSphClient->GetLastError();
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

	public function secondQueryViaValidConnection($key, $index) {
		$sc = self::$secondSphClient;
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
					$newSphClient = $this->updateSphinxClient();
					$result = $newSphClient->Query($key, $index);
					$sc_error = $newSphClient->GetLastError();
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


	public function setSphinxLog($str) {
		// Write the error message randomly for file writing pressure
		if(1) {
			list($usec,$sec) = explode(' ',microtime());
			$milliSec = (int)((float)$usec * 1000);
			$intSec = intval($sec);
			file_put_contents(LOG_FILE_BASE_PATH . '/sphinx.log',
				sprintf("%s:%d %s\n",date('Y-m-d H:i:s', $intSec),$milliSec,$str),FILE_APPEND);
		}
	}

	public function updateSphinxClient() {
		/**
		 * Set the current index of slave server in Available array FALSE
		 **/
		self::$available_idx[self::$idxes['current']] = 0;
		// Get new Slave Server Address
		$addr = self::getSlaveAddr();
		self::$sphClient->SetServer($addr['HOST'],$addr['PORT']);
		return self::$sphClient;
	}

	public function adjustResult(&$r, $max) {
		$ar = array();
		if(!empty($r['matches'])) {
			foreach ($r['matches'] as $k => $v) {
				$id = $v['attrs']['goods_id_attr'];
				$v['id'] = $id; 
				$ar[$id]= $v;
			}
			$r['matches'] = $ar; 
		}
		$r['total'] = $r['total_found'] > $max ? $max : $r['total_found'];
		return $ar; 
	}    

	//rank函数
	public function mergeCpc($normalRes, $cpcRes, $offset = 0) {
		if (empty($normalRes['matches'])) {
			//return $normalRes;
			$normalRes['matches'] = array();
		}
		$maxMatch = current($normalRes['matches']);
		$minMatch = end($normalRes['matches']);
		//$maxMatch = end($normalRes['matches']);
		//$minMatch = current($normalRes['matches']);
		$minexpr = $minMatch['attrs']['@expr'];
		$maxexpr = $maxMatch['attrs']['@expr'];
		if ($offset == 0) {
			$maxexpr = 1;  
		}
		if (isset($cpcRes['matches'])) {
			foreach ($cpcRes['matches'] as $key => $value) {
				if ($value['attrs']['@expr'] > $minexpr && $value['attrs']['@expr'] <= $maxexpr) {
					$normalRes['matches'][] = $value;
				}    
			}    
		}    
		$sort = create_function('$a,$b', 'return ($a["attrs"]["@expr"]<$b["attrs"]["@expr"]);');
		usort($normalRes['matches'], $sort);    
		return $normalRes;
	}

	public function getRandExpr() {
		return "1";
		$hour  = date('H');
		$min   = date('i');
		return "(sin( goods_id_attr * ($hour * 60 + $min ) / 15 )/5+0.8)";
	}


	public function getCataExpr($searchKey) {
		$catalogNameTmp = explode(")|(", $searchKey);
		$catalogId = 0;
		if (!empty($catalogNameTmp)) {
			$catalogName = trim("((" . end($catalogNameTmp), "\x28..\x29");
			$params = array();
			$params['catalog_name'] = $catalogName;
			$catalogFinder = new CataWords();
			$catalogFinder->setFields(array('catalog_id'));
			$catalogFinder->setValue("catalog_name", $catalogName);
			$catalogInfo = $catalogFinder->getCatalogInfo();
			$catalogId = $catalogInfo[0]['catalog_id'];
		}
		$bigTypeOfCata = floor($catalogId / pow(10, 12));
		$midTypeOfCata = floor($catalogId / pow(10, 9));
		$smallTypeOfCata = floor($catalogId / pow(10, 6));

		if ($bigTypeOfCata != 0 && $midTypeOfCata != 0 && $smallTypeOfCata != 0) {
			$cataExpr = "(
				IF(
					floor(catalog_id / pow(10, 6)) == {$smallTypeOfCata} , 1, 
					IF(
						floor(catalog_id / pow(10, 9)) == {$midTypeOfCata} , 0.9, 
						IF(floor(catalog_id / pow(10, 12)) == {$bigTypeOfCata} , 0.8, 0.5)
					)
					)
				)";

		}
		else {
			$cataExpr = '0.6';
		}

		return $cataExpr;
	}


	/** 
	 * cpc&cps置顶测试
	 */
	public function getBusinessExprForCpcTest() {
		return "0.3 * pow( IF(commission == 1, 125000, 1) * IF( sale_volume > 144, 1, 0.1 + pow( sale_volume, 1/2) / 12 * 0.9) * IF( level > 8, 1, 0.5), 1/3) ";
	}

}
