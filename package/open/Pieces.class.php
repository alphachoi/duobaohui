<?php
namespace Snake\Package\Open;

use \Snake\Package\Group\GroupSquare AS GroupSquare;
use \Snake\Package\Group\Groups AS Groups;
use \Snake\Package\Group\GroupUser AS GroupUser;
use \Snake\Package\Twitter\Twitter;
use \Snake\Package\Goods\Goods;
use \Snake\Package\Group\GroupTwitters;

class Pieces {

	public static function getPageTitle($twitterInfos = array()) {
		if(empty($twitterInfos)) {
			return array();
		}
		
		$twitterSourceIds = array();
		foreach ($twitterInfos AS $key => $value) {
			if (!empty($twitterInfos[$key]['twitter_source_tid'])) {
				$twitterSourceIds[] = $twitterInfos[$key]['twitter_source_tid'];
			}
		}
		
		$col = array('twitter_id', 'twitter_author_uid', 'twitter_goods_id');
		$twitterHelper = new Twitter($col);
		$sTwitterInfo = $twitterHelper->getTwitterByTids($twitterSourceIds);
		$sInfo = array();	
		foreach ($sTwitterInfo AS $key => $value) {
			$sTid = $sTwitterInfo[$key]['twitter_id'];
			$sInfo[$sTid]['s_twitter_author_uid'] = $sTwitterInfo[$key]['twitter_author_uid'];
		}
		
		foreach ($twitterInfos AS $key => $value) {
			$sTid = $twitterInfos[$key]['twitter_source_tid'];
			$twitterInfos[$key]['s_twitter_author_uid'] = $sInfo[$sTid]['s_twitter_author_uid'];
		}

		$twitterInfos = self::getPageTitleBytInfo($twitterInfos);
        return $twitterInfos;

	}

	private static function getPageTitleBytInfo($twitterInfos) {
		if (empty($twitterInfos)) {
			return array();
		}
		$face = array("大爱","喜欢，呵呵","赞！","爱！", "好漂亮", "可爱，喜欢。","这个也稀饭呀~","推荐，我觉得很好看！", "喜欢这样的","好看吗","好看吧","好看呀","好看啊","好看死了","真好看",'[笑]','[泪汪汪]','[害羞]','[流泪]','[得意]','[酷]','[坏笑]','[猪头]','[转眼珠]','[刚巴德]','[长草]','[财迷]','[星星眼]','[白菜]','[鄙视]','[飞吻]','[色色]','[调皮]','[泪]','[汗]','[么么]','[如花]','[思考]','[小红心]');
		$groupHelper = new GroupTwitters();
		foreach ($twitterInfos AS $key => $value) {
			$twitterId = $twitterInfos[$key]['twitter_id'];
			$twitterType = $twitterInfos[$key]['twitter_show_type'];
			$twitterSourceId = $twitterInfos[$key]['twitter_source_tid'];
			$tInfo = $groupHelper->getTwitterToAndFrom($twitterId, $twitterType, $twitterSourceId);
			if ($twitterType == 2) { //发图片
				//$pageTitle = "【图】" . $tInfo['aNickname'] . "分享到" . $tInfo['tGroupName'] . "杂志的图片 - 美丽说";
				$pageTitle = $tInfo['tGroupName'];//. " 杂志的精彩图片 - 美丽说用户@" . $tInfo['aNickname'] . "的分享";
			}
			else if ($twitterType == 8) { //转发推
				//$pageTitle = "【图】" . $tInfo['aNickname'] . "从@" . $tInfo['sNickname'] . "的" . $tInfo['fGroupName'] . "杂志分享的图片 - 美丽说";
				$pageTitle = $tInfo['tGroupName'];// . " 杂志的精彩图片 - 美丽说用户@" . $tInfo['aNickname'] . "的分享";
				if (!empty($twitterInfos[$key]['twitter_goods_id'])) {
					$pageTitle = $twitterInfos[$key]['goods_title'];
				}
			}
			else if ($twitterType == 7) {
				$pageTitle = $twitterInfos[$key]['goods_title'];
			}	
			if (empty($pageTitle)) {
                $num = rand(0,38);
				$pageTitle = $face[$num];
                $twitterInfos[$key]['page_title'] = $face[$num];
			}
			$twitterInfos[$key]['page_title'] = $pageTitle;
		}
		
		return $twitterInfos;

	}
}
