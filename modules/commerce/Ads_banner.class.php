<?php
namespace Snake\Modules\Commerce;
Use Snake\Package\Commerce\AdsData;
Use Snake\Package\Picture\PictureConvert;

class Ads_banner extends \Snake\Libs\Controller {
	private $type;
	private $position;
	public function run() {
		$this->type = trim($this->request->path_args[0]);
		$this->position = trim($this->request->path_args[1]);
		$catalog_id = $this->request->path_args[2] ? $this->request->path_args[2] : 0;
		$class_code = $page_code = $this->type;
		if($this->type == 'catalog') {
			$class_code = $this->type;
			$page_code = $catalog_id;
		}
		$bannerInfo = array();
		$adsHelper = new AdsData();
		$bannerInfo = $adsHelper->getWebBannerAPI($class_code, $page_code, $this->position);
		if (empty($bannerInfo)) return false;
		$info = $this->assmbleInfo($bannerInfo);
		$this->view = $info;
	}	
	public function assmbleInfo($bannerInfo) {
		$i = 0;
		$infos = array();
		foreach($bannerInfo as &$info) {
			if (!empty($info['pic_path'])) {
				$pictureHelper = new PictureConvert($info['pic_path']);
				$infos[$i]['img_url'] = $pictureHelper->getPictureO();
				$infos[$i]['links'] = MEILISHUO_URL . "/api/jump?r=" . urlencode($info['links']) . "&sid=" . $info['sid'] . "&mid=" . $info['mid'] . "&adid=" .$info['adid'];
				$i++;
			}
			
		}	
		return $infos;
	}
	
	
}
