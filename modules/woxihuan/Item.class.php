<?php
namespace Snake\Modules\Woxihuan;

use \Snake\libs\Cache\Memcache AS Memcache;
use \Snake\Package\Group\GroupSquare AS GroupSquare;
use \Snake\Package\Group\GroupMainCatalog AS GMainCatalog;
use \Snake\Package\User\UserFollowGroup;
use \Snake\Package\Group\AssembleGroup AS AssembleGroup; 
use \Snake\Package\Group\Groups;
use \Snake\Package\Group\GroupTwitters;
use \Snake\Package\Twitter\Twitter;
use \Snake\Package\Goods\Goods;
use \Snake\Package\Search\SegWords;
use \Snake\Package\User\User;


class Item extends \Snake\Libs\Controller{

	private $twitterId = NULL;
	private $volumeId = NULL;
	
	public function run() {
		if (!$this->init()){
			return FALSE;
		}
		$tIds = array($this->twitterId);
		$vId = $this->volumeId;
		$volumeName = array('发型那些事', '约会穿衣手册', '日韩潮流速递', '街拍那些范儿', '傻瓜，让我给你一个家', '吃货都来这里勾搭', '家有萌宠', '美女世界' , '伦敦奥运时尚特刊');
		$col = array('twitter_id','twitter_author_uid','twitter_show_type','twitter_images_id','twitter_source_tid','twitter_htmlcontent','twitter_goods_id', 'twitter_content', 'twitter_create_time');
        $twitterHelper = new Twitter($col);
        $twitterInfos = $twitterHelper->getTwitterByTids($tIds);
		$twitterPics = $twitterHelper->getPicturesByTids($tIds, "O");
		$col = array('goods_id','goods_price','goods_title','goods_pic_url');
		$goodsHelper = new Goods($col);
		foreach ($twitterInfos AS $key => $value) {
			$gIds[] = $twitterInfos[$key]['twitter_goods_id'];
		}
		$goodsInfos = $goodsHelper->getGoodsByGids($gIds);
		$title = $this->getSEO($twitterInfos[0]['twitter_id'], $twitterInfos[0]['twitter_show_type'], $twitterInfos[0]['twitter_source_tid'], $twitterInfos[0]['twitter_goods_id']);
		
		//assemble twitter
		$tInfo = array();
		$gInfos = array();
		foreach ($goodsInfos AS $key => $value) {
			$goodsId = $goodsInfos[$key]['goods_id'];
			$gInfos[$goodsId] = $goodsInfos[$key];
		}

		foreach ($twitterInfos AS $key =>$value) {
			$tInfo[$key]['twitter_id'] = $twitterInfos[$key]['twitter_id'];
			if (!empty($twitterInfos[$key]['twitter_goods_id'])) {
				$goodsId = $twitterInfos[$key]['twitter_goods_id'];
				$tInfo[$key]['title'] = $gInfos[$goodsId]['goods_title'];
			}
			else  {
				$tInfo[$key]['title'] = $title;
			}
			$tInfo[$key]['category'] = '服饰搭配';
			$tInfo[$key]['pic_url'] = $twitterPics[$this->twitterId]['n_pic_file'];
			$tInfo[$key]['url'] = "http://www.meilishuo.com/share/" . $this->twitterId . "?frm=woxihuan_" . $vId;
			$attrs = SegWords::segword($tInfo[$key]['title']);
            $comp_len = create_function('$a, $b', 'return(strLen($a) < strLen($b));');
			if (is_array($attrs)) {
				usort($attrs, $comp_len);
				$times = count($attrs) <= 5 ? count($attrs) : 5;
				$keyword = NULL;
				for($i=0; $i< $times; $i++) {
					if ($i != 0 ) {
						$keyword .= ', ';
					}
					$keyword .= $attrs[$i];
				}   
			}
			else {
				$keyword = $tInfo[$key]['title'];
			}
            $tInfo[$key]['keywords'] = rtrim($keyword, ' ');
			$tInfo[$key]['time'] = date('Y-m-d H:i:s',$twitterInfos[$key]['twitter_create_time']);	
			$tInfo[$key]['volume_name'] = $volumeName[$vId];
		}
		
		$xmlDoc = new \DOMDocument("1.0");
        foreach ($tInfo AS $key => $value) {
			$xmlDoc = new \DOMDocument("1.0");
            $xmlDoc->formatOutput = true;
			$xmlstr = "<?xml version='1.0' encoding='utf-8' ?><doc></doc>";
			$xmlDoc->loadXML($xmlstr); 
            $x = $xmlDoc->getElementsByTagName("doc");

			$qid = $xmlDoc->createElement("qid");
			$x->item(0)->appendChild($qid);
            $time = $xmlDoc->createTextNode('120240445');
            $qid->appendChild($time);

			$volume = $xmlDoc->createElement("Volume");
			$name = $xmlDoc->createElement("name");
			$x->item(0)->appendChild($volume);
			$volume->appendChild($name);
            $time = $xmlDoc->createTextNode($tInfo[$key]['volume_name']);
            $name->appendChild($time);

			$bookmark = $xmlDoc->createElement("bookmark");
			$x->item(0)->appendChild($bookmark);
			$title = $xmlDoc->createElement("title");
			$bookmark->appendChild($title);
            $time = $xmlDoc->createTextNode($tInfo[$key]['title']);
            $title->appendChild($time);

			$url = $xmlDoc->createElement("url");
            $bookmark->appendChild($url);
            $time = $xmlDoc->createTextNode($tInfo[$key]['url']);
			$url->appendChild($time);

			$category = $xmlDoc->createElement("category");
            $bookmark->appendChild($category);
            $time = $xmlDoc->createTextNode($tInfo[$key]['category']);
            $category->appendChild($time);

			$tag = $xmlDoc->createElement("tag");
			$bookmark->appendChild($tag);
            $time = $xmlDoc->createTextNode($tInfo[$key]['keywords']);
            $tag->appendChild($time);

			$createtime = $xmlDoc->createElement("createtime");
            $bookmark->appendChild($createtime);
            $time = $xmlDoc->createTextNode($tInfo[$key]['time']);
            $createtime->appendChild($time);

			$level = $xmlDoc->createElement("level");
			$bookmark->appendChild($level);
			$time = $xmlDoc->createTextNode("1");
			$level->appendChild($time);

			$content = $xmlDoc->createElement("content");
			$bookmark->appendChild($content);
			$p = $xmlDoc->createElement("p");
			$content->appendChild($p);
			$type = $xmlDoc->createElement("type");
			$p->appendChild($type);
			$time = $xmlDoc->createTextNode('picture');
			$type->appendChild($time);

			$text = $xmlDoc->createElement("text");
			$p->appendChild($text);
            $time = $xmlDoc->createTextNode($tInfo[$key]['pic_url']);
            $text->appendChild($time);
            $result = $xmlDoc->save("/tmp/item.xml");//home/linhuazhu/snake/first.xml");
        }
		$this->view = $xmlDoc->saveXML();
		return TRUE;
	}

	private function init() {
		$this->twitterId = $this->request->GET['tid'];
		$this->volumeId = $this->request->GET['vid'];
		if (!empty($this->twitterId)) {
			return TRUE;
		}
		else {
			return FALSE;
		}

	}

	private function getSEO($twitterId, $twitterType, $twitterSourceId = 0, $twitterGoodsId = 0) {

		$groupTwitterHelper = new GroupTwitters();
		$groupHelper = new Groups();
		$groupInfo = $groupTwitterHelper->getGroupTwitter(array($twitterId, $twitterSourceId));
		$aUserId = $groupInfo[$twitterId]['user_id'];
		$sUserId = $groupInfo[$twitterSourceId]['user_id'];
		$userHelper = new User();
		$authorInfo = $userHelper->getUserInfo($aUserId);
		$sourceAuthorInfo = $userHelper->getUserInfo($sUserId);
		$groupSEO = $groupInfo[$twitterId]['group_id'];
		$groupFROM = $groupInfo[$twitterSourceId]['group_id'];
		if (!empty($groupFROM)) {	
			$groupName = $groupHelper->getGroupInfo(array($groupSEO, $groupFROM));
		}
		else {
			$groupName = $groupHelper->getGroupInfo(array($groupSEO));
		}
		if (!empty($twitterSourceId)) {
			$col = array('twitter_id','twitter_author_uid','twitter_show_type','twitter_images_id','twitter_source_tid','twitter_htmlcontent','twitter_goods_id', 'twitter_content', 'twitter_create_time');
			$twitterHelper = new Twitter($col);
			$twitterInfo = $twitterHelper->getTwitterByTids(array($twitterSourceId));
			if (!empty($twitterInfo[0]['twitter_goods_id'])) {
				$col = array('goods_id','goods_price','goods_title','goods_pic_url');
				$goodsHelper = new Goods($col);
				$goodsInfo = $goodsHelper->getGoodsByGids(array($twitterInfo[0]['twitter_goods_id']));
			}
		}

        if ($twitterType == 2){ //发图片
            $pageTitle = "【图】" . $authorInfo['nickname'] . "分享到" . $groupName[$groupSEO]['name']. "杂志的图片 - 美丽说";
        }   
        elseif ($twitterType == 7){  //发宝贝
        }   
        elseif ($twitterType == 8) { //转发推
            $pageTitle = "【图】" . $authorInfo['nickname'] . "从@" . $sourceAuthorInfo['nickname'] . "的" . $groupName[$groupFROM]['name']. "杂志分享的图片 - 美丽说";
            if (!empty($twitterGoodsId)) {
                $pageTitle = "【图】" . $goodsInfo[0]['goods_title']. "被@" . $authorInfo['nickname'] . "转发 - 美丽说";
            }   
        }
		return $pageTitle;

	}
}
