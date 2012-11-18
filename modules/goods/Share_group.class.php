<?php
namespace Snake\Modules\Goods;
Use \Snake\Package\Group\GroupTwitters;
Use Snake\Package\Group\Groups;
Use \Snake\Package\Twitter\Twitter;
Use \Snake\Package\Recommend\Recommend;
Use \Snake\Package\Goods\KeyWords;
Use \Snake\Package\Goods\Attribute;
Use \Snake\Package\Goods\Goods;
Use \Snake\Package\Search\SegWords;
Use \Snake\Package\Goods\AttrSquareMemcache;
Use \Snake\Package\Base\IdentityObject;
Use \Snake\Package\Manufactory\Attrmix;

/**
 * 单推页面的所在杂志
 * @author weiwang
 * @since 2012.08.31
 * @example curl snake.mydev.com/goods/share_group?tid=74090164 //有所在杂志的 
 * @example curl snake.mydev.com/goods/share_group?tid=7168973 //没有所在杂志
 */
class Share_group extends \Snake\Libs\Controller{

	public function run() {
		$tid = isset($this->request->REQUEST['tid']) ? (int)$this->request->REQUEST['tid'] : 0;
		$userId = $this->userSession['user_id'];
		$groupTwitter = $this->getGroupTwitter($tid);	
		$groupId = 0;
		if (!empty($groupTwitter)) {
			$groupId = $groupTwitter[$tid]['group_id'];
		}
		$twitterObj = NULL;
		//如果有杂志社则显示杂志社九宫格
		if (!empty($groupId)) {
			$group = new Groups();
			$ninePicture = $group->getGroupSquareInfo(array($groupId), $userId);
			if (!empty($ninePicture)) {
				$ninePicture[$groupId]['is_group'] = 1;
				$this->view = $ninePicture[$groupId];
				return TRUE;
			}
			else {
				$twitterObj = $this->getTwitter($tid);
			}
		}
		//否则显示推所在属性词
		else {
			$twitterObj = $this->getTwitter($tid);
		}
		
		if (!empty($twitterObj)) {
				$goodsObj = $this->getGoods($twitterObj);
				$ninePicture = $this->getAttrSquare($goodsObj, $twitterObj);
				$ninePicture[0]['is_group'] = 0;
				$this->view = $ninePicture[0];
		}
		else {
			$this->view = array();
		}

	}

	public function getAttrSquare($goodsObj, $twitterObj) {
		$title = '';
		$title = strip_tags($twitterObj->getTwitterHtmlcontent());
		if (!empty($goodsObj)) {
			$title = strip_tags($goodsObj->getGoodsTitle());
			$gid = $goodsObj->getId();
		}
		$segWords = SegWords::segword($title);
		if (!empty($segWords)) {
			foreach ($segWords as &$word) {
				$word = "'" . $word . "'";
			}
		}
		$topAttr = array();
		$attrMixs = array(); 
		$wordIds = array();
		if (!empty($segWords)) {
			$identityObject = new IdentityObject();
			$identityObject->field('word_name')->in($segWords);
			$identityObject->col(array("word_id"));
			$keywords = new KeyWords();
			$keywordsObjs = $keywords->getKeywords($identityObject, TRUE);
			if (!empty($keywordsObjs)) {
				foreach ($keywordsObjs as $obj) {
					$wordIds[]['attr_id'] = $obj->getId();
				}    
			}
		}
		$recommend = new Recommend();
		if (empty($gid)) {
			$gid = -1;
		}    
		$retData = $recommend->getReAttrByGid($gid, $wordIds);

		if (!empty($retData)) {
			$topAttr = array_shift($retData);
			$attrMix = new Attrmix(array($topAttr['word_id']));
			$attrMixs = $attrMix->getAttrMix();
			$attrMixs = array_values($attrMixs);
		}
		else {
			$attrMixs = array();
		}

		return $attrMixs; 
	}

	/**
	 * 获取推信息
	 * @return array
	 * @access private 
	 */
	private function getTwitter($tid) {
		$twitterObj = NULL;
		if (!empty($tid)) {
			$twitterAssembler = new Twitter(array("twitter_id","twitter_goods_id","twitter_author_uid","twitter_htmlcontent","twitter_create_time","twitter_show_type","twitter_images_id","twitter_source_tid","twitter_source_uid"));
			$twitter = $twitterAssembler->getTwitterByTids(array($tid), TRUE);
			if (isset($twitter[0])) {
				$twitterObj = $twitter[0];	
			}
		}
		return $twitterObj;
	}

	/**
	 * 获取宝贝信息
	 * @return array
	 * @access private 
	 */
	private function getGoods($twitterObj) {
		$gid = $twitterObj->getTwitterGoodsId();
		$goodsObj = NULL;
		if (empty($gid)) {
			return $goodsObj;		
		}
		if (!empty($gid)) {
			$goodsAssembler = new Goods(array('goods_id','goods_price','goods_title','goods_pic_url','goods_url'));
			$goods = $goodsAssembler->getGoodsByGids(array($gid), TRUE);
			if (isset($goods[0])) {
				$goodsObj = $goods[0];
			}
		}
		return $goodsObj;
	}

	/**
	 * 获取杂志和推的关系
	 * @return array
	 * @access private 
	 */
	private function getGroupTwitter($tid) {
		$tids = array($tid);
		if (empty($tids)) {
			return array();
		}
		$col = array("group_id","twitter_id");
		$groupAssembler = new GroupTwitters();
		$groupTwitter = $groupAssembler->getGroupTwitter($tids, $col);
		return $groupTwitter;
	}

}
