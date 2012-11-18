<?php
namespace Snake\Modules\User;

/**
 * @author yishuliu@meilishuo.com
 * 显示用户推荐的宝贝被喜欢的列表页面
 *
 **/

Use \Snake\Package\User\User;
Use \Snake\Libs\Cache\Memcache;
Use \Snake\Package\Twitter\Twitter;
Use \Snake\Package\Goods\Goods;
Use \Snake\Package\Recommend\RecUserOther;
Use \Snake\Package\Picture\Picture;
Use \Snake\Package\Twitterstat\TwitterStat;

class Heart_atme extends \Snake\Libs\Controller {
	private $userId = NULL;
	private $isSelfPage = 0;
	const pageSize = 20;

	public function run()  {
        if (!$this->_init()) {
            return FALSE;
        }
		//0表示用户未登录或访问别人页面 1代表访问自己页面
		if ($this->userId == $this->visitedUserId) {
			$this->isSelfPage = 1;
		}
		
        $offset = $this->page * self::pageSize;
		$RecommendHandle = new RecUserOther();
		$recommendList = $RecommendHandle->getRecommendList($this->visitedUserId, $offset, self::pageSize);
		//print_r($recommendList);die('@@');

		$twitterIds = \Snake\Libs\Base\Utilities::DataToArray($recommendList, 'twitter_id');
		//print_r($twitterIds);die;
		if (!empty($twitterIds)) {
			$twitterInfos = $this->fetchTwitterInfos($twitterIds);
			$twitterStats = $this->fetchTwitterStats($twitterIds);
		}
		//print_r($twitterStats);die;
		//print_r($twitterInfos);die;

		//当前用户信息
		$currentUserInfo = $this->fetchUserInfos(array($this->visitedUserId));
		//print_r($currentUserInfo);die;
	
        //获取图片信息
        $tImages = \Snake\Libs\Base\Utilities::DataToArray($twitterInfos, "twitter_images_id");
        $pictures = $this->fetchTwitterImgs($tImages);
		//print_r($pictures);die;

		//点小红心喜欢的用户ids
		$guestIds = \Snake\Libs\Base\Utilities::DataToArray($recommendList, 'user_id');
		$guestInfos = $this->fetchUserInfos($guestIds);
		//print_r($guestInfos);die('##');

		$goodsIds = \Snake\Libs\Base\Utilities::DataToArray($twitterInfos, 'twitter_goods_id');
		$goodsInfos = $this->fetchGoodsInfo($goodsIds);
		//print_r($goodsInfos);die('###');

        $tInfo = array();
		//此处不能改成一层foreach twitter_id有重复值
		foreach($recommendList as $key => $value) {
			foreach($twitterInfos as $tKey => $tvalue) {
				if ($value['twitter_id'] == $tvalue['twitter_id']) {
					$tInfo[$key]['twitter_id'] = $value['twitter_id'];
					$tInfo[$key]['twitter_content'] = preg_replace('/<a.*?>/i', '', $tvalue['twitter_htmlcontent']);
					$tInfo[$key]['twitter_create_time'] = date("m月d日 H:i", $tvalue['twitter_create_time']);
					$tInfo[$key]['twitter_images_id'] = $tvalue['twitter_images_id'];
					$tInfo[$key]['twitter_goods_id'] = $tvalue['twitter_goods_id'];
					$tInfo[$key]['twitter_source_code'] = ($tvalue['twitter_source_code'] === 'web') ? '来自网站' : '来自' . $tvalue['twitter_source_code'];

					//插入picture_url或者goods_url
					$tInfo[$key]['picture_url'] = (!empty($tvalue['twitter_images_id']) && isset($pictures[$tvalue['twitter_images_id']])) ? \Snake\Libs\Base\Utilities::getPictureUrl($pictures[$tvalue['twitter_images_id']]['n_pic_file'], $type = "_o") : '';
					$tInfo[$key]['goods_title'] = (!empty($tvalue['twitter_goods_id']) && isset($goodsInfos[$tvalue['twitter_goods_id']])) ? $goodsInfos[$tvalue['twitter_goods_id']]['goods_title'] : '';
					$tInfo[$key]['goods_url'] = (!empty($tvalue['twitter_goods_id']) && isset($goodsInfos[$tvalue['twitter_goods_id']])) ? $goodsInfos[$tvalue['twitter_goods_id']]['goods_pic_url'] : '';
				}
			}
			foreach($twitterStats as $sKey => $svalue) {
				if ($value['twitter_id'] == $svalue['twitter_id']) {
					$tInfo[$key]['discuss_num'] = $svalue['discuss_num'];
				}
			}
			foreach($guestInfos as $uKey => $uvalue) {
				if ($value['user_id'] == $uvalue['user_id']) {
					$tInfo[$key]['user_id'] = $uvalue['user_id'];
					$tInfo[$key]['nickname'] = $uvalue['nickname'];
					$tInfo[$key]['avatar_b'] = $uvalue['avatar_b'];
					$tInfo[$key]['is_taobao_buyer'] = $uvalue['is_taobao_buyer'];
					$tInfo[$key]['wording'] = '喜欢你的分享，送给你一颗';
					$tInfo[$key]['author'] = $currentUserInfo[$this->visitedUserId]['nickname'];
				}
			}
		}
		
		//删除没有twitterinfo的数组
		$tInfo = $this->removeErrorInfos($tInfo);
		//print_r(array('tInfo' => $tInfo, 'self' => $this->isSelfPage));die('#*#');

		if (!empty($tInfo)) {
			$this->view = array('tInfo' => $tInfo, 'self' => $this->isSelfPage);
		}
		else {
			$this->view = array();
		}
		return TRUE;
	}

	public function fetchTwitterInfos($twitterIds) {
		$tObj = new Twitter(array('twitter_id', 'twitter_images_id', 'twitter_content', 'twitter_htmlcontent', 'twitter_create_time', 'twitter_show_type', 'twitter_goods_id', 'twitter_source_code'));
		$twitterInfos = $tObj->getTwitterByTids($twitterIds);
		return $twitterInfos;
	}

	public function fetchUserInfos($userIds) {
		$userHandle = new User();
		$userInfos = $userHandle->getUserInfos($userIds, array('user_id', 'nickname', 'avatar_c', 'avatar_b', 'verify_icons', 'is_taobao_buyer', 'verify_msg'));
		return $userInfos;
	}

	public function fetchTwitterImgs($tImages) {
        //有图片的pictures
        $col = array('picid','n_pic_file','nwidth','nheight');
        $pictureAssembler = new Picture($col);
        $pictures = $pictureAssembler->getPictureByPids($tImages);
		$pictures = \Snake\Libs\Base\Utilities::changeDataKeys($pictures, 'picid');
        return $pictures;
	}

	public function fetchGoodsInfo($goodsIds) {
		$goodsHelper = new Goods(array('goods_id', 'goods_title', 'goods_url', 'goods_pic_url', 'goods_pic_id'));
		$goodsInfos = $goodsHelper->getGoodsByGids($goodsIds);
		$goodsInfos = \Snake\Libs\Base\Utilities::changeDataKeys($goodsInfos, 'goods_id');
		return $goodsInfos;
	}

	public function fetchTwitterStats($tids) {
		$twitterStats = TwitterStat::objects()->filter($tids)->get();
		return $twitterStats;
	}

	public function removeErrorInfos($tInfos) {
		foreach ($tInfos as $key => $value) {
			if (empty($value['twitter_id'])) {
				unset($tInfos[$key]);
			}
		}
		return $tInfos;
	}

    /**
     * 初始化变量
     **/
    private function _init() {
        if (!$this->setVisitedUserId()) {
            return FALSE;
        }
        if (!$this->setPage()) {
            return FALSE;
        }
        if (!$this->setUserId()) {
            return FALSE;
        }
        return TRUE;
    }

    private function setUserId() {
        $this->userId = $this->userSession['user_id']; //7580696;
        return TRUE;
    }

    private function setVisitedUserId() {
        $visitorId = !empty($this->request->REQUEST['user_id']) ? $this->request->REQUEST['user_id'] : 0;
        if (empty($visitorId)) {
            $this->setError(400, 40109, 'user_id is empty');
            return FALSE;
        }
        if (!is_numeric($visitorId)) {
            $this->setError(400, 40110, 'User id is not a number');
            return FALSE;
        }
        if ($wordId < 0) {
            $this->setError(400, 40110, 'User id is nagetive');
            return FALSE;
        }
        $this->visitedUserId = $visitorId;
        return TRUE;
    }

    private function setPage() {
        $page = !empty($this->request->REQUEST['page']) ? $this->request->REQUEST['page'] : 0;
        if (!is_numeric($page)) {
            $this->setError(400, 40107, 'bad page');
            return FALSE;
        }
        if ($page < 0)  {
            $this->setError(400, 40108, 'page is nagetive');
            return FALSE;
        }
        $this->page = $page;
        return TRUE;
    }
}
