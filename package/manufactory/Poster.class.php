<?php
namespace Snake\Package\Manufactory;

Use \Snake\Package\Twitter\Twitter;
Use \Snake\Package\Goods\Goods;
Use \Snake\Package\Goods\GoodsVerify;
Use \Snake\Libs\Base\MultiClient;
Use \Snake\Package\Picture\Picture;
Use \Snake\Package\Group\Groups;
Use \Snake\Package\Group\GroupTwitters;
Use \Snake\Package\User\User;
Use \Snake\Package\Picture\PictureConvert;
Use \Snake\Package\Url\Url;
Use \Snake\Package\Cpc\Cpc;
Use Snake\Package\Url\ShortUrl;
Use Snake\Package\Act\Act;
Use Snake\Package\Base\IdentityObject;
Use Snake\Package\Search\SearchObject;

Use Snake\Package\Search\CataExpr;
Use Snake\Package\Search\BracketExpr;
Use Snake\Package\Search\BusExpr;
Use Snake\Package\Search\UserExpr;
Use Snake\Package\Search\MainExpr;
Use Snake\Package\Search\CpcBusExpr;
Use Snake\Package\Search\CpcUserTestExpr;
Use Snake\Package\Search\CpcBusTestExpr;

class Poster {
	/**
	 * @return twitters，海报样式的数组
	 */
	//是否显示评论信息（两条）
	private $isShowComment = 0;
	//是否显示删除按钮
	private $isShowClose = 0;
	//是否显示喜欢按钮
	private $isShowLike = 1;
	//是否是主编
	private $isAdmin = 0;
	//是否是杂志社海报
	private $isGroup = 0;
	//是否显示价钱
	private $isShowPrice = 0;
	//是否显示时间
	private $isShowTime= 0;
	//是否是杂志社海报
	private $userId = 0;
	//要获取的推ids
	private $tids = array();
	//abtest图片宽度
	private $width = 0;
	//cpc abtest
	private $cpcTest = 0;
	//repin是否显示
	private $isShowRepin = 1;
	//设置要获取的图片 
	private $showPic = "R";
	//查看时间等因素的白名单用户
	private $white = array(765,49,1116240,3855122,1431119,1033092,157,1072759,79,1110228,1166050,11058400,2933617,1714967,25833988,17335912);
	//是否并发0:不并发1:并发
	private $parallel = 1;
	//并发0:不并发，1:并发，part2
	private $parallelPart2 = 1;

	public function abtestPic($picwidth) {
		$this->width = $picwidth;	
	}

	public function isShowRepin($repin) {
		$this->isShowRepin = $repin;
	}

	public function parallel($parallel) {
		$this->parallel = $parallel;	
	}

	public function parallelPart2($parallel) {
		$this->parallelPart2 = $parallel;	
	}

	public function cpcTest($cpcTest) {
		$this->cpcTest = $cpcTest;	
	}

	public function isShowComment($isShowComment) {
		$this->isShowComment = $isShowComment;	
	}

	//设置要获取的图片
	public function setShowPic($pic) {
		$this->showPic = strtoupper($pic);			
		if ($this->showPic == 'T') {
			$this->width = 226;
		}
	}

	public function isShowClose($isShowClose) {
		$this->isShowClose = $isShowClose;	
	}

	public function isShowPrice($isShowPrice) {
		$this->isShowPrice = $isShowPrice;	
	}
	
	public function isShowTime($isShowTime) {
		$this->isShowTime = $isShowTime;	
	}

	public function isAdmin($isAdmin) {
		$this->isAdmin = $isAdmin;
	}

	public function isGroup($isGroup) {
		$this->isGroup = $isGroup;
	}

	public function isShowLike($showLike) {
		$this->isShowLike = $showLike; 
	}

	public function setVariables($tids, $userId) {
		/*if (empty($tids)) {
			throw new \Exception("empty tids");
		}*/
		$this->tids = $tids;
		$this->userId = $userId;
	}
	public function regetRankParameter($sorted) {	
		if (in_array($this->userId, $this->white) && $this->isShowTime) {
			$indexes = array('goods_id_dist',
							 'goods_id_oneday_verify',
							 'goods_id_business');

			$searchObj = new SearchObject();
			$searchObj->resetFilters();
			$searchObj->setFilter('twitter_id', $this->tids);
			$searchObj->setLimit(0, count($this->tids));
			$searchRes = array();
			foreach ($indexes as $index) {
				if ($index == "goods_id_business") {
					$exprBuilder = new CataExpr(new BracketExpr(new CpcBusExpr(new CpcUserTestExpr(new MainExpr(), ""), "+"), ""), "*");
				}
				else {
					$exprBuilder = new CataExpr(new BracketExpr(new BusExpr(new UserExpr(new MainExpr(), ""), "+"), ""), "*");
				}
				$searchExpr = $exprBuilder->getExpr();
				$searchObj->setSortMode(SPH_SORT_EXPR, $searchExpr);
				
				$searchObj->setIndex($index);
				$searchObj->search("");
				$goodsDist = $searchObj->getSearchResult();
				$searchRes[$index] = \Snake\Libs\Base\Utilities::changeDataKeys($goodsDist['matches'], 'id');
			}

			foreach ($sorted as $key => &$value) {
				$indexExist = ""; 
				foreach ($searchRes as $index => $re) {
					if (isset($re[$value['twitter_goods_id']])) {
						$indexExist = $index; 
					}
				}
				if (isset($searchRes[$indexExist][$value['twitter_goods_id']])) {
					$rank = $searchRes[$indexExist][$value['twitter_goods_id']]['attrs'];	
					$verifyStat = "未审核";
					if (2 == $rank['verify_stat']) {
						$verifyStat = "进榜";
					}
					elseif(1 == $rank['verify_stat']) {
						$verifyStat = "进库";
					}
					$catalogName = "";
					$catalogId = substr($rank['catalog_id'], 0, 1);
					switch($catalogId) {
						case 2: $catalogName = "衣服"; break;
						case 6: $catalogName = "鞋子"; break;
						case 5: $catalogName = "包包"; break;
						case 7: $catalogName = "配饰"; break;
						case 9: $catalogName = "家居"; break;
						case 8: $catalogName = "美容"; break;
					}
					$str = "实际小红心:" . ($rank['goods_weight']-10000) . "<br/>"	.
						   "排除作弊后的小红心:" . $rank['rank_like'] . "<br/>" . 	
						   "评论数:" . $rank['count_discuss'] . "<br/>"	.
						   "转发数:" . $rank['count_forward'] . "<br/>" . 
						   "价格:" . $rank['goods_price'] . "<br/>" . 
						   "类目:" . $catalogName . "<br/>" . 
						   "审核状态:" . $verifyStat . "<br/>" . 
						   "淘客:" . $rank['commission'] . "<br/>" . 
						   "销售量:" . $rank['sale_volume'] . "<br/>" . 
						   "商家等级:" . $rank['level'] . "<br/>" . 
						   "创建时间:" . date("y-m-d H:i:s", $rank['twitter_create_time']) . "<br/>" . 
						   "历史总点击:" . $rank['all_click'] . "<br/>" . 
						   "最近一次有点击的时间:" . $rank['last_clicktime'] . "<br/>" .
						   "最近一次点击数:" . $rank['oneday_click'] . "<br/>" .
						   "最近一次点击增量:" . $rank['oneday_incr'] . "<br/>" .
						   "最近3天内点击数:" . $rank['threeday_click'] . "<br/>" .
						   "最近7天内点击数:" . $rank['sevenday_click'] . "<br/>";
					$cpc = "是";
					if (empty($rank['initial_commercial_value'])) {
						   $cpc = "否";
					}
					$str .= "是否cpc:" . $cpc . "<br/>";
					$str .= "分数:" . $rank['@expr']. "<br/>";
					$value['twitter_htmlcontent'] = $str . $value['twitter_htmlcontent'];
				}
			}
		}
		return $sorted;
	}
    public function getPoster() {
		//$this->tids = array(74095875);
		if ($this->parallel) {
			$this->parallelCurl = new ClassicCurl();	
		}

		$twitters = $this->getTwitters();
		if (empty($twitters)) {
			return array();
		}
		//获取comments parallel
		$parallelCommentsKey = "comments";
		$comments = $this->getComments($parallelCommentsKey);

		//获取图片信息parallel
		$parallelPicturesKey = "pictures";
		$pids = \Snake\Libs\Base\Utilities::DataToArray($twitters, "twitter_images_id");
		$pictures = $this->getPictures($pids, $parallelPicturesKey);

		//获取用户收藏信息	parallel
		$parallelCollectKey = "collect";
		$mergeTids = array_merge(\Snake\Libs\Base\Utilities::DataToArray($twitters, "twitter_source_tid"), $this->tids);
		list($collectInfo, $myCollectInfo) = $this->getCollect($mergeTids, $parallelCollectKey);	

		//增加杂志信息parallel
		$parallelGroupTwitterKey = "grouptwitter";
		$groupTwitter = $this->getGroupTwitter($mergeTids, $parallelGroupTwitterKey);


		//获取宝贝信息parallel
		$gids = \Snake\Libs\Base\Utilities::DataToArray($twitters, "twitter_goods_id");
		$parallelGoodsKey = "goods";
		$goods = $this->getGoods($gids, $parallelGoodsKey);
		
		//parallel 统一处理
		if ($this->parallel) {
			$responses = $this->parallelCurl->parallelCurl();
			$comments = $responses[$parallelCommentsKey];
			$goods = $responses[$parallelGoodsKey];
			$pictures = $responses[$parallelPicturesKey];
			$groupTwitter = $responses[$parallelGroupTwitterKey];
			list($collectInfo, $myCollectInfo) = $responses[$parallelCollectKey];
		}


		//parallel 二期 part1
		//获取杂志名称
		$groupIds = \Snake\Libs\Base\Utilities::DataToArray($groupTwitter, "group_id");
		$parallelGroupNamesKey = "group_names";

//		$log = new \Snake\Libs\Base\SnakeLog("poster_log_for_groupId", 'normal');
//		$mergeTidsPrintR = print_r($mergeTids, TRUE);
//		$groupTwitterPrintR = print_r($groupTwitter, TRUE);
//		$groupIdsPrintR = print_r($groupIds, TRUE);
//		if (is_array($groupIds)) {
//			foreach ($groupIds as $key => $idOfGroup) {
//				if (empty($idOfGroup)) {
//					unset($groupIds[$key]);
//					$echo = "mergeTidsPrintR:\n{$mergeTidsPrintR}\ngroupTwitterPrintR:\n{$groupTwitterPrintR}\ngroupIdsPrintR:\n{$groupIdsPrintR}\n";
//					$log->w_log($echo);
//				}
//			}
//		}
//		else {
//			if (!empty($groupIds)) {
//				$echo = "mergeTidsPrintR:\n{$mergeTidsPrintR}\ngroupTwitterPrintR:\n{$groupTwitterPrintR}\ngroupIdsPrintR:\n{$groupIdsPrintR}\n";
//				$log->w_log($echo);
//				$groupIds = array();	
//			}
//		}

		$groupNames = $this->getGroup($groupIds, $parallelGroupNamesKey);

		//parallel 二期 part2
		$urlIds = \Snake\Libs\Base\Utilities::DataToArray($goods, "goods_url");
		$parallelUrlKey = "urls";
		$urlInfo = $this->getUrl($urlIds, $parallelUrlKey); 



		//获取评论的用户id
		$uids = array();
		if (!empty($comments)) {
			foreach ($comments as $tid => $comment) {
				$uids = array_merge($uids, \Snake\Libs\Base\Utilities::DataToArray($comment, "twitter_author_uid"));
			}
		}
		//获取用户信息
		$uids = array_unique(array_merge($uids, \Snake\Libs\Base\Utilities::DataToArray($twitters, "twitter_author_uid")));

		//parallel 二期 part3
		$parallelUsersKey = "users";
		$users = $this->getUsers($uids, $parallelUsersKey);


		//获得活动id
		foreach($this->tids as $tid) {
			if (!isset($groupTwitter[$tid]) && $twitters[$tid]['twitter_show_type'] != 9 && $twitters[$tid]['twitter_show_type'] != 1) {
				$atids[] = $tid;	
			}	
		}

		//parallel 二期 part4
		$parallelActKey = "act";
		$actInfo = $this->getActInfo($atids, $parallelActKey);
		

		
		//parallel 二期 part5
		$parallelCpcKey = "cpc";
		$cpcInfo = $this->getCpc($parallelCpcKey);

		//parallel 二期 part6
		$parallelGoodsVerifyKey = "goods_verify";
		$goodsVerify = $this->getGoodsVerify($parallelGoodsVerifyKey);


		//parallel 二期 together
		if ($this->parallelPart2) {
			$responsesPart2 = $this->parallelCurl->parallelCurl();
			$groupNames = $responsesPart2[$parallelGroupNamesKey];
			$urlInfo = $responsesPart2[$parallelUrlKey];
			$users = $responsesPart2[$parallelUsersKey];
			$actInfo = $responsesPart2[$parallelActKey];
			$cpcInfo = $responsesPart2[$parallelCpcKey];
			$goodsVerify = $responsesPart2[$parallelGoodsVerifyKey];
		}


		if (!empty($comments)) {
			foreach ($comments as $tid => &$comment) {
				foreach ($comment as &$co) {
					if (isset($users[$co['twitter_author_uid']])) {
						$co['author'] = $users[$co['twitter_author_uid']];	
					}
					else {
						unset($comments[$tid]);	
					}
				}
			}
		}
		//
        // assemble
		//
        foreach ($twitters AS $k => &$twitter) {
            $userId = $twitter['twitter_author_uid'];
            $goodsId = $twitter['twitter_goods_id'];
			$urlId = $goods[$goodsId]['goods_url'];
			$twitterId = $twitter['twitter_id'];
            $picId = $twitter['twitter_images_id'];
            $sourceTid = $twitter['twitter_source_tid'];
			$sourceTidAuthor = $twitters[$sourceTid]['twitter_author_uid'];
			$sourceShowType = $twitters[$sourceTid]['twitter_show_type'];
			$showType = $twitter['twitter_show_type'];

			//是否显示时间
			$cpc = "";
			if (isset($cpcInfo[$twitterId])) {
				$cpc = "c";
			}

			$verifyStat = 3;
			if (isset($goodsVerify[$twitterId])) {
				$verifyStat = $goodsVerify[$twitterId]['verify_stat'];		
			}

			$twitters[$k]['twitter_create_time'] = date("y-m-d H:i:s", $twitter['twitter_create_time']) . "-" . $cpc . "-" . $verifyStat;
			$twitters[$k]['isShowTime'] = 0;
            if (in_array($this->userId, $this->white) && $this->isShowTime) {
                $twitters[$k]['isShowTime'] = 1;
            }   

			$twitters[$k]['like_twitter_id'] = $k;
			$twitters[$k]['like_author_uid'] = $twitter['twitter_author_uid'];
			
			$twitters[$k]['from_act_id'] = '';	
			$twitters[$k]['from_act_name'] = '';	
			if (isset($actInfo[$twitterId])) {
				$twitters[$k]['from_act_id'] = $actInfo[$twitterId]['activity_id'];	
				$twitters[$k]['from_act_name'] = $actInfo[$twitterId]['activity_title'];	
			}

			//cpc直接跳转
			$twitters[$k]['url'] = "";
			/*if (isset($cpcInfo[$twitterId])) {
				$shortUrl = new ShortUrl($twitterId); 			
				$twitters[$k]['url'] = "s/" . $shortUrl->dec2base() . "?market=1";
			}
			elseif ($this->cpcTest && isset($goods[$goodsId]) && isset($urlInfo[$goods[$goodsId]['goods_url']])) {
				$shortUrl = new ShortUrl($goods[$goodsId]['goods_url']); 			
				$twitters[$k]['url'] = "u/" . $shortUrl->dec2base() . "?market=1";
			}*/

			$toGroupId = $groupTwitter[$twitterId]['group_id'];

			//获取分享痕迹
			$repinTrace = new Repin();	
			$repinTrace->showType = $showType;
			$repinTrace->groupId = $groupTwitter[$twitterId];
			$repinTrace->sourceTwitterGroupId = $groupTwitter[$twitterId];
			$repinTrace->sourceTwitterAuthor = $sourceTidAuthor;
			$repinTrace->sourceTwitterAuthorNick = $users[$sourceTidAuthor]['nickname'];
			$repinTrace->sourceTwitterShowType = $sourceShowType;
			$repinTrace->sourceGroupId = $sourceShowType;
			$repinTrace->toGroupId = $groupTwitter[$twitterId]['group_id'];
			$repinTrace->toGroupName = $groupNames[$toGroupId]['name']; 
			$repin = $repinTrace->getRepin();
			$twitter['repin'] = $repin;


			if (2 == $showType || $showType == 8)  {
				$links = $this->hackForLancome($toGroupId);
				if (!empty($links)) {
					if (empty($twitter['twitter_goods_id'])) { 
						$twitter['url'] = $links;
					}
				 }
			}

		
			//喜欢的推设置
			$twitters[$k]['is_liked'] = 0;
			if (isset($myCollectInfo[$k]) && $myCollectInfo[$k]) {
				$twitters[$k]['is_liked'] = 1;
			}

            if ($sourceTid && isset($twitters[$sourceTid])) {
				if (9 == $twitters[$k]['twitter_show_type'] || 4 == $twitters[$k]['twitter_show_type']) {
					if (isset($myCollectInfo[$sourceTid]) && $myCollectInfo[$sourceTid]) {
						$twitters[$k]['is_liked'] = 1;
					}
					else {
						$twitters[$k]['is_liked'] = $twitters[$sourceTid]['is_liked'];
					}
					$twitters[$k]['like_twitter_id'] = $sourceTid;
					$twitters[$k]['like_author_uid'] = $twitters[$sourceTid]['twitter_author_uid'];
				}
			}
			/*else {
				//如果需要将回复转到原始twitter上需要开启此部
				/*$twitters[$k]['like_twitter_id'] = $k;
				$twitters[$k]['like_author_uid'] = $twitter['twitter_author_uid'];
				$twitters[$k]['is_liked'] = $twitters[$k]['is_liked'];
				$twitters[$k]['group_id'] = 0;*/
			//}

			//组装用户信息
            if (isset($users[$userId])) {
				$twitters[$k]['uinfo'] = $users[$userId];
            }
			else {
				unset($twitters[$k]);
				continue;
			}
			//组装评论
			if (isset($comments[$k])) {
				$twitters[$k]['comments'] = $comments[$k];
			}
			else {
				$twitters[$k]['comments'] = array();
			}
			//组装宝贝信息
			if (isset($goods[$goodsId])) {
				$twitters[$k]['ginfo'] = $goods[$goodsId];
            }

			//组装图片信息
			$defaultWidth = 200;
			if (!empty($this->width)) {
				$defaultWidth = $this->width;
			}
			if (empty($goods[$goodsId]['goods_pic_url']) && $showType == 7) {
				unset($twitters[$k]);
				continue;
			}
			elseif (empty($picId) && $showType == 2) {
				unset($twitters[$k]);	
				continue;
			}
			//老的转发，没有图片和goods_id
			elseif (empty($picId) && empty($goodsId) && $showType = 3) {
				unset($twitters[$k]);	
				continue;
			}
            elseif ($picId && isset($pictures[$picId])) {
				list($twitters[$k]['poster_height'], $twitters[$k]['poster_width']) = \Snake\Libs\Base\Utilities::getPosterImageHeight($pictures[$picId]['nwidth'], $pictures[$picId]['nheight'], $defaultWidth);
				//如果因为异步暂时获取不到图片高度，使用默认值
				if (empty($twitters[$k]['poster_height']) || $twitters[$k]['poster_height'] < 1) {
					$twitters[$k]['poster_height'] = 240;
				}
				//获取推的图片
				if (!empty($pictures[$picId]['n_pic_file'])) {
					$pictureConvert = new PictureConvert($pictures[$picId]['n_pic_file']);
					$method = "getPicture" . $this->showPic;
					$twitters[$k]['show_pic'] = $pictureConvert->$method();
				}
			}
            else {
				//图片没有抓取回来则使用原始图片
				if (7 == $showType && !empty($goods[$goodsId]['goods_pic_url'])) {
					$twitters[$k]['show_pic'] = $goods[$goodsId]['goods_pic_url'];	
				}
				//处理原推图片没有抓取回来，转发推的图片也没有的时候，则根据goods_id来查找图片
				elseif (!empty($goodsId)) {
					$twitter[$k]['show_pic'] = $goods[$goodsId]['goods_pic_url'];
				}
				//防止大图将海报撑开
				$twitters[$k]['poster_height'] = 240;
				$twitters[$k]['poster_width'] = $defaultWidth;
			}

			//组装喜欢转发讨论数字	
			$twitters[$k]['count_forward'] = 0;
			$twitters[$k]['count_reply'] = 0;
			$twitters[$k]['count_like'] = 0;
			//非小红心推展示本推的内容
			if (9 != $showType) {
				if (isset($collectInfo[$k])) {
					$twitters[$k]['count_forward'] = (int)$collectInfo[$k]['count_forward'];
					$twitters[$k]['count_reply'] = (int)$collectInfo[$k]['count_discuss'];
					$twitters[$k]['count_like'] = (int)$collectInfo[$k]['count_like'];
				}
			}
			else {
				if (isset($collectInfo[$sourceTid])) {
					$twitters[$k]['count_forward'] = (int)$collectInfo[$sourceTid]['count_forward'];
					$twitters[$k]['count_reply'] = (int)$collectInfo[$sourceTid]['count_discuss'];
					$twitters[$k]['count_like'] = (int)$collectInfo[$sourceTid]['count_like'];
				}
			}
			
			//组装各种控制字段
			$twitters[$k]['isShowLike'] = $this->isShowLike;
			$twitters[$k]['isShowClose'] = $this->isShowClose;
			//$twitters[$k]['isAdmin'] = $this->isAdmin;
			//$twitters[$k]['isGroup'] = $this->isGroup;
			$twitters[$k]['isShowPrice'] = $this->isShowPrice;
			//$twitters[$k]['isShowKick'] = 0;
            if ($this->isAdmin && $this->isGroup) {
                //$twitters[$k]['isShowKick'] = 1;       
                $twitters[$k]['isShowClose'] = 1;       
                $twitters[$k]['isShowLike'] = 1;       
				if ($twitters[$k]['twitter_author_uid'] === $this->userId) {   
                    $twitters[$k]['isShowLike'] = 0;    
                }   
            }   
            elseif (!$this->isAdmin) {
                if ($twitters[$k]['twitter_author_uid'] === $this->userId)     {   
                    $twitters[$k]['isShowLike'] = 0;
                    $twitters[$k]['isShowClose'] = 1;
                    //$twitters[$k]['isShowKick'] = 0;     
                }   
			}   	
		}
		//还原初始的序列状态，防止在处理的过程中顺序被修改
		$sorted = array();
		foreach ($this->tids AS $tid) {
			if (empty($twitters[$tid])) {
				continue;
			}
			$sorted[] = $twitters[$tid];
		}
		$sorted = $this->regetRankParameter($sorted);
		//$sorted = $this->regetRankParameter($sorted);
		//$response = $sorted;
        return $sorted;
    }

	
	private function getCollect($mergedTids, $parallelKey) {
        if (empty($mergedTids)) {
			return array();	
		}

		if ($this->parallel) {
			$requestUrl = HttpHost::getInstance()->getHost() . "goods/Parallel_collect";
			$fields['uid'] = $this->userId; 
			$fields['tids'] = $mergedTids;
			$this->parallelCurl->addUrl($requestUrl, $parallelKey, $fields);
			return TRUE;
		}

		$userId = $this->userId;
		//$userId = 1714967;
		$client = MultiClient::getClient($userId);
        $collectInfo = array();
        $myCollectInfo = array();
		//$mergedTids = array_merge($this->tids, $this->stids);
		//$mergedTids = array(74078419,74083589);
		$stat = array(
				'multi_func' => 'twitters_stat',
				'method' => 'POST',
				'twitter_id' => implode(',', $mergedTids),
				'self_id' => $userId,
				);
		$collect = array(
				'multi_func' => 'twitter_likes_state',
				'method' => 'GET',
				'twitter_id' => implode(',', $mergedTids),
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
		foreach ($mergedTids AS $mergedTid) {
			if (!empty($tempInfo[$mergedTid])) {
				$collectInfo[$mergedTid] = $tempInfo[$mergedTid];
			}
			else {
				$collectInfo[$mergedTid] = array(
					'twitter_id' => $mergedTid,
					'twitter_goods_id' => 0,
					'count_discuss' => 0,
					'count_forward' => 0,
					'beauty' => array(
						'num' => 0,
					)
				);
			}
		}
		return array($collectInfo, $myCollectInfo);
	}

	private function getUsers($uids, $parallelKey) {
		if (empty($uids)) {
			return array();
		}

		if ($this->parallelPart2) {
			$requestUrl = HttpHost::getInstance()->getHost() . "goods/Parallel_users";
			$fields['uids'] = $uids;
			$fields['fields'] = array("nickname","user_id","avatar_c","is_taobao_seller");
			$this->parallelCurl->addUrl($requestUrl, $parallelKey, $fields);
			return TRUE;
		}


		//获取用户信息
		$col = array("nickname","user_id","avatar_c","is_taobao_seller");
		$userAssembler = new User();
		$users = $userAssembler->getUserInfos($uids, $col);
		return $users;	
	}

	private function getGoodsVerify($parallelKey) {
		return array();
		if (!in_array($this->userId, $this->white) || !$this->isShowTime) {
			return array();	
		}
		if (empty($this->tids)) {
			return array();
		}

		if ($this->parallelPart2) {
			$requestUrl = HttpHost::getInstance()->getHost() . "goods/Parallel_goods_verify";
			$fields['tids'] = $this->tids;
			$fields['fields'] = array("verify_stat","verify_twitter_id");
			$this->parallelCurl->addUrl($requestUrl, $parallelKey, $fields);
			return TRUE;
		}

		$identityObject = new IdentityObject();
		$identityObject->field('verify_twitter_id')->in($this->tids);
		$identityObject->col(array("verify_stat","verify_twitter_id"));
		$goodsVerifyAssembler =  new GoodsVerify();
		$goodsVerify = $goodsVerifyAssembler->getGoodsVerify($identityObject);
		$goodsVerify = \Snake\Libs\Base\Utilities::changeDataKeys($goodsVerify, 'verify_twitter_id');
		return $goodsVerify;
	}

	private function getCpc($parallelKey) {
		return array();
		if (!$this->cpcTest && !$this->isShowTime) {
			return array();
		}
		if (!in_array($this->userId, $this->white)) {
			return array();	
		}
		if (empty($this->tids)) {
			return array();
		}

		if ($this->parallelPart2) {
			$requestUrl = HttpHost::getInstance()->getHost() . "goods/Parallel_cpc";
			$fields['tids'] = $this->tids;
			$fields['fields'] = array('goods_url','twitter_id');
			$this->parallelCurl->addUrl($requestUrl, $parallelKey, $fields);
			return TRUE;
		}

		$col = array('goods_url','twitter_id');
		$identityObject = new \Snake\Package\Base\IdentityObject();
		$identityObject->field('twitter_id')->in($this->tids);
		$identityObject->col($col);
		$cpcAssembler = new Cpc();
		$cpcs = $cpcAssembler->getCpcInfo($identityObject);
		$cpcs = \Snake\Libs\Base\Utilities::changeDataKeys($cpcs, 'twitter_id');
		return $cpcs;	
	}
	
	private function getUrl($urlIds, $parallelKey) {
		if (!$this->cpcTest) {
			return array();
		}
		if (empty($urlIds)) {
			return array();
		}

		if ($this->parallelPart2) {
			$requestUrl = HttpHost::getInstance()->getHost() . "goods/Parallel_url";
			$fields['url_ids'] = $urlIds;
			$fields['fields'] = array('source_link','url_id');
			$this->parallelCurl->addUrl($requestUrl, $parallelKey, $fields);
			return TRUE;
		}

		$col = array('source_link','url_id');
		$urlAssembler = new Url($col);
		$urls = $urlAssembler->getUrlsByUrlIds($urlIds);
		$urls = \Snake\Libs\Base\Utilities::changeDataKeys($urls, 'url_id');
		return $urls;	
	}

	private function getComments($parallelKey) {
		if (empty($this->tids) && !$this->isShowComment) {
			return array();
		}
		if ($this->parallel) {
			$requestUrl = HttpHost::getInstance()->getHost() . "goods/Parallel_comments";
			$fields['tids'] = $this->tids;
			$fields['fields'] = array('twitter_id','twitter_author_uid','twitter_source_tid','twitter_htmlcontent');
			$this->parallelCurl->addUrl($requestUrl, $parallelKey, $fields);
			return TRUE;
		}
		$col = array('twitter_id','twitter_author_uid','twitter_source_tid','twitter_htmlcontent');
		$twitterAssembler = new Twitter($col);
		$twitters = $twitterAssembler->getTwitterRecentReply($this->tids);
		return $twitters;	
	}

	private function getTwitters() {


		$log = new \Snake\Libs\Base\SnakeLog('poster_twitter_log', 'normal');
		$tidNum = count($this->tids);

		if (empty($this->tids)) {
			$log->w_log('empty tids');
			return array();
		}

		

		$col = array('twitter_id','twitter_author_uid','twitter_show_type','twitter_images_id','twitter_source_tid','twitter_htmlcontent','twitter_goods_id','twitter_create_time');
		$twitterAssembler = new Twitter($col);
		$twitterInfo = $twitterAssembler->getTwitterByTids($this->tids);
		//$twitterAssembler->getTwitterRecentReply($this->tids);
		$twitters = array();
		$stids = array();
		foreach ($twitterInfo as $key => $value) {
			$twitters[$value['twitter_id']] = $value;
            $value['twitter_source_tid'] == 0 || $stids[] = $value['twitter_source_tid'];
		}
		$tids = array_diff($stids, $this->tids);	

		$twitterInfo = $twitterAssembler->getTwitterByTids($tids);
		foreach ($twitterInfo as $key => $value) {
			$twitters[$value['twitter_id']] = $value;
		}

		$num1 = count($twitterInfo);
		$num2 = count($twitters);
		$log->w_log("{$tidNum}\t{$num1}\t{$num2}");

		return $twitters;
	}
	private function getPictures($pids, $parallelKey) {
		if (empty($pids)) {
			return array();	
		}
		if ($this->parallel) {
			$fields['pids'] = $pids;
			$fields['fields'] = array('picid','n_pic_file','nwidth','nheight');
			$requestUrl = HttpHost::getInstance()->getHost() . "goods/Parallel_pictures";
			$this->parallelCurl->addUrl($requestUrl, $parallelKey, $fields);
			return TRUE;
		}
		//有图片的pictures
		$col = array('picid','n_pic_file','nwidth','nheight');
		$pictureAssembler = new Picture($col);
		$pictures = $pictureAssembler->getPictureByPids($pids);
		$pictures = \Snake\Libs\Base\Utilities::changeDataKeys($pictures, 'picid');
		return $pictures;
	}

	function getGroup($groupIds, $parallelKey) {
		if (empty($groupIds)) {
			return array();
		}
		if ($this->parallelPart2) {
			$requestUrl = HttpHost::getInstance()->getHost() . "goods/Parallel_group";
			$fields['group'] = $groupIds;
			$fields['fields'] = array("group_id","name");
			$this->parallelCurl->addUrl($requestUrl, $parallelKey, $fields);
			return TRUE;
		}
		$col = array("group_id","name");
		$groupAssembler = new Groups();
		$groupNames = $groupAssembler->getGroupInfo($groupIds, $col);
		return $groupNames;
		
	}
	function getActInfo($atids, $parallelKey) {
		if (empty($this->tids) || empty($atids)) {
			return array();
		}

		if ($this->parallelPart2) {
			$requestUrl = HttpHost::getInstance()->getHost() . "goods/Parallel_act";
			$fields['atids'] = $atids;
			$this->parallelCurl->addUrl($requestUrl, $parallelKeyi, $fields);
			return TRUE;
		}

		if (!empty($atids)) {
			$act = new Act();
			$column = 't1.activity_id,t1.activity_title,t2.twitter_id';
			$param['status'] = 1;
			$actInfo = $act->getActInfoByTids($atids, $column, $param, false, 'twitter_id');
		}
		return $actInfo;
	}
	function getGoods($gids, $parallelKey) {
		if (empty($gids)) {
			return array();
		}
		//并发
		if ($this->parallel) {
			$requestUrl = HttpHost::getInstance()->getHost() . "goods/Parallel_goods";
			$fields['gids'] = $gids;
			$fields['fields'] = array('goods_id','goods_price','goods_title','goods_pic_url','goods_url');
			$this->parallelCurl->addUrl($requestUrl, $parallelKey, $fields);
			return TRUE;
		}

		$col = array('goods_id','goods_price','goods_title','goods_pic_url','goods_url');
		$goodsAssembler = new Goods($col);
		$goodsInfo = $goodsAssembler->getGoodsByGids($gids);
		$goodsInfo = \Snake\Libs\Base\Utilities::changeDataKeys($goodsInfo, 'goods_id');
		return $goodsInfo;
	}
	/*
	 *
	 */
	function getGroupTwitter($mergeTids, $parallelKey) {
		if (empty($mergeTids) || !$this->isShowRepin) {
			return array();
		}

		if ($this->parallel) {
			$requestUrl = HttpHost::getInstance()->getHost() . "goods/Parallel_grouptwitter";
			$fields['tids'] = $mergeTids;
			$fields['fields'] = array("group_id","twitter_id");
			$this->parallelCurl->addUrl($requestUrl, $parallelKey, $fields);
			return TRUE;
		}

		$col = array("group_id","twitter_id");
		$groupAssembler = new GroupTwitters();
		$groupIds = $groupAssembler->getGroupTwitter($mergeTids, $col);
		return $groupIds;
	}

	public function hackForLancome($gid){
		if (!in_array($gid,array(14699544, 14699474, 14699546, 14699444, 12094988, 14699455, 14952722, 14699459))) {
			return false;
		}
		$linkurl = array(
			14699544 => 'http://www.rosebeauty.com.cn/bbs/lancome_bbs_341165.html',
			14699474 => 'http://www.rosebeauty.com.cn/bbs/lancome_bbs_341166.html',
			14699546 => 'http://www.rosebeauty.com.cn/bbs/lancome_bbs_341155.html',
			14699444 => 'http://www.rosebeauty.com.cn/bbs/lancome_bbs_341170.html',
			12094988 => 'http://www.rosebeauty.com.cn/bbs/lancome_bbs_341171.html',
			14699455 => 'http://www.rosebeauty.com.cn/bbs/lancome_bbs_341291.html',
			14952722 => 'http://www.rosebeauty.com.cn/bbs/lancome_bbs_341363.html',
			14699459 => 'http://www.rosebeauty.com.cn/bbs/lancome_bbs_342216.html'
		);
		foreach($linkurl as $key => $value) {
			if ($gid == $key) {
				$link = $value;
				break;
			}
		}
		return $link;
	}
}
