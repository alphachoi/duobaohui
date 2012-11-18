<?php
namespace Snake\Modules\Goods;

Use \Snake\Package\Twitter\Twitter;
Use \Snake\Package\Goods\Popular;
Use \Snake\Libs\Sphinx\SphinxClient AS SphinxClient;

/**
 * 删除24小时和7天最热的宝贝接口
 * @package goods
 * @author weiwang
 * @since 2012.11.08
 * @example curl 
 */
class Pop24_delete extends \Snake\Libs\Controller{

	public function run() {
		//tids = 1,2,3,4,5
		$tids = isset($this->request->REQUEST['tids']) ? $this->request->REQUEST['tids'] : "";
		if (empty($tids)) {
			$response['ok'] = "empty tids";
			$this->view = $response;
			return TRUE;
		}
		$tids = explode(",", $tids);
		$col = array('twitter_id','twitter_goods_id');
		$twitterAssembler = new Twitter($col);
		$twitterInfo = $twitterAssembler->getTwitterByTids($tids);
        $twitterInfo = \Snake\Libs\Base\Utilities::changeDataKeys($twitterInfo, 'twitter_id');

		$retTotal = 0;                     
		$indexes = array('goods_id_dist',
                         'goods_id_oneday_verify',
                         'goods_id_business');

		//删除24小时的redis
		$popular = new Popular();
		$popular->setData();
		$popular->removeTid($tids);
		//从所有索引中删除
		$columes = array('goods_id_attr');
		foreach ($indexes as $index) {
			//$values = array($gid=>array(0));
			foreach ($tids as $tid) {	
				if (empty($tid)) {
					continue;
				}
				$values = array($twitterInfo[$tid]['twitter_goods_id'] => array(0));
				foreach($nodes as  $v) {
					$sc = new SphinxClient();
					$sc->SetServer($v['HOST'],$v['PORT']);
					$ret = $sc->UpdateAttributes($index, $columes, $values);
					$retTotal += $ret;
				}    
			}
		}
		$response['ok'] = "success";
        if ($retTotal < 0){
			$response['ok'] = "update 0";
        }    
		$this->view = $response;
		return TRUE;
	}

}
