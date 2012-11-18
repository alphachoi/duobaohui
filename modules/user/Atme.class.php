<?php
namespace Snake\Modules\User;

/**
 * @author yishuliu@meilishuo.com
 * 显示用户被@的列表页面
 *
 **/

Use \Snake\Package\User\User;
Use \Snake\Libs\Cache\Memcache;
Use \Snake\Package\Twitter\Twitter;
Use \Snake\Package\Goods\Goods;
Use \Snake\Package\Picture\Picture;
Use \Snake\Package\Twitterstat\TwitterStat;
Use \Snake\Package\Msg\Alert;

class Atme extends \Snake\Libs\Controller {
	private $userId = NULL;
	private $isSelfPage = 0;
	const pageSize = 20;

	public function run() {
        if (!$this->_init()) {
            return FALSE;
        }
		if (empty($this->userId)) {
			$result['redirect'] = 'welcome';
			$result['error_msg'] = 'not login';
			return $result;
		}
        $offset = $this->page * self::pageSize;
		$alertHandle = new Alert();
		$alertList = $alertHandle->getAtMeTids($this->userId);

		$twitterIds = \Snake\Libs\Base\Utilities::DataToArray($alertList, 'twitter_id');
		//twitter_show_type = 4为评论推 
		$twitterInfos = $this->fetchTwitterInfos($twitterIds);
		//print_r($twitterInfos);die;

		//原推数据
		$sourceTids = \Snake\Libs\Base\Utilities::DataToArray($twitterInfos, 'twitter_source_tid');

		if (!empty($sourceTids)) {
			$sourceTInfos = $this->fetchTwitterInfos($sourceTids);
			$twitterStats = $this->fetchTwitterStats($sourceTids);
		}
	
        //获取图片信息
        $tImages = \Snake\Libs\Base\Utilities::DataToArray($sourceTInfos, "twitter_images_id");
		//print_r($tImages);die;
        $pictures = $this->fetchTwitterImgs($tImages);
		//print_r($pictures);die;

		//当前用户信息
		$currentUserInfo = $this->fetchUserInfos(array($this->userId));

		//@人的用户ids
		$guestIds = \Snake\Libs\Base\Utilities::DataToArray($twitterInfos, 'twitter_author_uid');
		$guestInfos = $this->fetchUserInfos($guestIds);
		//print_r($guestInfos);die('##');

		$goodsIds = \Snake\Libs\Base\Utilities::DataToArray($sourceTInfos, 'twitter_goods_id');
		$goodsInfos = $this->fetchGoodsInfo($goodsIds);

		//将twitterStats,图片信息和宝贝信息merge进入sourceTInfos
		foreach($sourceTInfos as $skey => $svalue) {
			if (!empty($twitterStats)) {
				foreach($twitterStats as $tkey => $tvalue) {
					if ($svalue['twitter_id'] == $tvalue['twitter_id']) {
						$sourceTInfos[$skey]['discuss_num'] = $tvalue['discuss_num'];
					}
				}
			}
            if (!empty($svalue['twitter_images_id']) && !empty($pictures)) {
                foreach($pictures as $pKey => $pvalue) {
                    if ($pvalue['picid'] == $svalue['twitter_images_id']) {
                        $sourceTInfos[$skey]['picture_url'] = \Snake\Libs\Base\Utilities::getPictureUrl($pvalue['n_pic_file'], $type = "_o");
                    }   
                }   
            }   
            if (!empty($svalue['twitter_goods_id']) && !empty($goodsInfos)) {
                foreach($goodsInfos as $gKey => $gvalue) {
                    if ($gvalue['goods_id'] == $svalue['twitter_goods_id']) {
                        $sourceTInfos[$skey]['goods_title'] = $gvalue['goods_title'];
                        $sourceTInfos[$skey]['goods_url'] = $gvalue['goods_pic_url'];
                    }   
                }   
            }   
		}
		//print_r($sourceTInfos);die('@@@');

		//将sourceTinfos merge 进入twitterInfos
		foreach($twitterInfos as $tKey => $tvalue) {
			foreach($sourceTInfos as $sKey => $svalue) {
				if ($tvalue['twitter_source_tid'] == $svalue['twitter_id']) {
					$twitterInfos[$tKey]['source_twitter_htmlcontent'] = $svalue['twitter_htmlcontent'];
					$twitterInfos[$tKey]['discuss_num'] = $svalue['discuss_num'];
					$twitterInfos[$tKey]['picture_url'] = $svalue['picture_url'];
					$twitterInfos[$tKey]['goods_title'] = $svalue['goods_title'];
					$twitterInfos[$tKey]['goods_url'] = $svalue['goods_url'];
				}
			}

			foreach($guestInfos as $uKey => $uvalue) {
				if ($uvalue['user_id'] == $tvalue['twitter_author_uid']) {
					$twitterInfos[$tKey]['user_id'] = $uvalue['user_id'];
					$twitterInfos[$tKey]['nickname'] = $uvalue['nickname'];
					$twitterInfos[$tKey]['avatar_b'] = $uvalue['avatar_b'];
					$twitterInfos[$tKey]['is_taobao_buyer'] = $uvalue['is_taobao_buyer'];
					$twitterInfos[$tKey]['at_user'] = $currentUserInfo[$this->userId]['nickname'];
				}
			}

			$twitterInfos[$tKey]['twitter_create_time'] = date("m月d日 H:i", $tvalue['twitter_create_time']);
			$twitterInfos[$tKey]['twitter_source_code'] = ($tvalue['twitter_source_code'] === 'web') ? '来自网站' : '来自' . $tvalue['twitter_source_code'];
		}
		//print_r($twitterInfos);die('###');		

		if (!empty($twitterInfos)) {
			$this->view = $twitterInfos;
		}
		else {
			$this->view = array();
		}
		return TRUE;
	}

	public function fetchTwitterInfos($twitterIds) {
		$tObj = new Twitter(array('twitter_id', 'twitter_author_uid', 'twitter_images_id', 'twitter_content', 'twitter_htmlcontent', 'twitter_create_time', 'twitter_show_type', 'twitter_goods_id', 'twitter_source_code', 'twitter_source_tid'));
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
        return $pictures;
	}

	public function fetchGoodsInfo($goodsIds) {
		$goodsHelper = new Goods(array('goods_id', 'goods_title', 'goods_url', 'goods_pic_url', 'goods_pic_id'));
		$goodsInfos = $goodsHelper->getGoodsByGids($goodsIds);
		return $goodsInfos;
	}

	public function fetchTwitterStats($tids) {
		$twitterStats = TwitterStat::objects()->filter($tids)->get();
		return $twitterStats;
	}

    /**
     * 初始化变量
     **/
    private function _init() {
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
