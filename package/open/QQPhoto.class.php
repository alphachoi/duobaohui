<?php
namespace Snake\Package\Open;

use \Snake\Package\Group\GroupSquare AS GroupSquare;
use \Snake\Package\Group\Groups AS Groups;
use \Snake\Package\Group\GroupTwitters;
use \Snake\Package\Group\GroupUser AS GroupUser;
use \Snake\Package\Twitter\Twitter;
use \Snake\libs\Cache\Memcache;
use \Snake\Package\Goods\Goods;


class QQPhoto {
	
	const TWITTER_NUMBER = 144;

	private $boardId = NULL;
    private $groupIds = array(
                array(15058384, 13389941), 
                array(13178549), 
                //array(14047249, 12952415, 13672186, 13519294,
				//13656793,14156391, 13271059), 
                array(13046262, 13705407, 13173197, 13550624, 14107004, 13554995, 13024695), 
                array(13521046, 13480328, 10424), 
                //array(14235085, 12177400, 13253524, 12784567), 
                array(12880866), 
                array(14464366, 15013627, 14259804, 4181340, 12171492, 14188357, 13559429, 14344337, 13559667, 14596029, 14099245)
                );
	private $groupId = array(array(3,4),array(5),array(7),array(8),array(9),array(10),array(11, 37982), array(1));
	private $volumeName =
		array(
			array(
				'name' => '发型那些事',
				'description' => '发型是女孩子谈不完的话题，教你如何解决“头”等大事。',
				'category' => '时尚服饰'
				),
			array(
				'name' => '约会穿衣手册',
				'description' => '解决女孩约会的服饰搭配困扰。',
				'category' => '时尚服饰'
				),
			/*'日韩潮流速递',*/
			array(
				'name' => '街拍那些范儿', 
				'description' => '时尚、眼力尖锐、品位独特的街拍',
				'category' => '时尚服饰'
				),
			array(
				'name' => '傻瓜，让我给你一个家',
				'description' => '最细腻的、最爱家的女孩，diy装饰小达人们的聚集地。',
				'category' => '亲子家居'
				),
			/* '吃货都来这里勾搭',*/
			array(
				'name' => '我家有萌宠',
				'description' => '最萌的宠物集合，分享自己养宠的心得。',
				'category' => '风景宠物'
				),
			array(
				'name' => '黑白世界',
				'description' => '美女的黑白世界。',
				'category' => '美女'
			)
		);
	private $face = array("大爱","喜欢，呵呵","赞！","爱！", "好漂亮", "可爱，喜欢。","这个也稀饭呀~","推荐，我觉得很好看！", "喜欢这样的","好看吗","好看吧","好看呀","好看啊","好看死了","真好看",'[笑]','[泪汪汪]','[害羞]','[流泪]','[得意]','[酷]','[坏笑]','[猪头]','[转眼珠]','[刚巴德]','[长草]','[财迷]','[星星眼]','[白菜]','[鄙视]','[飞吻]','[色色]','[调皮]','[泪]','[汗]','[么么]','[如花]','[思考]','[小红心]');

	public function __construct($boardId) {
		$this->boardId = $boardId;

	}

	public function getDataIndex() {

	}

	public function getBoard() {
		$twitters = $this->getTwitter();
		$twitterInfos = $this->getTwitterInfo($twitters);
		if ($this->boardId != 5 && $this->boardId != 4 && $this->boardId != 0 && $this->boardId != 2) {
			$piecesHelper = new Pieces();
			$twitterInfos = $piecesHelper->getPageTitle($twitterInfos);
		}
		$boardXml = $this->getBoardXml($twitterInfos);
		return $boardXml;
	}

	public function getTwitter() {
		$boardId = $this->boardId;
		$groupIds = $this->groupIds[$boardId];
		$startTime = strtotime("2012-8-13");
		if ($boardId == 4) {
			$startTime = strtotime("2012-8-13");
		}
		if ($boardId == 0) {
			$startTime = strtotime("2012-8-13");
		}
		$nowTime = time();
		$cutTime =strtotime("2012-8-13");
		$cutsetTime = intval(($cutTime - $startTime)/(3600*24));
		$offsetTime = intval(($nowTime - $cutTime)/(3600*24));
		$offset = ($cutsetTime * self::TWITTER_NUMBER) + $offsetTime*50;
		$limit = 50;
		if ($this->boardId == 1) {
			$twitters = $this->getSpecialTwitters($cacheKey, $offset, $limit);
			if(!empty($twitters)) {
				return $twitters;
			}
		}
		$groupHelper = new GroupTwitters();
		$twitters = $groupHelper->getGroupTwittersByGroupIdsNoCache($groupIds, array('twitter_id', 'group_id', 'elite'), $offset, 144, "twitter_id ASC");
		$tIds = array();
		foreach ($twitters AS $key => $value) {
			$tIds[] = $twitters[$key]['twitter_id'];
		}
		return $tIds;
	}
	
	public function getTwitterInfo($twitters = array()) {
		if (empty($twitters)) {
			return FALSE;
		}
		$col = array('twitter_id', 'twitter_author_uid', 'twitter_show_type', 'twitter_source_tid', 'twitter_goods_id', 'twitter_create_time', 'twitter_htmlcontent');
		$twitterHelper = new Twitter($col);
		$twitterInfos = $twitterHelper->getTwitterByTids($twitters);
		$twitterPics = $twitterHelper->getPicturesByTids($twitters, "O");
		$col = array('goods_id','goods_price','goods_title','goods_pic_url');
		$goodsHelper = new Goods($col);
		$gIds = array();
		foreach ($twitterInfos AS $key => $value) {
			$gIds[] = $twitterInfos[$key]['twitter_goods_id'];
		}
		$goodsInfos = $goodsHelper->getGoodsByGids($gIds);
		$gInfos = array();
		foreach ($goodsInfos AS $key => $value) {
			$gInfos[$goodsInfos[$key]['goods_id']]['goods_title'] = $goodsInfos[$key]['goods_title'];
			$gInfos[$goodsInfos[$key]['goods_id']]['goods_pic_url'] = $goodsInfos[$key]['goods_pic_url'];
			$gInfos[$goodsInfos[$key]['goods_id']]['goods_price'] = $goodsInfos[$key]['goods_price'];
		}

        foreach ($twitterInfos AS $key => $value) {
            $goodsId = $twitterInfos[$key]['twitter_goods_id'];
            $twitterInfos[$key]['goods_price'] = "0.00";
            if (!empty($goodsId)) {
                $twitterInfos[$key]['goods_title'] = $gInfos[$goodsId]['goods_title'];
                $twitterInfos[$key]['goods_pic_url'] = $gInfos[$goodsId]['goods_pic_url'];
                $twitterInfos[$key]['goods_price'] = $gInfos[$goodsId]['goods_price'];
            }   
            $twitterInfos[$key]['pic_url'] = $twitterPics[$twitterInfos[$key]['twitter_id']]['n_pic_file'];
            if (!empty($twitterInfos[$key]['twitter_htmlcontent'])) {
                $twitterInfos[$key]['page_title'] = htmlspecialchars_decode(strip_tags($twitterInfos[$key]['twitter_htmlcontent']));
            }   
            if (empty($twitterInfos[$key]['twitter_htmlcontent']) || trim($twitterInfos[$key]['page_title']) == "·" || trim($twitterInfos[$key]['page_title']) == "." || trim($twitterInfos[$key]['page_title']) == '`' || empty($twitterInfos[$key]['page_title'])) {
                $num = rand(0,38);
                $twitterInfos[$key]['page_title'] = $this->face[$num];
            }
            if (($this->boardId == 5 || $this->boardId == 4) && !empty($twitterInfos[$key]['twitter_goods_id'])) {
                unset($twitterInfos[$key]);
            }
        }
  
		return $twitterInfos;
	}

	private function getBoardXml($twitterInfos = array()) {
		if(empty($twitterInfos)) {
			return array();
		}
		shuffle($twitterInfos);
		
		$xmlDoc = new \DOMDocument();
		$xmlDoc->formatOutput = true;
		$xmlstr = "<?xml version='1.0' encoding='utf-8' ?><sdd></sdd>";
		$xmlDoc->loadXML($xmlstr); 
		$x = $xmlDoc->getElementsByTagName('sdd');
		$rootNode = $x->item(0);
		$this->createNode($xmlDoc, $rootNode, "provider", "qqphoto");
		$this->createNode($xmlDoc, $rootNode, "version", "1.0");

		$boardNode = $this->createNode($xmlDoc, $rootNode, "board");
		$vName = $this->volumeName[$this->boardId];		
		$nodeArray = array(
			'name' => $this->volumeName[$this->boardId]['name'],
			'desc' => $this->volumeName[$this->boardId]['description'],
			'category' => $this->volumeName[$this->boardId]['category'],
			'qq'   => '754748622',
			'create' => '0'
			);
		$this->createBlock($xmlDoc, $boardNode, $nodeArray);

		$picListNode = $this->createNode($xmlDoc, $rootNode, "piclist");
		foreach ($twitterInfos AS $key => $value) {
			$itemNode = $this->createNode($xmlDoc, $picListNode, "item");
			$nodeArray = array();
			$nodeArray = array(
				'url' => $twitterInfos[$key]['pic_url'],
				'desc' => $twitterInfos[$key]['page_title'],
				'link' => MEILISHUO_URL . '/share/' . $twitterInfos[$key]['twitter_id'] . '?frm=qqphoto_' . $this->boardId,
				'price' => $twitterInfos[$key]['goods_price'],
				'source' => "meilishuo.com"
				);
			$this->createBlock($xmlDoc, $itemNode, $nodeArray);			
		}
		$path = "/home/work/webdata/open/qq/board" . $this->boardId . "_" . date("YmdH") . ".xml";
		$result = $xmlDoc->save($path);//home/linhuazhu/snake/first.xml");

		return $xmlDoc->saveXML();
	}
	
	private function createNode($xmlDoc, $parentNode, $nodeName, $nodeValue = "") {
		$childNode = $xmlDoc->createElement($nodeName);	
		$parentNode->appendChild($childNode);
		if (!empty($nodeValue)) {
			$text = $xmlDoc->createTextNode($nodeValue);
			$childNode->appendChild($text);
			return TRUE;
		}
		return $childNode;
	}
	
	private function createBlock($xmlDoc, $parentNode, $nodeArray) {
		foreach ($nodeArray AS $key => $value) {
			$this->createNode($xmlDoc, $parentNode, $key, $value);
		}
		return TRUE;
	}

	private function getSpecialTwitters($cacheKey, $offset, $limit) {
		$cacheKey = "QQphotos:boardId:1";
		$cacheHelper = Memcache::instance();
		$tIds = $cacheHelper->get($cacheKey);
		if (!empty($tIds)) {
			$tIds = array_slice($tIds, $offset, $limit);
		}
		return $tIds;
	}
}


