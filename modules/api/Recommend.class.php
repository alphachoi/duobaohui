<?php
namespace Snake\Modules\Api;


use \Snake\libs\Cache\Memcache AS Memcache;
use \Snake\Package\Group\GroupSquare AS GroupSquare;
use \Snake\Package\Group\GroupMainCatalog AS GMainCatalog;
use \Snake\Package\User\UserFollowGroup;
use \Snake\Package\Group\AssembleGroup AS AssembleGroup; 
use \Snake\Package\Group\GroupMainCatalogInfo AS GroupMainCatalogInfo;
use \Snake\Package\Group\Groups AS Groups;
use \Snake\Package\Group\GroupUser AS GroupUser;
use \Snake\Package\User\Helper\RedisUserFollow ;
use \Snake\Package\Msg\UpdateUserNotice;
use \Snake\Package\Msg\Helper\RedisUserNotification;
use \Snake\Package\Timeline\Timeline;
use \Snake\Package\Group\GetUserGroupSquares;
use \Snake\Package\Msg\Msg;
use \Snake\Package\Twitter\Twitter;



class Recommend extends \Snake\Libs\Controller {
	
	public function run() {
		$groupId = 36420;
		$groupIds = array(4,5,7,8,9,10,37982);
		$volumeName = array('4' => '发型那些事', '5' => '约会穿衣手册', '7' => '日韩潮流速递', '8' => '街拍那些范儿', '9' => '傻瓜，让我给你一个家', '10' => '吃货都来这里勾搭', '37982' => '家有萌宠', '36998' => '黑白世界');
		$groupHelper = new Groups();
		$twitters = $groupHelper->getGroupTwitters($groupIds, array('twitter_id', 'group_id', 'elite'), $start = 0, $limit = 144);

		$i = 1;
		$j = 0;
		foreach ($twitters AS $twitter) {
			foreach ($twitter AS $key => $value) {
				$tIds[$j]['twitter_id'] = $twitter[$key]['twitter_id'];
				$tIds[$j]['category'] = $i;
				$tId[] = $twitter[$key]['twitter_id'];
				$j++;
			}
			$i++;
		}
		$twitterHelper = new Twitter(array('twitter_id', 'twitter_create_time'));
		$twitterInfos = $twitterHelper->getTwitterByTids($tId);
		$twitterTime = array();
		foreach ($twitterInfos AS $key => $value) {
			$twitterId = $twitterInfos[$key]['twitter_id'];
			$twitterTime[$twitterId] = $twitterInfos[$key]['twitter_create_time'];
		}


		$xmlDoc = new \DOMDocument();
		$xmlDoc->formatOutput = true;
		$xmlstr = "<?xml version='1.0' encoding='utf-8' ?><doc></doc>";
		$xmlDoc->loadXML($xmlstr); 
		shuffle($tIds);

		foreach ($tIds AS $key => $value) {
			$Root = $xmlDoc->documentElement;
			$x = $xmlDoc->getElementsByTagName('doc');
			$li = $xmlDoc->createElement("li"); 
			$x->item(0)->appendChild($li);

			$url = $xmlDoc->createElement("url");
			$linkUrl = 'http://l.xml.meilishuo.com/woxihuan/item?tid=' . $tIds[$key]['twitter_id'] . '&cataid=' . $tIds[$key]['category'];
			$cdata = $xmlDoc->createCDATASection($linkUrl);
			$li->appendChild($url);
			$text = $xmlDoc->createTextNode(iconv("GB2312","UTF-8",$linkUrl)); 
			$url->appendChild($cdata);
			
			$createtime = $xmlDoc->createElement("createtime");
			$li->appendChild($createtime);
			$time = date('Y-m-d H:i:s',$tInfos[$key]['twitter_create_time']);
			$text = $xmlDoc->createTextNode($time);
			$createtime->appendChild($text);

			$level = $xmlDoc->createElement("level");
			$li->appendChild($level);
			$text = $xmlDoc->createTextNode('1');
			$level->appendChild($text);

			$category = $xmlDoc->createElement("category");
			$li->appendChild($category);
			$text = $xmlDoc->createTextNode('服饰搭配');
			$category->appendChild($text);

		}
			//echo $xmlDoc->saveXML();
		$result = $xmlDoc->save("/tmp/first_one_here.xml");//home/linhuazhu/snake/first.xml");
		var_dump($result);
		exit;
		echo $xmlDoc->saveXML();
		exit;

		print_r($twitterInfos);exit;
		print_R($tIds);exit;
		
		
	}

}
