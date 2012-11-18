<?php
namespace Snake\Modules\Woxihuan;


use \Snake\libs\Cache\Memcache AS Memcache;
use \Snake\Package\Group\GroupSquare AS GroupSquare;
use \Snake\Package\Group\GroupMainCatalog AS GMainCatalog;
use \Snake\Package\User\UserFollowGroup;
use \Snake\Package\Group\AssembleGroup AS AssembleGroup; 
use \Snake\Package\Group\GroupMainCatalogInfo AS GroupMainCatalogInfo;
use \Snake\Package\Group\GroupTwitters;
use \Snake\Package\Group\Groups;
use \Snake\Package\Group\GroupUser AS GroupUser;
use \Snake\Package\User\Helper\RedisUserFollow ;
use \Snake\Package\Msg\UpdateUserNotice;
use \Snake\Package\Msg\Helper\RedisUserNotification;
use \Snake\Package\Timeline\Timeline;
use \Snake\Package\Group\GetUserGroupSquares;
use \Snake\Package\Msg\Msg;
use \Snake\Package\Twitter\Twitter;



class Data_list extends \Snake\Libs\Controller {
	
	public function run() {
		$groupId = 36420;
		$groupIds = array(4,5,7,8,9,10,37982);
		$groupIds = array(
                array(12169279, 48410, 13763056, 14197674, 13312670, 11411, 15058384, 13389941, 15108979, 14767229, 14132854, 13271571, 14090760, 14359063, 14196558, 13614410, 71724, 14651843, 13960101, 13044495),//发型那些事 
                array(13243179, 13178958), //约会穿衣手册
                array(13483378, 13640409, 13273038, 4298606, 13916173, 14047249, 12952415, 13672186, 13519294, 13656793, 14156391, 13271059, 2509344, 12421068), //日韩潮流速递
                array(13631036, 14178829, 14243524, 13486593, 13046262, 13705407, 13173197, 13550624, 14107004, 13554995, 13024695, 12515865, 11892254), //街拍那些范儿
                array(13513841, 13046261, 14493789, 14230334, 7497919, 14100201, 13521046, 13480328, 10424, 13908945), //让我给你一个家
                array(14344170, 14016364, 12510703, 14235085, 12177400, 13253524, 12784567, 14487290, 14342463, 14087000), //吃货都来这里勾搭
                array(14096126, 13290364, 12943220,12880866, 13068446, 13247331, 13574887, 14096126),//我家有萌宠
				array(14408096, 14336612, 14103056, 13329033, 13496076, 320186, 14942568, 13388573, 14941506, 12542995, 14452069, 13390073, 26733, 13366810, 14305113, 14311578, 14470648, 14306784, 14551904, 13859271, 14942143, 14953584, 14464366, 15013627, 14259804, 4181340, 12171492, 14188357, 13559429, 14344337, 13559667, 14596029, 14099245, 14464366, 15013627, 14259804, 4181340, 12171492, 11646815, 13353486, 14820960, 14195964, 14651529, 88492, 14464391)//黑白世界
				//array(14671621, 15369193, 15398282)
        );

		$groupHelper = new GroupTwitters();
		$j = 0;
		//$startTime = strtotime("2012-7-");
		$nowTime = time();
		$cutTime =strtotime("2012-7-21");
		//$cutsetTime = intval(($cutTime - $startTime)/(3600*24));
		$offsetTime = intval(($nowTime - $cutTime)/(3600*24));
		$start = $offsetTime * 50;
		$limit = 50;
		foreach ($groupIds AS $key => $value) {
			if ($key != 1) { 
				$twitters = $groupHelper->getGroupTwittersByGroupIdsNoCache($groupIds[$key], array('twitter_id', 'group_id', 'elite'), $start, $limit, "twitter_id ASC");
			}
			/*else if ($key == 8) {
				$time = time();
				$cut = strtotime("2012-8-6");
				$offset = intval(($time- $cut)/(3600*24));
				$start_one = $offset * 50;
				$twitters = $groupHelper->getGroupTwittersByGroupIdsNoCache($groupIds[$key], array('twitter_id', 'group_id', 'elite'), $start_one, $limit, "twitter_id ASC");
			}*/
			else if ($key == 1) {
				$twitters = $this->getSpecialTwitters($start, $limit);
			}
			else {
				$time = time();
				$cut = strtotime("2012-7-30");
				$offset = intval(($time- $cut)/(3600*24));
				$start_one = $offset * 50;
				$twitters = $groupHelper->getGroupTwittersByGroupIdsNoCache($groupIds[$key], array('twitter_id', 'group_id', 'elite'), $start_one, $limit, "twitter_id ASC");
			}
			foreach ($twitters AS $k => $v) {
				$tIds[$j]['twitter_id'] = $twitters[$k]['twitter_id'];
				$tIds[$j]['category'] = $key;
				$tId[] = $twitters[$k]['twitter_id'];
				$j++;
			}

		}
		
		
		/*$i = 1;
		$j = 0;
		foreach ($twitters AS $twitter) {
			foreach ($twitter AS $key => $value) {
				$tIds[$j]['twitter_id'] = $twitter[$key]['twitter_id'];
				$tIds[$j]['category'] = $i;
				$tId[] = $twitter[$key]['twitter_id'];
				$j++;
			}
			$i++;
		}*/
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
			$linkUrl = 'http://open.meilishuo.com/woxihuan/item?tid=' . $tIds[$key]['twitter_id'] . '&vid=' . $tIds[$key]['category'] . "&token=af1bbeca07854055";
			//$cdata = $xmlDoc->createCDATASection($linkUrl);
			$li->appendChild($url);
			$text = $xmlDoc->createTextNode(iconv("GB2312","UTF-8",$linkUrl)); 
			//$url->appendChild($cdata);
			$url->appendChild($text);
			
			$createtime = $xmlDoc->createElement("createtime");
			$li->appendChild($createtime);
			$time = date('Y-m-d H:i:s',$twitterTime[$tIds[$key]['twitter_id']]);
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
		$this->view = $xmlDoc->saveXML();
		$path ="/home/work/webdata/open/woxihuan/data_list" . date("YmdH") . ".xml";
		$result = $xmlDoc->save($path);//home/linhuazhu/snake/first.xml");
		//$result = $xmlDoc->save("/tmp/first_one_here.xml");//home/linhuazhu/snake/first.xml");
		return TRUE;	
	}

    private function getSpecialTwitters($offset, $limit) {
        $cacheKey = "QQphotos:boardId:1";
        $cacheHelper = Memcache::instance();
        $tIds = $cacheHelper->get($cacheKey);
		if (!empty($tIds)) {
			$tIds = array_slice($tIds, $offset, $limit);
		}
		return $tIds;    
	} 


}
