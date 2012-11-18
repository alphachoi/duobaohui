<?php
namespace Snake\Package\Picture;

use \Snake\Libs\PlatformService\MlsStorageService;
use \Snake\Libs\Thrift\Packages\Anyval;
use \Snake\Package\Picture\Picture;
use \Snake\Package\Picture\Helper\DBPictureHelper;
require_once(PLATFORM_SERVICE_PATH . '/MlsStorageService.class.php');
class PictureFactory {

    protected $pictures = array();
    public function __construct($picids) {
		    /*$picids = implode(',', $picids);
		    $sql = "SELECT * FROM t_picture WHERE picid IN ({$picids})";
        $result = DBPictureHelper::getConn()->read($sql, array());*/
            $anyvalArr = array();
            foreach($picids as $pid) {
                $anyval = new AnyVal();
                $anyval->SetI32($pid);
                $anyvalArr[] = $anyval;
            }
            $result = MlsStorageService::UniqRowGetMulti('t_picture', 'picid', $anyvalArr, NULL, '*');
		    foreach ($result as $picture_info) {
			      $this->pictures[$picture_info['picid']] = new Picture($picture_info);
		    }
	  }

	  public function getPictureUrl($type) {
		    foreach ($this->pictures as $picture) {
			      $picture->getPictureThumbUrl($type);
		    }
	  }

	  public function getPictures() {
		    return $this->pictures;
	  }
}
