<?php
namespace Snake\Package\Goods;

Use Snake\Package\Twitter\Twitter;
Use Snake\Package\Goods\Goods;
Use Snake\Package\Goods\Attribute;
Use Snake\Package\Goods\AttrWords;
Use Snake\Package\User\User;
Use Snake\Package\Manufactory\Attrmix;
Use Snake\Package\search\SegWords;
Use Snake\Package\recommend\Recommend;


class ShareAttrRecommend {
	private $tid = 0;

	private $invokeType = array(
		'maybelike' => array('offset' => 0, 'limit' => 3),
		'side' => array('offset' => 3, 'limit' => 7),
	);

	/**
	 * construct
	 * @param int
	 * @return NULL
	 */
	public function __construct($tid = 0) {
		$this->tid = $tid;	
	}

	/**
	 * aids => response data
	 * @param array aids
	 * @return array
	 */
	private function aidsPics($aids = array()) {
		$attrHelper = new Attribute();
		$tinfos = $attrHelper->getTwittersByAttrIds($aids, 0, 10, 'weight');
		if (empty($tinfos)) {
			return array();
		}
		$aidsRecomm = array_keys($tinfos);
		$aidsInfo = $this->getAidInfos($aidsRecomm);


		$tids = array();
		foreach ($tinfos as $key => $info) {
			$tids = array_merge($tids, $info['tid']);
		}
		$infos = $this->tidsToPics($tids, 'b');
		$response = array();
		foreach ($tinfos as $k => $i) {
			$wordTmp = array();	
			$wordTmp['id'] = $k;
			$wordTmp['name'] = $aidsInfo[$k]['word_name'];
			foreach ($i['tid'] as $tid) {
				if (empty($infos[$tid]['n_pic_file'])) { 
					continue;
				}
				$wordTmp['pics'][] = $infos[$tid]['n_pic_file'];
			}
			if (empty($wordTmp['pics'])) {
				continue;
			}
			$wordTmp['pics'] = array_slice($wordTmp['pics'], 0, 3);
			$wordTmp['num'] = $i['total'];
			$response[] = $wordTmp;
		}
		return $response;
	}

	/**
	 * tids => pics
	 * @param array
	 * @param string
	 * @return array
	 */
	private function tidsToPics($tids, $type = 'c') {
		if (empty($tids)) {
			return array();
		}
		$fields = array('twitter_id', 'twitter_goods_id', 'twitter_images_id');
		$twitterHelper = new Twitter($fields, array());		
		$infos = $twitterHelper->getPicturesByTids($tids, $type);
		return $infos;
	}

	/**
	 * 获取推荐属性词的属性词info
	 * @param array
	 * @return array
	 */
	private function getAidInfos($aidsRecomm = array()) {
		if (empty($aidsRecomm)) {
			return array();
		}
		$params = array();
		$params['word_id'] = $aidsRecomm;
		$params['isuse'] = 1;
		$aidsInfos = AttrWords::getWordInfo($params, 'word_name,word_id');
		$wordsInfos = array();
		if (empty($aidsInfos)) {
			return array();
		}
		foreach ($aidsInfos as $ainfo) {
			$wordInfos[$ainfo['word_id']] = $ainfo;
			$aidsInfo = $wordInfos;
		}
		return $aidsInfo;
	}


	/**
	 * get recommend by aids
	 * @param int 
	 * @param array
	 * @return array
	 */
	private function getRecommend($gid = -1, $aids, $invokeFor = 'meybelike') {

		if (empty($aids)) {
			$aids = array();
		}
		$aidsTmp = array();
		foreach ($aids as $a) {
			$aidsTmp[] =  array('attr_id' => $a);	
		}
		$aids = $aidsTmp;

		$recommendHelper = new Recommend();
		$recommend = $recommendHelper->getReAttrByGid($gid,$aids, 12);
		if (!empty($recommend)) {
			$recommend = array_slice($recommend, $this->invokeType[$invokeFor]['offset'], $this->invokeType[$invokeFor]['limit']);
		}
		return $recommend;
	}

	/**
	 * seg words and get the attrwords
	 * 
	 * @param string
	 * @return array
	 */
	private function titleToAttrId($title = '') {
		$segHelper = new SegWords();
		$aids = $segHelper->segwordToAttr($title);
		return $aids;
	}


	/**
	 * get twitter info
	 * @param int
	 * @return array
	 */
	private function getGoodsInfo($tid = 0) {
		$fields = array('twitter_id', 'twitter_goods_id', 'twitter_htmlcontent');
//		$fields = array("twitter_show_type","twitter_goods_id","twitter_author_uid","twitter_id","twitter_htmlcontent","twitter_images_id");
		$tids = array($this->tid);
		$twitterHelper = new Twitter($fields, array()); 
		$tinfo = $twitterHelper->getTwitterByTids($tids);
//		var_export($tinfo);die();
		$gid = $tinfo[0]['twitter_goods_id'];
		$gids = array($gid);
		$fields = array('goods_id', 'goods_title');
		$goodsHelper = new Goods($fields, array());
		$ginfo = $goodsHelper->getGoodsByGids($gids);
		$ginfo[0]['twitter_title'] = $tinfo[0]['twitter_htmlcontent'];
		return 	$ginfo[0];
	}

	/**
	 * ginfo => gid
	 * @param array
	 * @return int
	 * @todo let param be a object
	 */
	private function getGid($ginfo = array()) {
		$gid = -1;
		$gid = isset($ginfo['goods_id']) ? $ginfo['goods_id'] : -1;
		return $gid;	
	}

	/**
	 * ginfo => gTitle
	 * @param array
	 * @return string
	 * @todo let param be a object too
	 */
	private function getGTitle($ginfo) {
		if (empty($ginfo['goods_title'])) {
			if (empty($ginfo['twitter_title'])) {
				return '';	
			}
			else {
				return $ginfo['twitter_title'];
			}
		}
		return $ginfo['goods_title'];
	}

	/**	
	 * twitterInfo => pics, contents, nickname
	 * @param array
	 * @return array
	 */
	private function tinfoToDetailData($tinfo = array()) {
		if (empty($tinfo)) {
			return array();
		}
		$tids = array();
		$uids = array();
		$contents = array();
		foreach ($tinfo as $t) {
			$tinfos[$t['twitter_id']] = $t;
			$tids[] = $t['twitter_id'];
			$uids[] = $t['twitter_author_uid'];
		}
		$pics = $this->tidsToPics($tids, 'g');
		$uinfos = $this->uidsToUinfos($uids, array('nickname'));
		$detailData = $this->assembleForGoodsRecommend($pics, $uinfos, $tinfos);
		return $detailData;
	}

	/**
	 * 组装 数据 给 tinfoToDetailData
	 * @param array
	 * @param array
	 * @param array
	 * @return array
	 */
	private function assembleForGoodsRecommend($pics, $uinfos, $tinfos) {
		if (empty($pics)) {
			return array();	
		}
		$tids = array_keys($pics);	
		$returnData = array();
		foreach ($tids as $t) {
			$tmp['tid'] = $t;
			$tmp['uid'] = $tinfos[$t]['twitter_author_uid'];
			$tmp['pic'] = $pics[$t]['n_pic_file'];
			$tmp['content'] = $tinfos[$t]['twitter_htmlcontent'];
			$tmp['nick'] = $uinfos[$tinfos[$t]['twitter_author_uid']]['nickname'];
			if (empty($tmp['nick'])) {
				continue;
			}
			$returnData[] = $tmp;
		}
		if (!empty($returnData)) {
			$returnData = array_slice($returnData, 0, 6);
		}
		return $returnData;
	}	



	/**
	 * uids => uinfos(nickname)
	 * @param array
	 * @return array
	 */
	private function uidsToUinfos($uids, $fields = array('nickname')) {
		if (empty($uids)) {
			return array();
		}
		$userHelper = new User();
		$uinfos = $userHelper->getUserInfos($uids, $fields, TRUE);
		return $uinfos;
	}

	/**
	 * 获取单推页sidebar上的推荐属性response的method
	 * @param null
	 * @return array
	 */
	public function shareAttrRecommendSide() {
		if (empty($this->tid)) {
			return array();
		}
		$ginfo = $this->getGoodsInfo();
		$goodsTitle = $this->getGTitle($ginfo);
		$gid = $this->getGid($ginfo);

		$aids = $this->titleToAttrId($goodsTitle);
		$recommend = $this->getRecommend($gid, $aids, 'side');
		$aidsPics = $this->aidsPics($recommend);
		return $aidsPics;
	}

	/**
	 * 获取单推页的猜你喜欢的response的method
	 * @param null
	 * @return array
	 */
	public function shareAttrRecommendMaybeLie() {
		if (empty($this->tid)) {
			return array();
		}
		$ginfo = $this->getGoodsInfo();
		$goodsTitle = $this->getGTitle($ginfo);
		$gid = $this->getGid($ginfo);
		$aids = $this->titleToAttrId($goodsTitle);
		$recommend = $this->getRecommend($gid, $aids, 'maybelike');
		if (empty($recommend)) {
			return array();	
		}
		$reAids = array();
		foreach ($recommend as $word) {
			$reAids[] = $word['word_id'];
		}

		$attrMix = new Attrmix($reAids);
		$attrMixs = $attrMix->getAttrMix();
		$attrMixs = array_values($attrMixs);
		return $attrMixs;
	}

	/**
	 * 获取单推页的也许你还喜欢的response的method
	 * @param null
	 * @return array
	 */
	public function shareGoodsRecommend() {
		if (empty($this->tid)) {
			return array();
		}
		$ginfo = $this->getGoodsInfo();
		$goodsTitle = $this->getGTitle($ginfo);
		$gid = $this->getGid($ginfo);
		$aids = $this->titleToAttrId($goodsTitle);
		$recommendHelper = new Recommend();
		$recommendGids = $recommendHelper->getReGoodsByGid($gid, $aids, 12);
		
		$twitter = new Twitter(array("twitter_show_type","twitter_goods_id","twitter_author_uid","twitter_id","twitter_htmlcontent","twitter_images_id"));
		$tinfos = $twitter->getTwitterByGids($recommendGids, TRUE);
		$returnData = $this->tinfoToDetailData($tinfos);
		return $returnData;
	}


}
