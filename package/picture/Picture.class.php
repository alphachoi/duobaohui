<?php
namespace Snake\Package\Picture;
Use \Snake\Libs\PlatformService\MlsStorageService;
Use \Snake\Libs\Thrift\Packages\Anyval;
Use \Snake\Package\Twitter\Helper\DBTwitterHelper;
Use \Snake\libs\Cache\Memcache;
Use \Snake\Package\Base\IdentityObject;
Use \Snake\Package\Base\DomainObjectAssembler;
require_once(PLATFORM_SERVICE_PATH . '/MlsStorageService.class.php');

class Picture{

	private $picture = array();
	private $fields = array();
    public function __construct($fields = array(), $picture = array()) {
		$this->fields = $fields;
		$this->picture = $picture;
	}

	public function getPictureByPids($pids, $obj = FALSE) {
		if (empty($this->fields) || empty($pids)) {
			return array();
		}

        foreach ($pids as $pid) {
			if (!is_numeric($pid) || $pid <= 0) {
				continue;
			}
            $anyval = new AnyVal();
            $anyval->SetI32($pid);
            $anyvalArr[] = $anyval;
        }	

		//构造sql
		$identityObject = new IdentityObject();
		$identityObject->field('picid')->in($pids);
		//设置需要获取的列
		$identityObject->col($this->fields);
		$domainObjectAssembler = new DomainObjectAssembler(PicturePersistenceFactory::getFactory('\Snake\Package\Picture\PicturePersistenceFactory'));
		//$pictureCollection = $domainObjectAssembler->mysqlFind($identityObject);
		$pictureCollection = $domainObjectAssembler->storageUniqRowFind($identityObject, $anyvalArr, $filter);
		//遍历集合
		$pictures = array();
		while ($pictureCollection->valid()) {
			$pictureObj = $pictureCollection->next();	
			if ($obj) {
				$pictures[] = $pictureObj;
			}
			else {
				$pictures[] = $pictureObj->getRow();
			}
		}
		$this->picture = $pictures;
		return $pictures;
	}
 
}
