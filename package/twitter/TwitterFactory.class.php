<?php
namespace Snake\Package\Twitter;
use \Snake\Libs\PlatformService\MlsStorageService;
use \Snake\Libs\Thrift\Packages\Anyval;
use \Snake\Package\Twitter\Helper\DBTwitterHelper;
use \Snake\Package\Twitter\Twitter;

require_once(PLATFORM_SERVICE_PATH . '/MlsStorageService.class.php');
class TwitterFactory {

	protected $twitters = array();

	public function __construct($twitter_ids) {
		/*$twitter_ids = implode(',', $twitter_ids);
		$sql = "SELECT * FROM t_twitter WHERE twitter_id IN ({$twitter_ids})";
		$result = DBTwitterHelper::getConn()->read($sql, array());*/
		$anyvalArr = array();
		foreach($twitter_ids as $tid) {
			$anyval = new AnyVal();
			$anyval->SetI32($tid);
			$anyvalArr[] = $anyval;
		}
		$result = MlsStorageService::UniqRowGetMulti('t_twitter', 'twitter_id', $anyvalArr, NULL, '*');
		foreach ($result as $twitter_info) {
			$this->twitters[$twitter_info['twitter_id']] = new Twitter($twitter_info);
		}
	}

	public function getTwitters() {
		return $this->twitters;
	}
}
