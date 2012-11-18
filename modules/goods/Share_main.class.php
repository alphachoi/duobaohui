<?php
namespace Snake\Modules\Goods;
Use \Snake\Package\Twitter\Twitter;
Use \Snake\Package\Goods\Goods;
Use \Snake\Package\Goods\GoodsShelf;
Use \Snake\Package\Manufactory\ShareRepin;
Use \Snake\Package\Picture\Picture;
Use \Snake\Package\User\User;
Use \Snake\Libs\Base\MultiClient;
Use \Snake\Package\Url\Url;
Use \Snake\Package\Cpc\Cpc;
Use \Snake\Package\Twitter\TwitterVerify;
Use \Snake\Package\Url\ShortUrl;
Use \Snake\Package\Manufactory\TimeConverter;
Use \Snake\Package\Manufactory\TwitterSource;
Use \Snake\Package\Base\IdentityObject;
Use \Snake\Package\Picture\PictureConvert;
Use \Snake\Package\Group\Groups;
Use \Snake\Package\Group\GroupTwitters;
Use \Snake\Package\Manufactory\ClassicCurl;
Use \Snake\Package\Cpc\CpcQueue;
Use \Snake\Package\Search\SegWords;
Use \Snake\Package\Base\MemcacheIdentityObject;
Use \Snake\Package\Goods\ShareMainMemcache;
Use \Snake\Package\Goods\TestFreeshipping;
Use \Snake\Package\Goods\TestSpecialoffer;
Use \Snake\Package\Goods\TestMoresimilar;

/**
 * 单推页面的主页面展现
 * @package goods 
 * @author weiwang
 * @since 2012.08.24
 * @example curl snake.mydev.com/goods/share_main?tid=74090164
 */
class Share_main extends \Snake\Libs\Controller{

	
	/**
	 * 推信息
	 *
	 * @var array
	 * @access private
	 *
	 */	
	private $twitter = array();	

	/**
	 * 是否是cpc的商品
	 *
	 * @var bool
	 * @access private
	 *
	 */	
	private $iscpc =  FALSE;

	/**
	 * 返回信息
	 *
	 * @var array
	 * @access private
	 *
	 */	
	private $share = array();

	/**
	 * 测试带的参数
	 * @var string
	 * @access private
	 */
	private $ump = '';

	private function init($tid) {
		//获取推信息
		$twitter = $this->getTwitter($tid);
		$this->twitter = $twitter[0];
	}
	public function run() {



		$tid = isset($this->request->REQUEST['tid']) ? (int)$this->request->REQUEST['tid'] : 0;
		$userId = $this->userSession['user_id'];
		$cpc = new Cpc();
		$this->iscpc = $cpc->isCpc($tid);
		$this->cpcFee($tid, $userId);
		if (empty($userId) ) {
			$memIdentityObject = new MemcacheIdentityObject(ShareMainMemcache::SHAREMAIN);
			$memIdentityObject->setSuffix(array($tid));
			$memcacheFinder = ShareMainMemcache::create($memIdentityObject);
			list($notCacheTids, $cacheData) = $memcacheFinder->get();
			if (!empty($cacheData[$tid]) && !isset($this->request->REQUEST['ump'])) {
				$this->view = $cacheData[$tid];
				return TRUE;
			}
		}
		//初始化
		$this->init($tid);
		//设置是否出404页面
		
		if ($this->setShow404($userId)) {
			$this->view = $this->share;
			return ;
		}
		//是否显示删除按钮
		$this->setShowDelete($userId);
		//是否是首都网警
		$this->setPolice($userId);

		//获取商品信息,返回obj
		$goodsObj = $this->getGoods();
		//获取图片信息,返回obj
		$pictureObj = $this->getPicture();
		//获取用户信息,返回数组
		$users = $this->getUsers();
		//设置$this->share['is_verify']
		$this->setVerify();
		//设置$this->share['likers']
		$this->setLikers();
		//设置$this->share['shelf']
		$this->setShelf();
		//获取收藏信息
		list($collectInfo, $myCollectInfo) = $this->getCollect($userId);	

		//为下架宝贝检测添加到集合
		$this->inSet($goodsObj);
		//获取连接信息,返回obj
		$urlObj = $this->getUrl($goodsObj);
		//设置$this->share['showLike']
		$this->setShowLike($userId);
		//设置$this->share['show_pic']	
		$this->setShowPic($pictureObj, $goodsObj);
		//设置图片原始连接$this->share['original']	
		$this->setOriginalPic($pictureObj);
		$this->share['twitter_id'] = $this->twitter['twitter_id'];
		//设置喜欢状态
		$this->share['beauty_me'] = 1;
		if (empty($myCollectInfo[$this->twitter['twitter_id']])) {
			$this->share['beauty_me'] = 0;
		}
		//用户发的内容
		$this->share['twitter_htmlcontent'] = $this->twitter['twitter_htmlcontent'];
		//过滤掉html标签后的内容，方便前段做一些处理
		$this->share['twitter_content'] = strip_tags($this->twitter['twitter_htmlcontent']);
		//设置时间
		$this->setCreateTime();
		//设置跳转url
		$this->setUrl($goodsObj, $userId, $pictureObj);	
		//设置来源
		$this->setSource();
		//设置显示模式(7=>宝贝推，2=>图片推，1=>文字或小红心)
		$this->setDisplayMode();

		$tid = $this->twitter['twitter_id'];
		$uid = $this->twitter['twitter_author_uid'];
		$stid = $this->twitter['twitter_source_tid'];
		$suid = $this->twitter['twitter_source_uid'];
		$this->share['twitter_show_type'] = $this->twitter['twitter_show_type'];
		$this->share['twitter_source_tid'] = $this->twitter['twitter_source_tid'];
		$this->setNums($collectInfo);

		$this->share['ginfo']['is_taobao'] = $this->isTaobao($urlObj);
		$this->share['ginfo']['price'] = 0;
		$this->share['ginfo']['title'] = "";
		if (!empty($goodsObj)) {
			$drainage['107567340'] = "¥69";
			$drainage['389255904'] = "¥49";
			$drainage['459387244'] = "¥75";
			$drainage['458732092'] = "¥89";
			$drainage['460222660'] = "¥68";
			$drainage['413783962'] = "¥198";
			$this->share['ginfo']['price'] = $goodsObj->getShareGoodsPrice();
			if (isset($drainage[$this->twitter['twitter_id']])) {
				$this->share['ginfo']['price'] = $drainage[$this->twitter['twitter_id']];
			}
			$this->share['ginfo']['title'] = $goodsObj->getGoodsTitle();
		}
		$this->share['uinfo']['user_id'] = $users[$uid]['user_id'];
		$this->share['uinfo']['nickname'] = $users[$uid]['nickname'];
		$this->share['uinfo']['avatar_c'] = $users[$uid]['avatar_c'];
		
		//设置repin 
		list($toGroupName, $fromGroupName) = $this->setRepin($suid, $users[$suid]['nickname']);
		//seo相关内容
		$this->forSeo($goodsObj, $users[$uid]['nickname'], $users[$suid]['nickname'], $toGroupName, $fromGroupName);


		if (trim((string)$this->request->REQUEST['ump']) != '') {
			$ump = trim((string)$this->request->REQUEST['ump']);
			$this->share = $this->ump($this->share, $ump);
		}

		if (empty($userId) && !isset($this->share['ump'])) {
			$cache[$this->twitter['twitter_id']] = $this->share;
			$memcacheFinder->put($cache);
		}

		$this->view = $this->share;
	}


	/**
	 *
	 *
	 *
	 */
	private function ump($share = array(), $ump = '') {
		if ('freeshipping' === $ump) {
			$testHelper = new TestFreeshipping(10, array(3));
			$intest = $testHelper->judgeTidInTetst($share['twitter_id']);
			if ( TRUE === $intest) {
				$share['ump'] = 'freeshipping';
			}
		}	
		else if ('specialoffer' === $ump) {
			$testHelper = new TestSpecialoffer(10, array(5));
			$intest = $testHelper->judgeTidInTetst($share['twitter_id']);
			$specialPrice = $testHelper->getSpecialByTid($share['twitter_id']);
			if ( TRUE === $intest && !empty($specialPrice)) {
				$share['ump'] = 'specialoffer';
				$share['ginfo']['specialprice'] = $share['ginfo']['price'];
				$share['ginfo']['price'] = $specialPrice;
			}
		}	
		else if ('moresimilar' === $ump) {
			$testHelper = new TestMoresimilar(10, array(9));
			$intest = $testHelper->judgeTidInTetst($share['twitter_id']);
			if ( TRUE === $intest) {
				$share['ump'] = 'moresimilar';
			}
		}
		return $share;	
	}

	/**
	 * 设置keywords,metadescription
	 * 
	 * @return TRUE
	 * @access private 
	 */
	private function forSeo($goodsObj, $nick, $sourceNick, $toGroupName, $fromGroupName) {

		!empty($goodsObj) && $goodsTitle = strip_tags($goodsObj->getGoodsTitle());

        //seo需求-start
        $metaDescription = strip_tags($this->twitter['twitter_htmlcontent']) . " - 美丽说用户@" . $nick . "的分享";
        $pageTitle = "【图】" . $goodsTitle . " - 美丽说";

        if ($this->twitter['twitter_show_type'] == 2){ //发图片
            $metaDescription = $toGroupName. "杂志的精彩图片 - 美丽说用户@" . $nick . "的分享";
            $pageTitle = "【图】" . $nick . "分享到" . $toGroupName . "杂志的图片 - 美丽说";
            $keyword = $nick . "," . "分享, 图片, 宝贝";
        }
        elseif ($this->twitter['twitter_show_type'] == 7){  //发宝贝
            $metaDescription = strip_tags($this->twitter['twitter_htmlcontent']) . " - 美丽说用户@" . $nick . "分享到杂志#" . $toGroupName . "#";
        }
        elseif ($this->twitter['twitter_show_type'] == 8) { //转发推
            $metaDescription = $toGroupName . "杂志的精彩图片 - 美丽说用户@" . $nick . "的分享";
            $pageTitle = "【图】" . $nick . "从@" . $sourceNick . "的" . $fromGroupName . "杂志分享的图片 - 美丽说";
            $keyword = $authorInfo['nickname'] . "," . "分享, 图片, 宝贝";
            if (!empty($shareInfo[$tid]['twitter_goods_id'])) {
                $metaDescription = strip_tags($shareInfo[$tid]['use_content']) . " - 美丽说用户@" . $authorInfo['nickname'] . "分享到杂志#" . $toGroupName . "#";
                $pageTitle = "【图】" . $goodsTitle . "被@" . $nick . "转发 - 美丽说";
            }
        }	

        //seo需求-end
        if ($this->share['twitter_show_type'] == 7 || ($this->share['twitter_show_type'] == 8 && !empty($this->share['twitter_goods_id']))) {
			$attrs = array();
			if (!empty($goodsObj)) {
				$attrs = SegWords::segword(strip_tags($goodsObj->getGoodsTitle()));
			}
            $comp_len = create_function('$a, $b', 'return(strLen($a) < strLen($b));');
            if (is_array($attrs)) {
                usort($attrs, $comp_len);
            }
            $times = count($attrs) <= 5 ? count($attrs) : 5;
            $keyword = NULL;
            for($i=0; $i< $times; $i++) {
                $keyword .= $attrs[$i];
                $keyword .= ' ';
            }
            $keyword = rtrim($keyword, ' ');
        }
		$this->share['pageTitle'] = $pageTitle;
		$this->share['metaDescription'] = $metaDescription;
		$this->share['keyword'] = $keyword;
		return TRUE;
	}

	/**
	 * 为首都网警开启删除功能
	 * 
	 * @return TRUE
	 * @access private 
	 */
	private function setPolice($userId) {
		$this->share['isPolice'] = 0;
		$police = 18185784;
		if ($userId == $police) {
			$this->share['isPolice'] = 1;
		}
		return TRUE;
	}

	/**
	 * 设置是否显示删除按钮
	 * 
	 * @return TRUE
	 * @access private 
	 */
	private function setShowDelete($userId) {
		$this->share['super'] = 0;
		$this->share['showDelete'] = 0; 
		if ($this->twitter['twitter_author_uid'] == $userId) {
			$this->share['showDelete'] = 1;
		}
		$white = array(765,1068659,2132529,7222759,6140112,4592358,7578993);
		if (in_array($userId, $white)) {
			$this->share['showDelete'] = 1;	
			$this->share['super'] = 1;
		}
		return TRUE;
	}

	/**
	 * 设置是否显示404页面
	 * 
	 * @return TRUE
	 * @access private 
	 */
	private function setShow404($userId) {
		$this->share['show404'] = 0;
		if (empty($this->twitter)) {
			$this->share['show404'] = 1;
		}
		$user = new User();
		$isBan = $user->getUserBaseInfo($userId, array('is_actived'));
		if ($isBan['is_actived'] == -2) {
			$this->share['show404'] = 1;
		}
		return $this->share['show404'];
	}
	/**
	 * 设置显示模式，前端好展示相应的模板
	 * @param private 
	 * @return True
	 */
	private function setDisplayMode() {
		$twitterShowType = $this->twitter['twitter_show_type'];
		$gid = $this->twitter['twitter_goods_id'];
		$pid = $this->twitter['twitter_images_id'];
		$mode = 2;
		switch ($twitterShowType) {
			case 2: 
				$mode = 2;break;
			case 7:
				$mode = 7;break;
			case 9:
				$mode = 9;break;
			default:
				$mode = 1;
		}
		if (in_array($twitterShowType , array(3,8))) {
			if (empty($gid)) {
				$mode = 2;
			}	
			else {
				$mode = 7;
			}
		}
		$this->share['mode'] = $mode;
	}

	/**
	 * 设置分享痕迹
	 * 
	 * @return array($toGroupName,$fromGroupName)
	 * @access private 
	 */
	private function setRepin($suid, $nick) {
		$stid = $this->twitter['twitter_source_tid'];
		$tid = $this->twitter['twitter_id'];
		$groupTwitter = $this->getGroupTwitter();
		//获取杂志名称
		$groupIds = \Snake\Libs\Base\Utilities::DataToArray($groupTwitter, "group_id");
		$groupNames = $this->getGroup($groupIds);
		$fromGroupId = $toGroupId = 0;
		$fromGroupName = $toGroupName = "";
		if (isset($groupTwitter[$stid])) {
			$fromGroupId = $groupTwitter[$stid]['group_id'];
			if (isset($groupNames[$fromGroupId])) { 
				$fromGroupName = $groupNames[$fromGroupId]['name'];
			}
			else {
				$fromGroupId = 0;	
			}
		}
		if (isset($groupTwitter[$tid])) {
			$toGroupId = $groupTwitter[$tid]['group_id'];
			if (isset($groupNames[$toGroupId])) {
				$toGroupName = $groupNames[$toGroupId]['name'];
			}
			else {
				$toGroupId = 0;
			}
		}
		//转发痕迹
		$repinTrack = new ShareRepin();
		$repinTrack->showType = $this->twitter['twitter_show_type'];
		$repinTrack->fromGroupId = $fromGroupId;
		$repinTrack->fromGroupName = $fromGroupName;
		$repinTrack->twitterGoodsId = $this->twitter['twitter_goods_id'];
		$repinTrack->toGroupId = $toGroupId;
		$repinTrack->toGroupName = $toGroupName;
		$repinTrack->sourceTwitterAuthor = $suid;
		$repinTrack->sourceTwitterAuthorNick = $nick;
		$this->share['repin'] = $repinTrack->getRepin();
		$this->share['group_name'] = $toGroupName;
		return array($toGroupName, $fromGroupName);
	}

	/**
	 * 设置推创建时间
	 * 
	 * @return TRUE
	 * @access private 
	 */
	private function setCreateTime() {
		//$timeConverter = new TimeConverter($this->twitter['twitter_create_time']);
		$this->share['twitter_create_time'] = date("m月d日 H:i", $this->twitter['twitter_create_time']);//$timeConverter->convert();
		return TRUE;
	}

	/**
	 * 设置count_like,count_discuss,count_forward字段
	 * 喜欢，评论，转发
	 * @return TRUE
	 * @access private 
	 */
	private function setNums($collectInfo) {
		$this->share['count_like'] = 0;
		if (!empty($collectInfo[$this->twitter['twitter_id']]['count_like'])) {
			$this->share['count_like'] = $collectInfo[$this->twitter['twitter_id']]['count_like'];
		}
		$this->share['count_discuss'] = $collectInfo[$this->twitter['twitter_id']]['count_discuss'];
		$this->share['count_forward'] = $collectInfo[$this->twitter['twitter_id']]['count_forward'];
		return TRUE;
	}

	/**
	 * 设置showLike字段
	 * 是否是当前用户自己的推
	 * @return TRUE
	 * @access private 
	 */
	private function setShowLike($userId) {
		$this->share['showLike'] = 1;	
		if ($userId == $this->twitter['twitter_author_uid']) {
			$this->share['showLike'] = 0;	
		}
		return TRUE;
	}

	/**
	 * 设置shelf字段
	 * 是否下架
	 * @return TRUE
	 * @access private 
	 */
	private function setShelf() {
		$shelf = 1;
		$showTypes = array(3,7,8);
		if (in_array($this->twitter['twitter_show_type'], $showTypes)) {
			$goodsShelf = new GoodsShelf();	
			$shelf = $goodsShelf->isOnshelf($this->twitter['twitter_goods_id']);
		}
		//是否下架	
		$this->share['shelf'] = $shelf;
		return TRUE;
	}

	/**
	 * 设置original字段
	 * 商品图片原始地址
	 * @return TRUE
	 * @access private 
	 */
	private function setOriginalPic($pictureObj) {
		if (empty($pictureObj)) {
			$this->share['original'] = "";
			return TRUE;
		}
		$picFile = $pictureObj->getPicFile();
		$pictureConvert = new PictureConvert($picFile);
		$original = $pictureConvert->getPictureO();
		$this->share['original'] = $original;
		return TRUE;
	}

	/**
	 * 设置show_pic字段
	 * 商品图片地址
	 * @return TRUE
	 * @access private 
	 */
	private function setShowPic($pictureObj, $goodsObj) {
		$this->share['show_pic'] = "";
		$this->share['e_show_pic'] = "";
		$this->share['taobao_original'] = 0;
		if (empty($pictureObj) && empty($goodsObj)) {
			return TRUE;
		}

		$sourcePic = "";
		$picFile = "";
		$nwidth = "";
		if (!empty($pictureObj)) {
			$picFile = $pictureObj->getPicFile();
			$nwidth = $pictureObj->getWidth();
		}
		if (!empty($goodsObj)) {
			$sourcePic = $goodsObj->getGoodsPicUrl();
		}

		$showPic = "";
		$eShowPic = "";
		if ($this->twitter['twitter_images_id'] > 0) {
			$pictureConvert = new PictureConvert($picFile);
			if ($nwidth > 310) {
				if (empty($goodsObj)) {
					$showPic = $pictureConvert->getPictureM();
				}
				else {
					if ($this->share['is_verify']) {
						$showPic = $pictureConvert->getPictureM();
					}
					else {
						$showPic = $pictureConvert->getPictureL();
					}
				}
				$eShowPic = $pictureConvert->getPictureE();
			}
			else {
				$eShowPic = $showPic = $pictureConvert->getPictureO();
			}
		}
		else {
			$eShowPic = $showPic = $sourcePic;
			$this->share['taobao_original'] = 1;
		}
		$this->share['show_pic'] = $showPic;
		$this->share['e_show_pic'] = $eShowPic;
		return TRUE;
	}

	/**
	 * 设置source字段
	 * 来源描述
	 * @return TRUE
	 * @access private 
	 */
	private function setSource() {
		$twitterSource = new TwitterSource($this->twitter['twitter_source_code']);
		$this->share['source'] = $twitterSource->getSource();
		return TRUE;
	}

	/**
	 * cpc计费
	 *
	 * @return TRUE
	 * @access private 
	 */
	private function cpcFee($tid, $userId) {
		if ($this->iscpc) {
			//cpc计费
			$cpcQueue = new CpcQueue($userId, cpcQueue::CPCSHAREPVS);
			$cpcQueue->queueIn($tid);
		}
		return TRUE;
	}

	/**
	 * 设置url字段
	 * 跳转到淘宝的url
	 * @return TRUE
	 * @access private 
	 */
	private function setUrl($goodsObj, $userId, $pictureObj) {
		$this->share['url'] = "";
		if (empty($goodsObj) && !empty($pictureObj)) {
			if (empty($pictureObj)) {
				return TRUE;
			}
			$picFile = $pictureObj->getPicFile();
			$pictureConvert = new PictureConvert($picFile);
			$url = $pictureConvert->getPictureM();
			$this->share['url'] = $pictureConvert->getPictureO();
			return TRUE;
		}
		if (empty($goodsObj)) {
			return array();
		}
		$urlId = $goodsObj->getGoodsUrl();
		//是否是cpc
		if ($this->iscpc) {
			//计算短连接
			$shortUrl = new ShortUrl($this->twitter['twitter_id']); 			
			$this->share['url'] = MEILISHUO_URL . "/s/" . $shortUrl->dec2base() . "/" . $this->twitter['twitter_id'];
		}
		else {
			$shortUrl = new ShortUrl($urlId); 			
			$this->share['url'] = MEILISHUO_URL . "/u/" . $shortUrl->dec2base() . "/" . $this->twitter['twitter_id'];
		}
		return TRUE;
	}

	/**
	 * 设置is_verify字段
	 * 是否是搭配秀
	 * @return TRUE
	 * @access private 
	 */
	private function setVerify() {
		$twitterVerify = $this->getTwitterVerify($this->twitter['twitter_show_type']);
		$this->share['is_verify'] = 0;
		if (!empty($twitterVerify)) {
			$this->share['is_verify'] = 1;
		}	
		return TRUE;
	}

	/**
	 * 获取用户的信息
	 * @return array
	 * @access private 
	 */
	private function getUsers() {
		$uids[] = $this->twitter['twitter_author_uid'];
		$uids[] = $this->twitter['twitter_source_uid'];
		$users = array();
		if (!empty($uids)) {
			$userAssembler = new User();
			$users = $userAssembler->getUserInfos($uids, array("nickname","user_id","avatar_c"));
		}
		return $users;
	}

	/**
	 * 获取图片信息
	 * @return array
	 * @access private 
	 */
	private function getPicture() {
		$pid = $this->twitter['twitter_images_id'];
		if (empty($pid)) {
			return array();
		}
		$pictureAssembler = new Picture(array('picid','n_pic_file','nwidth','nheight'));
		$pictures = $pictureAssembler->getPictureByPids(array($pid), TRUE);
		$pictureObj = NULL;
		if (isset($pictures[0])) {
			$pictureObj = $pictures[0];
		}
		return $pictureObj;
	}
	/**
	 * 将宝贝放到redis集合中，方便后续脚本扫描宝贝是否下架
	 * @return bool TRUE/FALSE
	 * @access private 
	 */
	private function inSet($goodsObj) {
		if (empty($goodsObj)) {
			return FALSE;
		}
		$goodsAssembler = new Goods();
		return $goodsAssembler->goodsShelfInSet($goodsObj->getId());
	}

	/**
	 * 获取宝贝信息
	 * @return array
	 * @access private 
	 */
	private function getGoods() {
		$gid = $this->twitter['twitter_goods_id'];
		$goodsObj =	NULL;
		if (!empty($gid)) {
			$goodsAssembler = new Goods(array('goods_id','goods_price','goods_title','goods_pic_url','goods_url'));
			$goods = $goodsAssembler->getGoodsByGids(array($gid), TRUE);
			if (isset($goods[0])) {
				$goodsObj = $goods[0];
			}
		}
		return $goodsObj;
	}

	private function setLikers() {
		$twitterAssembler = new Twitter();	
		$likers = $twitterAssembler->getLikersInRedis($this->twitter['twitter_id']);
		$users = array();
		if (!empty($likers)) {
			$userAssembler = new User();
			$users = $userAssembler->getUserInfos($likers, array("user_id","avatar_c"));
			$users = $userAssembler->filterUserAvatar($users);
			//$users = array_values($users);
			$users = array_slice(array_merge($users[0], $users[1]), 0, 14);
		}
		$this->share['likers'] = $users;
		return TRUE;
	}

	/**
	 * 获取推信息
	 * @return array
	 * @access private 
	 */
	private function getTwitter($tid) {
		$twitter = array();
		if (!empty($tid)) {
			$twitterAssembler = new Twitter(array("twitter_id","twitter_goods_id","twitter_author_uid","twitter_htmlcontent","twitter_create_time","twitter_show_type","twitter_images_id","twitter_source_tid","twitter_source_uid","twitter_source_code"));
			$twitter = $twitterAssembler->getTwitterByTids(array($tid));
		}
		return $twitter;
	}

	/**
	 * 获取杂志信息
	 * @return array
	 * @access private 
	 */
	private function getGroup($groupIds) {
		if (empty($groupIds) ) {
			return array();
		}
		$col = array("group_id","name");
		$groupAssembler = new Groups();
		$group_names = $groupAssembler->getGroupInfo($groupIds, $col);
		return $group_names;
	}

	/**
	 * 获取杂志和推的关系
	 * @return array
	 * @access private 
	 */
	private function getGroupTwitter() {
		$tids = array($this->twitter['twitter_id'], $this->twitter['twitter_source_tid']);
		if (empty($tids)) {
			return array();
		}
		$col = array("group_id","twitter_id");
		$groupAssembler = new GroupTwitters();
		$groupTwitter = $groupAssembler->getGroupTwitter($tids, $col);
		return $groupTwitter;
	}

	/**
	 * 获取收藏信息
	 * @return array
	 * @access private 
	 */
	private function getCollect($userId) {
		//喜欢等数字
		$client = MultiClient::getClient($userId);
        $collectInfo = array();
        $myCollectInfo = array();
		$stat = array(
				'multi_func' => 'twitters_stat',
				'method' => 'POST',
				'twitter_id' => $this->twitter['twitter_id'],
				'self_id' => $userId,
				);
		$collect = array(
				'multi_func' => 'twitter_likes_state',
				'method' => 'GET',
				'twitter_id' => $this->twitter['twitter_id'],
				'user_id' => $userId,
				'self_id' => $userId,
				);

		if (!empty($userId)) {
			list($tempInfo, $myCollectInfo) = $client->router(
					array($stat, $collect)
					);
		}
		else {
			list($tempInfo, $myCollectInfo) = $client->router(
					array($stat)
					);
			$myCollectInfo = array();
		}
		if (!empty($tempInfo[$this->twitter['twitter_id']])) {
			$collectInfo[$this->twitter['twitter_id']] = $tempInfo[$this->twitter['twitter_id']];
		}
		else {
			$collectInfo[$this->twitter['twitter_id']] = array(
				'twitter_id' => $this->twitter['twitter_id'],
				'twitter_goods_id' => 0,
				'count_discuss' => 0,
				'count_forward' => 0,
				'beauty' => array(
					'num' => 0,
				)
			);
		}

		return array($collectInfo, $myCollectInfo);
	}

	private function isTaobao($urlObj) {
		$url = "";
		if (!empty($urlObj)) {
			$url = $urlObj->getSourceLink();	
		}
		if (strpos($url, "taobao") !== FALSE) {
			return 1;	
		}
		return 0;
	}

	
	private function getTwitterVerify($showType) {
		$match = array(3,8,9,4);
		if (in_array($showType, $match)) {
			return array();
		}
		$identityObject = new IdentityObject();
		$identityObject->field('twitter_id')->in(array($this->twitter['twitter_id']))->field('verify_stat')->eq(1);
		$identityObject->col(array("twitter_id"));
		$twitterVerifyAssembler = new TwitterVerify();
		$twitterVerify = $twitterVerifyAssembler->getTwitterVerify($identityObject);
		return $twitterVerify;	
	}

	private function getUrl($goodsObj) {
		$urlObj = NULL;
		if (empty($goodsObj)) {
			return $urlObj;
		}
		$urlId = $goodsObj->getGoodsUrl();
		if (empty($urlId)) {
			return $urlObj;
		}
		$col = array('source_link','url_id');
		$urlAssembler = new Url($col);
		$urls = $urlAssembler->getUrlsByUrlIds(array($urlId), TRUE);
		if (isset($urls[0])) {
			$urlObj = $urls[0];
		}
		return $urlObj;	
	}

	private function errorMessage($code, $message) {
		self::setError(400, $code, $message);
		return TRUE;
	}
}
