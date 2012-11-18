<?php
namespace Snake\Modules\Goods;

Use Snake\Package\Goods\CatalogMix;
Use Snake\Package\Search\SearchObject;
Use Snake\Package\Search\CataExpr;
Use Snake\Package\Search\BracketExpr;
Use Snake\Package\Search\BusExpr;
Use Snake\Package\Search\UserExpr;
Use Snake\Package\Search\MainExpr;
Use Snake\Package\Search\CpcBusExpr;
Use Snake\Package\Search\CpcUserTestExpr;
Use Snake\Package\Search\CpcBusTestExpr;


Use \Snake\Package\Twitter\Twitter;
Use \Snake\Package\Picture\Picture;

/**
 * 为拍拍提供获取宝贝数据的接口
 * @package goods
 * @author weiwang
 * @since 2012.10.31
 */
class Paipai_catalog_get extends \Snake\Libs\Controller{

    public function run() {

        $appkey = isset($this->request->REQUEST['appkey']) ? $this->request->REQUEST['appkey'] : 0;
        $appsecret = isset($this->request->REQUEST['appsecret']) ? $this->request->REQUEST['appsecret'] : 0;
        $pageNo = isset($this->request->REQUEST['page_no']) ? $this->request->REQUEST['page_no'] : 0;
        $pageSize = isset($this->request->REQUEST['page_size']) ? $this->request->REQUEST['page_size'] : 40;
        $beginTime = isset($this->request->REQUEST['begin_time']) ? strtotime($this->request->REQUEST['begin_time']) : strtotime('2012-10-10');
        $endTime = isset($this->request->REQUEST['end_time']) ? strtotime($this->request->REQUEST['end_time']) : time();
        $parentId= isset($this->request->REQUEST['parent_id']) ? $this->request->REQUEST['parent_id'] : 0;
        $name = isset($this->request->REQUEST['name']) ? $this->request->REQUEST['name'] : "";

        if ($endTime < $beginTime) {
            $message = "invalid-parameter:end_time";
            $response = array("message" => $message);
            echo json_encode($response);
            return FALSE;
        }
        elseif (empty($appkey)) {
            $message = "missing-parameter:appkey";
            $response = array("message" => $message);
            echo json_encode($response);
            return FALSE;
        }
        elseif (empty($appsecret)) {
            $message = "missing-parameter:appsecret";
            $response = array("message" => $message);
            echo json_encode($response);
            return FALSE;
        }   
        elseif ($appkey != 111111) {
            $message = "invalid-parameter:appkey";
            $response = array("message" => $message);
            echo json_encode($response);
            return FALSE;
        }
        elseif ($appsecret != 'ea4b631e002ce301eab7357012d72077') {
            $message = "invalid-parameter:appsecret";
            $response = array("message" => $message);
            echo json_encode($response);
            return FALSE;
        }
        elseif($pageSize >= 200) {
            $message = "invalid-parameter:page_size";
            $response = array("message" => $message);
            echo json_encode($response);
            return FALSE;

        }
        
        $paipaiSearchObj = new SearchObject();
        $paipaiSearchObj->setFilter("verify_stat", array(1,2));      
        //$paipaiSearchObject->setFilterRange("goods_author_ctime", $beginTime, $endTime);        
        if (!empty($parentId)) {
            $catalogMix = new CatalogMix($parentId);
            $catRange = $catalogMix->getIdRange();
            $paipaiSearchObj->setFilterRange('catalog_id', $parentId, $catRange['down']);
        }
        $exprBuilder = new CataExpr(new BracketExpr(new BusExpr(new UserExpr(new MainExpr(), ""), "+"), ""), "*");
        $searchExpr = $exprBuilder->getExpr();
        $paipaiSearchObj->setLimit($pageNo * $pageSize, $pageSize);
        $paipaiSearchObj->setIndex("goods_id_dist");
        $paipaiSearchObj->setSortMode(SPH_SORT_EXPR, $searchExpr);
        $paipaiSearchObj->search($name);
        $goodsDist = $paipaiSearchObj->getSearchResult();
		$total = min($goodsDist['total'], $goodsDist['total_found']);
        $goodsDist = $goodsDist['matches'];
        $tids = array();
        $tidCataMap = array();
        if (!empty($goodsDist)) {
            foreach ($goodsDist as $id => $detail) {
                $tids[] = $detail['attrs']['twitter_id'];
                $tidCataMap[$detail['attrs']['twitter_id']] = substr($detail['attrs']['catalog_id'], 0, 1) . "000000000000";
            }
        }
        $twitters = $this->getTwitters($tids);
        $pids = array_values($twitters); 
        $pictures = $this->getPictures($pids);
        $view = array();
        foreach ($tidCataMap as $tid => $map) {
            $view[$map][] = array('tid' => $tid, 'n_pic_file' => "http://imgst-dl.meilishuo.net/" . $pictures[$twitters[$tid]]['n_pic_file']);       
        }
	    $view['total'] = $total;
        $this->view = $view;
        return TRUE;
    }

	private function getTwitters($tids) {
        if (empty($tids)) {
            return array(); 
        }
		$col = array('twitter_id','twitter_images_id');
		$twitterAssembler = new Twitter($col);
		$twitterInfo = $twitterAssembler->getTwitterByTids($tids);
        $twitters = array();
		foreach ($twitterInfo as $key => $value) {
			$twitters[$value['twitter_id']] = $value['twitter_images_id'];
		}
        return $twitters;
	}

    private function getPictures($pids) {
		if (empty($pids)) {
			return array();	
		}
        //有图片的pictures
		$col = array('picid','n_pic_file');
		$pictureAssembler = new Picture($col);
		$pictures = $pictureAssembler->getPictureByPids($pids);
		$pictures = \Snake\Libs\Base\Utilities::changeDataKeys($pictures, 'picid');
		return $pictures;
	}

}
