<?php
namespace Snake\Package\Manufactory;

Use \Snake\Package\Twitter\Twitter;
Use \Snake\Package\Goods\Goods;
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

class Poster360 {
	/**
	 * @return twitters，海报样式的数组
	 */
	//是否是杂志社海报
	private $userId = 0;
	//要获取的推ids
	private $tids = array();
	//abtest图片宽度
	private $width = 0;

	public function abtestPic($picwidth) {
		$this->width = $picwidth;	
	}


	public function setVariables($tids, $userId) {
		/*if (empty($tids)) {
			throw new \Exception("empty tids");
		}*/
		$this->tids = $tids;
		$this->userId = $userId;
	}

    public function getPoster() {
		$twitters = $this->getTwitters();
		if (empty($twitters)) {
			return array();
		}
		//获取评论的用户id
		//$uids = array();
		//获取用户信息
		//$uids = array_unique(array_merge($uids, \Snake\Libs\Base\Utilities::DataToArray($twitters, "twitter_author_uid")));

		//获取图片信息
		$pids = \Snake\Libs\Base\Utilities::DataToArray($twitters, "twitter_images_id");
		$pictures = $this->getPictures($pids);

		//获取用户收藏信息	
		$mergeTids = array_merge(\Snake\Libs\Base\Utilities::DataToArray($twitters, "twitter_source_tid"), $this->tids);

		//获取宝贝信息
		$gids = \Snake\Libs\Base\Utilities::DataToArray($twitters, "twitter_goods_id");
		$goods = $this->getGoods($gids);
		
		$urlIds = \Snake\Libs\Base\Utilities::DataToArray($goods, "goods_url");
		$urlInfo = $this->getUrl($urlIds); 

		//
        // assemble
		//
        foreach ($twitters AS $k => &$twitter) {
            $goodsId = $twitter['twitter_goods_id'];
			$urlId = $goods[$goodsId]['goods_url'];
			$twitterId = $twitter['twitter_id'];
            $picId = $twitter['twitter_images_id'];
            //$sourceTid = $twitter['twitter_source_tid'];
			$showType = $twitter['twitter_show_type'];

			//cpc直接跳转
			$twitters[$k]['url'] = "";
			if (isset($goods[$goodsId]) && isset($urlInfo[$goods[$goodsId]['goods_url']])) {
				$shortUrl = new ShortUrl($goods[$goodsId]['goods_url']); 			
				$twitters[$k]['url'] = "u/" . $shortUrl->dec2base();
				$twitters[$k]['source_link'] = $urlInfo[$goods[$goodsId]['goods_url']]['source_link'];
			}
		
			//喜欢的推设置
            /*if ($sourceTid && isset($twitters[$sourceTid])) {
                //$twitters[$k]['source'] = $twitters[$sourceTid];
				if (9 == $twitters[$k]['twitter_show_type'] || 4 == $twitters[$k]['twitter_show_type']) {
					$twitters[$k]['is_liked'] = $twitters[$sourceTid]['is_liked'];
					$twitters[$k]['like_twitter_id'] = $sourceTid;
					$twitters[$k]['like_author_uid'] = $twitters[$sourceTid]['twitter_author_uid'];
				}
			}*/
			/*else {
				//如果需要将回复转到原始twitter上需要开启此部
				/*$twitters[$k]['like_twitter_id'] = $k;
				$twitters[$k]['like_author_uid'] = $twitter['twitter_author_uid'];
				$twitters[$k]['is_liked'] = $twitters[$k]['is_liked'];
				$twitters[$k]['group_id'] = 0;*/
			//}

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
            elseif ($picId && isset($pictures[$picId])) {
				list($twitters[$k]['poster_height'], $twitters[$k]['poster_width']) = \Snake\Libs\Base\Utilities::getPosterImageHeight($pictures[$picId]['nwidth'], $pictures[$picId]['nheight'], $defaultWidth);
				//如果因为异步暂时获取不到图片高度，使用默认值
				if (empty($twitters[$k]['poster_height']) || $twitters[$k]['poster_height'] < 1) {
					$twitters[$k]['poster_height'] = 240;
				}
				//获取推的图片
				if (!empty($pictures[$picId]['n_pic_file'])) {
					$pictureConvert = new PictureConvert($pictures[$picId]['n_pic_file']);
					$twitters[$k]['show_pic'] = $pictureConvert->getPictureR();
					$twitters[$k]['big_show_pic'] = $pictureConvert->getPictureL();
				}
			}
            else {
				//图片没有抓取回来则使用原始图片
				if (7 == $showType && !empty($goods[$goodsId]['goods_pic_url'])) {
					$twitters[$k]['show_pic'] = $goods[$goodsId]['goods_pic_url'];	
					$twitters[$k]['big_show_pic'] = $goods[$goodsId]['goods_pic_url'];
				}
				//处理原推图片没有抓取回来，转发推的图片也没有的时候，则根据goods_id来查找图片
				elseif (!empty($goodsId)) {
					$twitter[$k]['show_pic'] = $goods[$goodsId]['goods_pic_url'];
					$twitter[$k]['big_show_pic'] = $goods[$goodsId]['goods_pic_url'];
				}
				//防止大图将海报撑开
				$twitters[$k]['poster_height'] = 240;
				$twitters[$k]['poster_width'] = $defaultWidth;
			}
              	
		}
		//还原初始的序列状态，防止在处理的过程中顺序被修改
		$sorted = array();
		foreach ($this->tids AS $tid) {
			if (empty($twitters[$tid])) {
				continue;
			}
			unset($twitters[$tid]['twitter_id']);
			unset($twitters[$tid]['twitter_goods_id']);
			unset($twitters[$tid]['twitter_images_id']);
			unset($twitters[$tid]['twitter_show_type']);
			unset($twitters[$tid]['twitter_htmlcontent']);
			$twitters[$tid]['is_taobao'] = 0;
			if (strpos($twitters[$tid]['source_link'], "taobao") !== FALSE) {
				$twitters[$tid]['is_taobao'] = 1;	
			}
			unset($twitters[$tid]['source_link']);
			$twitters[$tid]['ginfo']['goods_title'] = mb_substr($twitters[$tid]['ginfo']['goods_title'], 0, 55, "UTF-8");
			$sorted[] = $twitters[$tid];
		}
        return $sorted;
    }

	private function getUrl($urlIds) {
		if (empty($urlIds)) {
			return array();
		}
		$col = array('source_link','url_id');
		$urlAssembler = new Url($col);
		$urls = $urlAssembler->getUrlsByUrlIds($urlIds);
		$urls = \Snake\Libs\Base\Utilities::changeDataKeys($urls, 'url_id');
		return $urls;	
	}

	private function getTwitters() {
		if (empty($this->tids)) {
			return array();
		}
		$col = array('twitter_id','twitter_images_id','twitter_show_type','twitter_goods_id');
		$twitterAssembler = new Twitter($col);
		$twitterInfo = $twitterAssembler->getTwitterByTids($this->tids);
		//$twitterAssembler->getTwitterRecentReply($this->tids);
		$twitters = array();
		$stids = array();
		foreach ($twitterInfo as $key => $value) {
			$twitters[$value['twitter_id']] = $value;
		}
		/*$tids = array_diff($stids, $this->tids);	

		$twitterInfo = $twitterAssembler->getTwitterByTids($tids);
		foreach ($twitterInfo as $key => $value) {
			$twitters[$value['twitter_id']] = $value;
		}*/
		return $twitters;
	}
	
	private function getPictures($pids) {
		if (empty($pids)) {
			return array();	
		}
		//有图片的pictures
		$col = array('picid','n_pic_file','nwidth','nheight');
		$pictureAssembler = new Picture($col);
		$pictures = $pictureAssembler->getPictureByPids($pids);
		$pictures = \Snake\Libs\Base\Utilities::changeDataKeys($pictures, 'picid');
		return $pictures;
	}

	function getGoods($gids) {
		if (empty($gids)) {
			return array();
		}
		$col = array('goods_id','goods_price','goods_title','goods_pic_url','goods_url');
		$goodsAssembler = new Goods($col);
		$goodsInfo = $goodsAssembler->getGoodsByGids($gids);
		$goodsInfo = \Snake\Libs\Base\Utilities::changeDataKeys($goodsInfo, 'goods_id');
		return $goodsInfo;
	}
}
