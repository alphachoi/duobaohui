<?php
namespace Snake\Modules\Goods;

Use \Snake\Libs\Sphinx\SphinxClient AS SphinxClient;
Use \Snake\Package\Twitter\Twitter;

/**
 * 更新搜索的相关属性
 * @package goods
 * @author weiwang
 * @since 2012.11.06
 */

class Sphinx_update extends \Snake\Libs\Controller{

	public function run() {
		//tid,rank;tid,rank;
		$twitterLikeMap= isset($this->request->REQUEST['twitterLikeMap']) ? $this->request->REQUEST['twitterLikeMap'] : "";
		//解析map
		$maps = array();
		if (!empty($twitterLikeMap)) {
			$likeMap = explode(";", $twitterLikeMap);
			foreach ($likeMap as $map) {
				if (!empty($map)) {
					$pair = explode(",", $map);
					$maps[$pair[0]] = $pair[1];	
				}
			}
		}
		//$maps = array(504031066 => 303);
		if (empty($maps)) {
			$response['ok'] = "empty maps";
			$this->view = $response;
			return TRUE;
		}
        $nodes = array();
        if (isset($GLOBALS['SPHINX']['SLAVE'])) {
            $nodes = array_merge($nodes, $GLOBALS['SPHINX']['SLAVE']);
        }    
        $col = array('twitter_id','twitter_goods_id');
		$twitterAssembler = new Twitter($col);
        $tids = array_keys($maps);
		$twitterInfo = $twitterAssembler->getTwitterByTids($tids);
        $twitterInfo = \Snake\Libs\Base\Utilities::changeDataKeys($twitterInfo, 'twitter_id');
        $retTotal = 0;                     
		$index = "goods_id_dist";
		$columes = array('rank_like');
		//$values = array($gid=>array(0));
		foreach ($maps as $tid => $rankLike) {	
			$values = array($twitterInfo[$tid]['twitter_goods_id'] => array($rankLike));
			foreach($nodes as  $v) {
				$sc = new SphinxClient();
				$sc->SetServer($v['HOST'],$v['PORT']);
				$ret = $sc->UpdateAttributes($index, $columes, $values);
				$retTotal += $ret;
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
