<?php
namespace Snake\Package\Picture;

use \Snake\Libs\PlatformService\MlsStorageService;
use \Snake\Libs\Thrift\Packages\Anyval;
use \Snake\Package\Picture\Helper\DBPictureHelper;

require_once(PLATFORM_SERVICE_PATH . '/MlsStorageService.class.php');
class PictureMapper extends \Snake\Package\Base\Mapper{

	private $enforce = array('picid');	

    private $pictures = array();

    public function __construct($picture = array()) {
		parent::__construct($this->enforce);
		$this->pictures = $picture;
    }
	
	public function doGet($sql, array $sqlData) {
		if (empty($sql)) {
			return FALSE;
		}
		$this->pictures = DBPictureHelper::getConn()->read($sql, $sqlData, $this->master, $this->key);
		return $this->pictures;
	}
	//TODO
	public function doInsert($sql, array $sqlData) {
	}
	//TODO
	public function doUpdate() {
	}

	public function getPicture() {
		return $this->pictures;
	}

	public function storageGet($table, $col, $sql) {
        $this->picture = MlsStorageService::GetQueryData($table, NULL, $col, $sql);
		//错误处理
		if (!is_array($this->picture) || empty($this->picture)) {
			$this->picture = array();
		}
		return $this->picture;
	}

	public function storageuniqrowget($table, $keyname, $keyvals, $filter, $columns, $hashkey = "") {
		$this->picture = mlsstorageservice::uniqrowgetmulti($table, $keyname, $keyvals, $filter, $columns, $hashkey);
		//错误处理
		if (!is_array($this->picture) || empty($this->picture)) {
			$this->picture = array();
		}
		return $this->picture;	
	}
}
