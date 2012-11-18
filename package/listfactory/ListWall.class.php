<?php
/**
 * 获得推的列表信息
 */
namespace Snake\Package\Listfactory;
use \Snake\Package\Twitter\Twitter;
use \Snake\Package\Picture\Picture;
use \Snake\Package\Picture\PictureConvert;
use \Snake\Package\User\User;
use \Snake\Package\Goods\Goods;
use \Snake\Package\Twitterstat\TwitterStat;
use \Snake\Package\Act\Act;
use \Snake\Package\Group\GroupTwitters;
use \Snake\Libs\Base\MultiClient;

class ListWall {
	//获取的推ids
	private $tids = array();
	//用户id
	private $uid = 0;
	//是否为活动页的列表
	private $isAct = 0;
	//判断是否是杂志设
	private $isGroup = 0;


	public function setTids($tids) {
		$this->tids = $tids;
	}
	public function setUid($uid) {
		$this->uid = $uid;	
	}
	public function setIsAct($isact) {
		$this->isAct = $isact;	
	} 
	public function setIsGroup($isgroup) {
		$this->isGroup = $isgroup;	
	}

	public function getList() {
		$twitters = $this->getTwitter();
		if (empty($twitters)) return array();
		foreach ($twitters as $key => $twitter) {
			$tuids[$twitter['twitter_author_uid']] = $twitter['twitter_author_uid'];	
			!$twitter['twitter_images_id'] || $pids[] =  $twitter['twitter_images_id'];
			!$twitter['twitter_goods_id'] || $gids[] = $twitter['twitter_goods_id'];
		}
		$goods = $this->getGoods($gids);
		$pictures = $this->getPicture($pids);
		$users = $this->getUser($tuids);
		$likes = TwitterStat::objects()->filter($this->tids)->get();
		$groups = $this->groupAction();
		$acts = $this->actAction();
		$likedInfo = $this->_getLikedInfo();
		foreach ($twitters as $key => &$twitter) {
			if (isset($goods[$twitter['twitter_goods_id']])) {
				$twitter['ginfo'] = $goods[$twitter['twitter_goods_id']];
			}
			if (isset($pictures[$twitter['twitter_images_id']])) {
				$twitter['pinfo'] = $pictures[$twitter['twitter_images_id']];	
			}
			if (isset($users[$twitter['twitter_author_uid']])) {
				$twitter['uinfo'] = $users[$twitter['twitter_author_uid']];	
			}
			if (isset($likes[$twitter['twitter_id']])) {
				$twitter['count_like'] = $likes[$twitter['twitter_id']]['count_like'];
				$twitter['discuss_num'] = $likes[$twitter['twitter_id']]['discuss_num']; 
			}
			if (isset($groups[$twitter['twitter_id']])) {
				$twitter['elite'] = $groups[$twitter['twitter_id']]['elite'];	
			}
			if (isset($acts[$twitter['twitter_id']])) {
				$twitter['elite'] = $acts[$twitter['twitter_id']]['show_type'];	
			}
			if (isset($likedInfo[$twitter['twitter_id']])) {
				$twitter['liked'] = 0;
				if (!empty($likedInfo[$twitter['twitter_id']])) {
					$twitter['liked'] = 1;
				}
			}
		}
		return $twitters;
	}
	//活动处理
	public function actAction() {
		if (!$this->isAct) {
			return array();
		}	
		$actHelper = new Act();
		$acts = $actHelper->getActTwitters($this->tids, 'show_type');
		return $acts;
	}
	//杂志社处理
	public function groupAction() {
		if (!$this->isGroup) {
			return array();	
		}	
		//获得杂志设推的信息
		$groupHelper = new GroupTwitters();
		$colum = array('twitter_id', 'elite');
		$elite = $groupHelper->getGroupTwitter($this->tids, $colum);
		return $elite;
	}
	//获得宝贝的信息
	public function getGoods($gids) {
		if (empty($gids)) {
			return array();	
		}
		$col = array('goods_id','goods_price','goods_title','goods_pic_url','goods_url');
		$goodsHelper = new Goods($col);
		$goodsInfo = $goodsHelper->getGoodsByGids($gids);
		$goodsInfo = \Snake\Libs\Base\Utilities::changeDataKeys($goodsInfo, 'goods_id');
		return $goodsInfo;
	}
	//获得用户信息
	public function getUser($tuids) {
		$col = array("nickname","user_id","avatar_c");
		$userHelper = new User();
		$users = $userHelper->getUserInfos($tuids, $col);
		return $users;
	}
	//获得图片信息
	public function getPicture($pids) {
		if (empty($pids)) {
			return array();
		}
		$col = array('picid','n_pic_file','nwidth','nheight');
		$pictureHelper = new Picture($col);
		$pictures = $pictureHelper->getPictureByPids($pids);
		foreach ($pictures as $key => $picture) {
			$pid = $picture['picid'];
			$pHelper = new PictureConvert($picture['n_pic_file']);
			$pictures[$pid]['n_pic_file'] = $pHelper->getPictureO();
			$pictures[$pid]['l_pic_file'] = \Snake\Libs\Base\Utilities::getPictureUrl($picture['n_pic_file'], 'l');
			$pictures[$pid]['s_pic_file'] = \Snake\Libs\Base\Utilities::getPictureUrl($picture['n_pic_file'], 'f');
		}
		return $pictures;
	}
	//获得推信息
	public function getTwitter() {
		$colum = array('twitter_id','twitter_author_uid','twitter_show_type','twitter_images_id','twitter_source_tid','twitter_htmlcontent','twitter_goods_id','twitter_create_time');
		$twitterHelper = new Twitter($colum);
		$twitterInfo = $twitterHelper->getTwitterByTids($this->tids);
		return $twitterInfo;
	}
	
	//判断用户是否喜欢过推
	private function _getLikedInfo() {
		if (empty($this->uid)) {
			return FALSE;
		}
		$client = MultiClient::getClient($this->uid);
        $myCollectInfo = array();
		$collect = array(
			'multi_func' => 'twitter_likes_state',
			'method' => 'GET',
			'twitter_id' => implode(',', $this->tids),
			'user_id' => $this->uid,
			'self_id' => $this->uid,
		);	
		list($myCollectInfo) = $client->router(
			array($collect)
		);
		return $myCollectInfo;
		print_R($myCollectInfo);exit;
	}

	
	
	
	
	
	
	
	
}
