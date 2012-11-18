<?php
namespace Snake\Package\Medal;

Use \Snake\Package\Medal\Helper\DBMedalHelper       AS DBMedalHelper;
Use \Snake\Package\User\Helper\RedisUserConnectHelper;
Use \Snake\Package\Medal\Medal;
Use \Snake\Package\Msg\Msg;
Use \Snake\Package\User\UserStatistic;
Use \Snake\Package\Shareoutside\ShareHelper;

class MedalLib {
	var $userId;
	var $stat;
	var $topicNum;	//保留最近一次查询的标签数量，以便发私信时用

	static $cache = array();

	private $urls = array('74sina' =>'http://t.cn/zlLNuNb', '74qzone' => 'http://url.cn/7mjrx8', '75sina' => 'http://t.cn/zlLNYRY', '75qzone' => 'http://url.cn/5CYgb5', '76sina' => 'http://t.cn/zlLNnf6' , '76qzone' => 'http://url.cn/5ZMDMt');

	function medalLib($userId , $getFromMaster=false) {
		$userStat = UserStatistic::getInstance()->getUserStatisticByUid($userId , $getFromMaster);

		$this->userId = $userId;
		$this->stat = $userStat[0];
		$this->topicNum = 0;
	}

	/**
	 * 刷新用户medal
	 * @params $exp 可以是medal类型或者medal_id
	 * @params $isId boolean True 表示$exp中传的是medal_id,
	 * False表示$exp为medal类型 const String e.g. MEDAL_EXP_PERSON, MEDAL_EXP_TOPIC
	 */
	function refreshMedals($exp, $isId = FALSE) {
		$medalHelper = new Medal();
		if ($isId) {
			$medalInfo = $medalHelper->getMedalInfoByMids(array($exp));
		}
		else {
			$medalInfo = self::getCommonInfo('medal_info', $exp);
		}
		//判断每个勋章
		foreach ($medalInfo as $m) {
			if (empty($m['medal_condition_exp'])) {
				continue;
			}
			$expArr = explode("\n", $m['medal_condition_exp']);
			//判断每个条件是否都符合
			$pass = true;
			foreach ($expArr as $e) {
				$pass = $pass && $this->computingExp($e);
				if ($pass === false) {
					break;
				}
			}
			$medalNum = $medalHelper->getUserMedalNum($this->userId, $m['medal_id']);
			if ($pass) {
				//授予勋章
				if ($medalNum == 0) {
					$medalHelper->addUserMedal($this->userId, $m['medal_id'], false);
					$this->sendSysMsg($m);
					//$this->publishTwitter($m['medal_title'],$m['medal_id'] );
				}
			} 
			else {
				//删除勋章
				if ($medalNum > 0) {
					$medalHelper->deleteUserMedal($this->userId, $m['medal_id']);
				}
			}
		}
	}

	//mickey mouse活动medalId ＝72
	public function addMedalForActivity($medalId, $msg = '') {
		$medalHelper = new Medal();
        if (!empty($medalId)) {
            $exist = $medalHelper->getUserMedalNum($this->userId, $medalId);
            if (empty($exist)) {
                $medalHelper->addUserMedal($this->userId, $medalId, FALSE);
                $medalInfo = $medalHelper->getMedalInfoByMids(array($medalId));

                if (!empty($msg)) {
                    $this->sendSysMsgForActivity($msg);
                }   
				elseif ($medalInfo[$medalId]['medal_notice']) {
					$this->sendSysMsg($medalInfo[$medalId]);
				}
				$this->shareAssemble($medalId, $medalInfo);
            }   
        }   
	}

    //7夕互联登录可获得medal
    public function addMedalForQixi($medalId, $shareData) {
		$medalHelper = new Medal();
        if (!empty($medalId)) {
            $exist = $medalHelper->getUserMedalNum($this->userId, $medalId);
            if (empty($exist)) {
                $medalHelper->addUserMedal($this->userId, $medalId, FALSE);
                $medalInfo = $medalHelper->getMedalInfoByMids(array($medalId));
                //$this->sendSysMsgForQixi($this->userId);
				$this->shareOutsitesQixi($this->userId, $medalInfo, $medalId, $shareData);
            }   
        }   
    }   

    public function sendSysMsgForActivity($msg) {
        //$msg = '<div>感谢你在七夕情人节期间登录了美丽说，你已自动获得了美丽说七夕“乐吾”勋章，无论有没有情人，让自己快乐最重要哦！同时您也获得了美丽说官方系统自动抽取珂兰精美礼品的机会，在8月29日登录并查收系统消息，也许你就在获奖名单里哦！点击查看你的勋章>>>>> <a href="http://www.meilishuo.com/medal/detail/71">http://www.meilishuo.com/medal/detail/71</a></div>';
        //$nickname = userModel::getInstance()->getUserNickById($this->userId);
        //$msg = strtr($msg, array('%nickname' => $nickname));
        //TODO 添加判断条件
		$msgHelper = new Msg();
        $msgHelper->sendSysMsg($this->userId, $msg);
    }   

	public function shareOutsitesQixi($user_id, $medalInfo, $medalId, $shareData) {
		if (isset($shareData['qzone']) && $shareData['qzone'] == 1) {
			$content = ">>美丽说，陪你美丽每一天！";
			$extras['url'] = 'http://url.cn/8V3HPf';
			$extras['comment'] = '嘿嘿，今天我领取了【乐吾勋章】，无论有没有男友过七夕，都应该让自己更快乐！8月22-24日到美丽说签到，即可领取#美丽说七夕乐吾勋章#，分享勋章还有珂兰钻石惊喜礼！开心的好事就要与你分享>>>' . $extras['url'];
			$extras['image'] = 'http://img.meilishuo.net/css/images/medal/icons/' . $medalInfo[$medalId]['medal_icon'];
			ShareHelper::sync($user_id, 'medal', '', 4, 0, $content, '', $extras);
		}
		if (isset($shareData['weibo']) && $shareData['weibo'] == 1) {
			$url = "http://t.cn/zW8mNsk";
			$content = "嘿嘿，今天我领取了【乐吾勋章】，无论有没有男友过七夕，都应该让自己更快乐！8月22-24日到美丽说签到，即可领取#美丽说七夕乐吾勋章#，分享勋章还有珂兰钻石惊喜礼！开心的好事就要与你分享>>>" . $url;
			ShareHelper::sync($user_id, 'medal', '', 3, 0, $content, null, array('image' => 'http://img.meilishuo.net/css/images/medal/icons/' . $medalInfo[$medalId]['medal_icon']));
		}
	}

	public function shareOutsites($user_id, $medalInfo, $medalId, $shareQzone, $shareSina) {
        //$retq = json_decode(RedisUserConnectHelper::getUserSetting('qzone', $user_id), TRUE);
        //if ($retq['sync_goods'] == 1) { 
            $tokenq = RedisUserConnectHelper::getUserToken('qzone', $user_id);
            $openId = RedisUserConnectHelper::getUserAuth('qzone', $user_id);
            if (!empty($tokenq) && !empty($openId) && !empty($shareQzone['url'])) {
                ShareHelper::sync($user_id, 'medal', '', 4, 0, $shareQzone['content'], '', array('url' => $shareQzone['url'], 'image' => $shareQzone['image'], 'comment' => $shareQzone['comment']));
            }    
        //}  
		
        //$retw = json_decode(RedisUserConnectHelper::getUserSetting('weibo', $user_id), TRUE);
		//if ($retw['sync_goods'] == 1) {
			$tokenw = RedisUserConnectHelper::getUserToken('weibo', $user_id);
			if (!empty($tokenw)) {
				ShareHelper::sync($user_id, 'medal', '', 3, 0, $shareSina['content'], null, array('image' => $shareSina['image']));
			}
		//}
	}

	public function sendSysMsg($m, $extParams = array()) {
		$msg = $m['medal_notice'];
		if (empty($msg)) {
			return;
		}

		$replaceArr = array_merge($this->stat, $extParams);
		$replaceArr['medal_title'] = $m['medal_title'];
		$replaceArr['topic_num'] = $this->topicNum;
		$replaceArr['user_id'] = $this->userId;

		foreach($replaceArr as $k=>$v) {
			$msg = str_replace('{/$'.$k.'/}', $v, $msg);
		}
		$msgHelper = new Msg();
		$msgHelper->sendSysMsg($this->userId, $msg);
	}

	/**
	 * 添加勋章，同时发条系统消息，发一条微博
	 */
	function addMedalActions($medalId) {
		//授予勋章
		$medal = new Medal();
		$medal->addUserMedal($this->userId, $medalId, FALSE);
		//发私信
		$medalInfo = $medal->getMedalInfoByMids(array($medalId));
		$this->sendSysMsg($medalInfo[$medalId]);
		//分享到站外
		$this->shareAssemble($medalId, $medalInfo);
	}

	public function shareAssemble($medalId, $medalInfo) {
		$shareQzone = array();
		$shareQzone['content'] = ">>美丽说，陪你美丽每一天！";
		$keyQzone = $medalId . 'qzone';
		$shareQzone['url'] = ($medalId == 54) ? '' : $this->urls[$keyQzone];
		$shareQzone['comment'] = $medalInfo[$medalId]['medal_share_wording'] . $shareQzone['url'];
		$shareQzone['image'] = 'http://i.meilishuo.net/css/images/medal/icons/big_' . $medalInfo[$medalId]['medal_icon'];

		$shareSina = array();
		$keySina = $medalId . 'sina';
		$shareSina['url'] = ($medalId == 54) ? "http://www.meilishuo.com/person/u/" . $this->userId . '?frm=huiliu_weibozhuce' : $this->urls[$keySina];
		$shareSina['content'] = $medalInfo[$medalId]['medal_share_wording'] . $shareSina['url'];
		$shareSina['image'] = ($medalId == 54) ? 'http://i.meilishuo.net/css/images/medal/icons/' . $medalInfo[$medalId]['medal_icon'] : 'http://i.meilishuo.net/css/images/medal/icons/big_' . $medalInfo[$medalId]['medal_icon'];
		$this->shareOutsites($this->userId, $medalInfo, $medalId, $shareQzone, $shareSina);
	}

	/*
	 * 给用户发勋章的同时以用户的名义发布一条推
	 */
	public function publishTwitter($medal_title ,$medal_id=0) {

		$sqlData['twitter_show_type'] = 2;
		$sqlData['twitter_author_uid'] = $this->userId;
		$sqlData['twitter_images_id'] = 0;
		$sqlData['twitter_source_code'] = "web";

		$medal_title = rtrim($medal_title, '勋章');
		$href = "http://www.meilishuo.com/medal/u/" . $this->userId;
		$href_l = $this->getStringUrl($href,true);
		if( $medal_id == 1 ){
			$sqlData['twitter_content'] = "我在美丽说参与了100个讨论，获得了".$medal_title."勋章，和姐妹们讨论很开心哦。欢迎围观。".$href_l."<img style='width:57px;height:57px;display:block;' src='http://www.meilishuo.com/css/images/medal/icons/r1.png'/>";
			$sqlData['twitter_htmlcontent'] = "我在美丽说参与了100个讨论，获得了".$medal_title."勋章，和姐妹们讨论很开心哦。欢迎围观。{$href_l}<img src='http://www.meilishuo.com/css/images/medal/icons/r1.png' style='display:block;width:57px;height:57px;'/>";
		}elseif( $medal_id == 2 ){
			$sqlData['twitter_content'] = "我在美丽说参与了500个讨论，获得了".$medal_title."勋章，和姐妹们讨论很开心哦。欢迎围观。".$href_l."<img style='width:57px;height:57px;display:block;' src='http://www.meilishuo.com/css/images/medal/icons/r2.png'/>";
			$sqlData['twitter_htmlcontent'] = "我在美丽说参与了500个讨论，获得了".$medal_title."勋章，和姐妹们讨论很开心哦。欢迎围观。{$href_l}<img src='http://www.meilishuo.com/css/images/medal/icons/r2.png' style='display:block;width:57px;height:57px;'/>";
		}elseif( $medal_id == 3 ){
			$sqlData['twitter_content'] = "我在美丽说参与了3000个讨论，获得了".$medal_title."勋章，和姐妹们讨论很开心哦。欢迎围观。".$href_l."<img style='width:57px;height:57px;display:block;' src='http://www.meilishuo.com/css/images/medal/icons/r3.png'/>";
			$sqlData['twitter_htmlcontent'] = "我在美丽说参与了3000个讨论，获得了".$medal_title."勋章，和姐妹们讨论很开心哦。欢迎围观。{$href_l}<img src='http://www.meilishuo.com/css/images/medal/icons/r3.png' style='display:block;width:57px;height:57px;'/>";
		}elseif( $medal_id == 4 ){
			$sqlData['twitter_content'] = "我在美丽说参与了10000个讨论，获得了".$medal_title."勋章，和姐妹们讨论很开心哦。欢迎围观。".$href_l."<img style='width:57px;height:57px;display:block;' src='http://www.meilishuo.com/css/images/medal/icons/r4.png'/>";
			$sqlData['twitter_htmlcontent'] = "我在美丽说参与了10000个讨论，获得了".$medal_title."勋章，和姐妹们讨论很开心哦。欢迎围观。{$href_l}<img src='http://www.meilishuo.com/css/images/medal/icons/r4.png' style='display:block;width:57px;height:57px;'/>";
		}elseif( $medal_id == 5 ){
			$phref = "http://www.meilishuo.com/person/u/" . $this->userId;
			$phref_l = $this->getStringUrl($phref, true);
			$sqlData['twitter_content'] = "我在美丽说分享了5个宝贝，获得了" . $medal_title . "勋章，我的海报越来越丰富啦！快来送我小红心吧！" . $phref_l."<img style='width:57px;height:57px;display:block;' src='http://www.meilishuo.com/css/images/medal/icons/g1.png'/>";
			$sqlData['twitter_htmlcontent'] = "我在美丽说分享了5个宝贝，获得了" . $medal_title . "勋章，我的海报越来越丰富啦！快来送我小红心吧！{$phref_l}<img src='http://www.meilishuo.com/css/images/medal/icons/g1.png' style='display:block;width:57px;height:57px;'/>";
		}elseif( $medal_id == 6 ){
			$phref = "http://www.meilishuo.com/person/u/" . $this->userId;
			$phref_l = $this->getStringUrl($phref, true);
			$sqlData['twitter_content'] = "我在美丽说分享了20个宝贝，获得了" . $medal_title . "勋章，我的海报越来越丰富啦！快来送我小红心吧！" . $phref_l."<img style='width:57px;height:57px;display:block;' src='http://www.meilishuo.com/css/images/medal/icons/g2.png'/>";
			$sqlData['twitter_htmlcontent'] = "我在美丽说分享了20个宝贝，获得了" . $medal_title . "勋章，我的海报越来越丰富啦！快来送我小红心吧！{$phref_l}<img src='http://www.meilishuo.com/css/images/medal/icons/g2.png' style='display:block;width:57px;height:57px;'/>";
		}elseif( $medal_id == 7 ){
			$phref = "http://www.meilishuo.com/person/u/" . $this->userId;
			$phref_l = $this->getStringUrl($phref, true);
			$sqlData['twitter_content'] = "我在美丽说分享了50个宝贝，获得了" . $medal_title . "勋章，我的海报越来越丰富啦！快来送我小红心吧！" . $phref_l."<img style='width:57px;height:57px;display:block;' src='http://www.meilishuo.com/css/images/medal/icons/g3.png'/>";
			$sqlData['twitter_htmlcontent'] = "我在美丽说分享了50个宝贝，获得了" . $medal_title . "勋章，我的海报越来越丰富啦！快来送我小红心吧！{$phref_l}<img src='http://www.meilishuo.com/css/images/medal/icons/g3.png' style='display:block;width:57px;height:57px;'/>";
		}elseif( $medal_id == 8 ){
			$phref = "http://www.meilishuo.com/person/u/" . $this->userId;
			$phref_l = $this->getStringUrl($phref, true);
			$sqlData['twitter_content'] = "我在美丽说分享了200个宝贝，获得了" . $medal_title . "勋章，我的海报越来越丰富啦！快来送我小红心吧！" . $phref_l."<img style='width:57px;height:57px;display:block;' src='http://www.meilishuo.com/css/images/medal/icons/g4.png'/>";
			$sqlData['twitter_htmlcontent'] = "我在美丽说分享了200个宝贝，获得了" . $medal_title . "勋章，我的海报越来越丰富啦！快来送我小红心吧！{$phref_l}<img src='http://www.meilishuo.com/css/images/medal/icons/g4.png' style='display:block;width:57px;height:57px;'/>";
		}elseif( $medal_id == 9 ){
			$phref = "http://www.meilishuo.com/person/u/" . $this->userId;
			$phref_l = $this->getStringUrl($phref, true);
			$sqlData['twitter_content'] = "我在美丽说分享了500个宝贝，获得了" . $medal_title . "勋章，我的海报越来越丰富啦！快来送我小红心吧！" . $phref_l."<img style='width:57px;height:57px;display:block;' src='http://www.meilishuo.com/css/images/medal/icons/g5.png'/>";
			$sqlData['twitter_htmlcontent'] = "我在美丽说分享了500个宝贝，获得了" . $medal_title . "勋章，我的海报越来越丰富啦！快来送我小红心吧！{$phref_l}<img src='http://www.meilishuo.com/css/images/medal/icons/g5.png' style='display:block;width:57px;height:57px;'/>";
		}elseif( $medal_id == 10 ){
			$phref = "http://www.meilishuo.com/person/u/" . $this->userId;
			$phref_l = $this->getStringUrl($phref, true);
			$sqlData['twitter_content'] = "我在美丽说分享了1000个宝贝，获得了" . $medal_title . "勋章，我的海报越来越丰富啦！快来送我小红心吧！" . $phref_l."<img style='width:57px;height:57px;display:block;' src='http://www.meilishuo.com/css/images/medal/icons/g6.png'/>";
			$sqlData['twitter_htmlcontent'] = "我在美丽说分享了1000个宝贝，获得了" . $medal_title . "勋章，我的海报越来越丰富啦！快来送我小红心吧！{$phref_l}<img src='http://www.meilishuo.com/css/images/medal/icons/g6.png' style='display:block;width:57px;height:57px;'/>";
		}elseif( $medal_id == 11 ){
			$sqlData['twitter_content'] = "我在美丽说回答了5个问题，获得了".$medal_title."勋章，做一个乐于助人的好姐妹。欢迎围观。".$href_l."<img style='width:57px;height:57px;display:block;' src='http://www.meilishuo.com/css/images/medal/icons/a1.png'/>";
			$sqlData['twitter_htmlcontent'] = "我在美丽说回答了5个问题，获得了".$medal_title."勋章，做一个乐于助人的好姐妹。欢迎围观。{$href_l}<img src='http://www.meilishuo.com/css/images/medal/icons/a1.png' style='display:block;width:57px;height:57px;'/>";
		}elseif( $medal_id == 12 ){
			$sqlData['twitter_content'] = "我在美丽说回答了50个问题，获得了".$medal_title."勋章，做一个乐于助人的好姐妹。欢迎围观。".$href_l."<img style='width:57px;height:57px;display:block;' src='http://www.meilishuo.com/css/images/medal/icons/a2.png'/>";
			$sqlData['twitter_htmlcontent'] = "我在美丽说回答了50个问题，获得了".$medal_title."勋章，做一个乐于助人的好姐妹。欢迎围观。{$href_l}<img src='http://www.meilishuo.com/css/images/medal/icons/a2.png' style='display:block;width:57px;height:57px;'/>";
		}elseif( $medal_id == 13 ){
			$sqlData['twitter_content'] = "我在美丽说回答了100个问题，获得了".$medal_title."勋章，做一个乐于助人的好姐妹。欢迎围观。".$href_l."<img style='width:57px;height:57px;display:block;' src='http://www.meilishuo.com/css/images/medal/icons/a3.png'/>";
			$sqlData['twitter_htmlcontent'] = "我在美丽说回答了100个问题，获得了".$medal_title."勋章，做一个乐于助人的好姐妹。欢迎围观。{$href_l}<img src='http://www.meilishuo.com/css/images/medal/icons/a3.png' style='display:block;width:57px;height:57px;'/>";
		}elseif( $medal_id == 14 ){
			$sqlData['twitter_content'] = "我在美丽说问了5个问题，获得了".$medal_title."勋章，我是一个好奇宝宝，谢谢姐妹们的回答。争当好奇宝宝。欢迎围观。".$href_l."<img style='width:57px;height:57px;display:block;' src='http://www.meilishuo.com/css/images/medal/icons/q1.png'/>";
			$sqlData['twitter_htmlcontent'] = "我在美丽说问了5个问题，获得了".$medal_title."勋章，我是一个好奇宝宝，谢谢姐妹们的回答。争当好奇宝宝。欢迎围观。{$href_l}<img src='http://www.meilishuo.com/css/images/medal/icons/q1.png' style='display:block;width:57px;height:57px;'/>";
		}elseif( $medal_id == 15 ){
			$sqlData['twitter_content'] = "我在美丽说问了20个问题，获得了".$medal_title."勋章，我是一个好奇宝宝，谢谢姐妹们的回答。争当好奇宝宝。欢迎围观。".$href_l."<img style='width:57px;height:57px;display:block;' src='http://www.meilishuo.com/css/images/medal/icons/q2.png'/>";
			$sqlData['twitter_htmlcontent'] = "我在美丽说问了20个问题，获得了".$medal_title."勋章，我是一个好奇宝宝，谢谢姐妹们的回答。争当好奇宝宝。欢迎围观。{$href_l}<img src='http://www.meilishuo.com/css/images/medal/icons/q2.png' style='display:block;width:57px;height:57px;'/>";
		}elseif( $medal_id == 16 ){
			$sqlData['twitter_content'] = "我在美丽说问了50个问题，获得了".$medal_title."勋章，我是一个好奇宝宝，谢谢姐妹们的回答。争当好奇宝宝。欢迎围观。".$href_l."<img style='width:57px;height:57px;display:block;' src='http://www.meilishuo.com/css/images/medal/icons/q3.png'/>";
			$sqlData['twitter_htmlcontent'] = "我在美丽说问了50个问题，获得了".$medal_title."勋章，我是一个好奇宝宝，谢谢姐妹们的回答。争当好奇宝宝。欢迎围观。{$href_l}<img src='http://www.meilishuo.com/css/images/medal/icons/q3.png' style='display:block;width:57px;height:57px;'/>";
		}elseif( $medal_id == 17 ){
			$sqlData['twitter_content'] = "我在美丽说问了100个问题，获得了".$medal_title."勋章，我是一个好奇宝宝，谢谢姐妹们的回答。争当好奇宝宝。欢迎围观。".$href_l."<img style='width:57px;height:57px;display:block;' src='http://www.meilishuo.com/css/images/medal/icons/q4.png'/>";
			$sqlData['twitter_htmlcontent'] = "我在美丽说问了100个问题，获得了".$medal_title."勋章，我是一个好奇宝宝，谢谢姐妹们的回答。争当好奇宝宝。欢迎围观。{$href_l}<img src='http://www.meilishuo.com/css/images/medal/icons/q4.png' style='display:block;width:57px;height:57px;'/>";
		}elseif( $medal_id == 18 ){
			$sqlData['twitter_content'] = "我有5个答案被姐妹们评为最佳答案，获得了".$medal_title."勋章，在问答中帮助姐妹们自己也很开心。欢迎围观。".$href_l."<img style='width:57px;height:57px;display:block;' src='http://www.meilishuo.com/css/images/medal/icons/ba1.png'/>";
			$sqlData['twitter_htmlcontent'] = "我有5个答案被姐妹们评为最佳答案，获得了".$medal_title."勋章，在问答中帮助姐妹们自己也很开心。欢迎围观。{$href_l}<img src='http://www.meilishuo.com/css/images/medal/icons/ba1.png' style='display:block;width:57px;height:57px;'/>";
		}elseif( $medal_id == 19 ){
			$sqlData['twitter_content'] = "我有20个答案被姐妹们评为最佳答案，获得了".$medal_title."勋章，在问答中帮助姐妹们自己也很开心。欢迎围观。".$href_l."<img style='width:57px;height:57px;display:block;' src='http://www.meilishuo.com/css/images/medal/icons/ba2.png'/>";
			$sqlData['twitter_htmlcontent'] = "我有20个答案被姐妹们评为最佳答案，获得了".$medal_title."勋章，在问答中帮助姐妹们自己也很开心。欢迎围观。{$href_l}<img src='http://www.meilishuo.com/css/images/medal/icons/ba2.png' style='display:block;width:57px;height:57px;'/>";
		}elseif( $medal_id == 20 ){
			$sqlData['twitter_content'] = "我有50个答案被姐妹们评为最佳答案，获得了".$medal_title."勋章，在问答中帮助姐妹们自己也很开心。欢迎围观。".$href_l."<img style='width:57px;height:57px;display:block;' src='http://www.meilishuo.com/css/images/medal/icons/ba3.png'/>";
			$sqlData['twitter_htmlcontent'] = "我有50个答案被姐妹们评为最佳答案，获得了".$medal_title."勋章，在问答中帮助姐妹们自己也很开心。欢迎围观。{$href_l}<img src='http://www.meilishuo.com/css/images/medal/icons/ba3.png' style='display:block;width:57px;height:57px;'/>";
		}elseif( $medal_id == 21 ){
			$sqlData['twitter_content'] = "我有100个答案被姐妹们评为最佳答案，获得了".$medal_title."勋章，在问答中帮助姐妹们自己也很开心。欢迎围观。".$href_l."<img style='width:57px;height:57px;display:block;' src='http://www.meilishuo.com/css/images/medal/icons/ba4.png'/>";
			$sqlData['twitter_htmlcontent'] = "我有100个答案被姐妹们评为最佳答案，获得了".$medal_title."勋章，在问答中帮助姐妹们自己也很开心。欢迎围观。{$href_l}<img src='http://www.meilishuo.com/css/images/medal/icons/ba4.png' style='display:block;width:57px;height:57px;'/>";
		}elseif( $medal_id == 22 ){
			$sqlData['twitter_content'] = "我有200个答案被姐妹们评为最佳答案，获得了".$medal_title."勋章，在问答中帮助姐妹们自己也很开心。欢迎围观。".$href_l."<img style='width:57px;height:57px;display:block;' src='http://www.meilishuo.com/css/images/medal/icons/ba5.png'/>";
			$sqlData['twitter_htmlcontent'] = "我有200个答案被姐妹们评为最佳答案，获得了".$medal_title."勋章，在问答中帮助姐妹们自己也很开心。欢迎围观。{$href_l}<img src='http://www.meilishuo.com/css/images/medal/icons/ba5.png' style='display:block;width:57px;height:57px;'/>";
		}elseif( $medal_id == 23 ){
			$sqlData['twitter_content'] = "哇，不知不觉就买成黄钻了，我通过了".$medal_title."勋章申请，如果你是黄钻，还等什么，快去申请吧。".$href_l."<img style='width:57px;height:57px;display:block;' src='http://www.meilishuo.com/css/images/medal/icons/hzmj.png'/>";
			$sqlData['twitter_htmlcontent'] = "哇，不知不觉就买成黄钻了，我通过了".$medal_title."勋章申请，如果你是黄钻，还等什么，快去申请吧。{$href_l}<img src='http://www.meilishuo.com/css/images/medal/icons/hzmj.png' style='display:block;width:57px;height:57px;'/>";
		}elseif( $medal_id == 24 ){
			$sqlData['twitter_content'] = "作为一名时尚达人，就不要吝惜，我通过了".$medal_title."勋章申请，姐妹们也可以去申请哦。欢迎围观。".$href_l."<img style='width:57px;height:57px;display:block;' src='http://www.meilishuo.com/css/images/medal/icons/tbrz.png'/>";
			$sqlData['twitter_htmlcontent'] = "作为一名时尚达人，就不要吝惜，我通过了".$medal_title."勋章申请，姐妹们也可以去申请哦。欢迎围观。{$href_l}<img src='http://www.meilishuo.com/css/images/medal/icons/tbrz.png' style='display:block;width:57px;height:57px;'/>";
		}elseif( $medal_id == 25 ){
			$sqlData['twitter_content'] = "哪里有差价，就指向哪里，让美丽说的姐妹不多花一分的冤枉银子。我就是传说中独步江湖、人见人爱、花见花开的差价女王啦！我通过了".$medal_title."勋章申请，欢迎围观。".$href_l."<img style='width:57px;height:57px;display:block;' src='http://www.meilishuo.com/css/images/medal/icons/cjnw.png'/>";
			$sqlData['twitter_htmlcontent'] = "哪里有差价，就指向哪里，让美丽说的姐妹不多花一分的冤枉银子。我就是传说中独步江湖、人见人爱、花见花开的差价女王啦！我通过了".$medal_title."勋章申请，欢迎围观。{$href_l}<img src='http://www.meilishuo.com/css/images/medal/icons/cjnw.png' style='display:block;width:57px;height:57px;'/>";
		}elseif( $medal_id == 26 ){
			$sqlData['twitter_content'] = "我就像拥有一架精准的白菜雷达，任何价廉物美质感棒棒的宝贝，都逃不出我的眼睛。我通过了".$medal_title."勋章申请，如果你和我一样，还等什么，快去申请吧。".$href_l."<img style='width:57px;height:57px;display:block;' src='http://www.meilishuo.com/css/images/medal/icons/bcdr.png'/>";
			$sqlData['twitter_htmlcontent'] = "我就像拥有一架精准的白菜雷达，任何价廉物美质感棒棒的宝贝，都逃不出我的眼睛。我通过了".$medal_title."勋章申请，如果你和我一样，还等什么，快去申请吧。{$href_l}<img src='http://www.meilishuo.com/css/images/medal/icons/bcdr.png' style='display:block;width:57px;height:57px;'/>";
		}elseif( $medal_id == 27 ){
			$sqlData['twitter_content'] = "我精通美妆，擅长伪妆，彩妆问题问我准没错！我通过了".$medal_title."勋章申请，如果你也乐于分享心水宝贝、技巧心得，就快快申请美妆维纳斯勋章吧！".$href_l."<img style='width:57px;height:57px;display:block;' src='http://www.meilishuo.com/css/images/medal/icons/czdr.png'/>";
			$sqlData['twitter_htmlcontent'] = "我精通美妆，擅长伪妆，彩妆问题问我准没错！我通过了".$medal_title."勋章申请，如果你也乐于分享心水宝贝、技巧心得，就快快申请美妆维纳斯勋章吧！{$href_l}<img src='http://www.meilishuo.com/css/images/medal/icons/czdr.png' style='display:block;width:57px;height:57px;'/>";
		}elseif( $medal_id == 30 ){
			$sqlData['twitter_content'] = "我已经在“豹纹”标签下分享了10个以上的宝贝，获得了".$medal_title."勋章，如果你喜欢豹纹好物，你也应该拥有它，欢迎围观。".$href_l."<img style='width:57px;height:57px;display:block;' src='http://www.meilishuo.com/css/images/medal/icons/s1.png'/>";
			$sqlData['twitter_htmlcontent'] = "我已经在“豹纹”标签下分享了10个以上的宝贝，获得了".$medal_title."勋章，如果你喜欢豹纹好物，你也应该拥有它，欢迎围观。{$href_l}<img src='http://www.meilishuo.com/css/images/medal/icons/s1.png' style='display:block;width:57px;height:57px;'/>";
		}elseif( $medal_id == 31 ){
			$sqlData['twitter_content'] = "我已经在“高跟鞋”标签下分享了10个以上的宝贝，获得了".$medal_title."勋章，如果你喜欢高跟鞋好物，你也应该拥有它，欢迎围观。".$href_l."<img style='width:57px;height:57px;display:block;' src='http://www.meilishuo.com/css/images/medal/icons/s2.png'/>";
			$sqlData['twitter_htmlcontent'] = "我已经在“高跟鞋”标签下分享了10个以上的宝贝，获得了".$medal_title."勋章，如果你喜欢高跟鞋好物，你也应该拥有它，欢迎围观。{$href_l}<img src='http://www.meilishuo.com/css/images/medal/icons/s2.png' style='display:block;width:57px;height:57px;'/>";
		}elseif( $medal_id == 32 ){
			$sqlData['twitter_content'] = "我已经在“波点”标签下分享了10个以上的宝贝，获得了".$medal_title."勋章，如果你喜欢波点好物，你也应该拥有它，欢迎围观。".$href_l."<img style='width:57px;height:57px;display:block;' src='http://www.meilishuo.com/css/images/medal/icons/s3.png'/>";
			$sqlData['twitter_htmlcontent'] = "我已经在“波点”标签下分享了10个以上的宝贝，获得了".$medal_title."勋章，如果你喜欢波点好物，你也应该拥有它，欢迎围观。{$href_l}<img src='http://www.meilishuo.com/css/images/medal/icons/s3.png' style='display:block;width:57px;height:57px;'/>";
		}elseif( $medal_id == 33 ){
			$sqlData['twitter_content'] = "我已经在“条纹”标签下分享了10个以上的宝贝，获得了".$medal_title."勋章，如果你喜欢条纹好物，你也应该拥有它，欢迎围观。".$href_l."<img style='width:57px;height:57px;display:block;' src='http://www.meilishuo.com/css/images/medal/icons/s4.png'/>";
			$sqlData['twitter_htmlcontent'] = "我已经在“条纹”标签下分享了10个以上的宝贝，获得了".$medal_title."勋章，如果你喜欢条纹好物，你也应该拥有它，欢迎围观。{$href_l}<img src='http://www.meilishuo.com/css/images/medal/icons/s4.png' style='display:block;width:57px;height:57px;'/>";
		}elseif( $medal_id == 34 ){
			$sqlData['twitter_content'] = "我已经在“格子”标签下分享了10个以上的宝贝，获得了".$medal_title."勋章，如果你喜欢格子好物，你也应该拥有它，欢迎围观。".$href_l."<img style='width:57px;height:57px;display:block;' src='http://www.meilishuo.com/css/images/medal/icons/s5.png'/>";
			$sqlData['twitter_htmlcontent'] = "我已经在“格子”标签下分享了10个以上的宝贝，获得了".$medal_title."勋章，如果你喜欢格子好物，你也应该拥有它，欢迎围观。{$href_l}<img src='http://www.meilishuo.com/css/images/medal/icons/s5.png' style='display:block;width:57px;height:57px;'/>";
		}elseif( $medal_id == 35 ){
			$sqlData['twitter_content'] = "我已经在“裸色”标签下分享了10个以上的宝贝，获得了".$medal_title."勋章，如果你喜欢裸色好物，你也应该拥有它，欢迎围观。".$href_l."<img style='width:57px;height:57px;display:block;' src='http://www.meilishuo.com/css/images/medal/icons/s6.png'/>";
			$sqlData['twitter_htmlcontent'] = "我已经在“裸色”标签下分享了10个以上的宝贝，获得了".$medal_title."勋章，如果你喜欢裸色好物，你也应该拥有它，欢迎围观。{$href_l}<img src='http://www.meilishuo.com/css/images/medal/icons/s6.png' style='display:block;width:57px;height:57px;'/>";
		}elseif( $medal_id == 36 ){
			$sqlData['twitter_content'] = "我已经在“蕾丝”标签下分享了10个以上的宝贝，获得了".$medal_title."勋章，如果你喜欢蕾丝好物，你也应该拥有它，欢迎围观。".$href_l."<img style='width:57px;height:57px;display:block;' src='http://www.meilishuo.com/css/images/medal/icons/s7.png'/>";
			$sqlData['twitter_htmlcontent'] = "我已经在“蕾丝”标签下分享了10个以上的宝贝，获得了".$medal_title."勋章，如果你喜欢蕾丝好物，你也应该拥有它，欢迎围观。{$href_l}<img src='http://www.meilishuo.com/css/images/medal/icons/s7.png' style='display:block;width:57px;height:57px;'/>";
		}elseif( $medal_id == 37 ){
			$phref = "http://www.meilishuo.com/person/u/".$this->userId;
			$phref_l = $this->getStringUrl($phref, true);
			$sqlData['twitter_content'] = "女孩们！有什么比穿一件简单的T恤更能表达你的态度？我已经在 @美丽说 分享了10件值得拥有T恤，获得了一枚".$medal_title."勋章，我推荐的有你喜欢的吗？".$phref_l."<img style='width:57px;height:57px;display:block;' src='http://www.meilishuo.com/css/images/medal/icons/s9.png'/>";
			$sqlData['twitter_htmlcontent'] = "女孩们！有什么比穿一件简单的T恤更能表达你的态度？我已经在 @美丽说 分享了10件值得拥有T恤，获得了一枚".$medal_title."勋章，我推荐的有你喜欢的吗？{$phref_l}<img src='http://www.meilishuo.com/css/images/medal/icons/s9.png' style='display:block;width:57px;height:57px;'/>";
		}elseif( $medal_id == 38 ){
			$sqlData['twitter_content'] = "我已经在“复古”标签下分享了10个以上的宝贝，获得了".$medal_title."勋章，如果你喜欢复古好物，你也应该拥有它，欢迎围观。".$href_l."<img style='width:57px;height:57px;display:block;' src='http://www.meilishuo.com/css/images/medal/icons/s8.png'/>";
			$sqlData['twitter_htmlcontent'] = "我已经在“复古”标签下分享了10个以上的宝贝，获得了".$medal_title."勋章，如果你喜欢复古好物，你也应该拥有它，欢迎围观。{$href_l}<img src='http://www.meilishuo.com/css/images/medal/icons/s8.png' style='display:block;width:57px;height:57px;'/>";
		}elseif( $medal_id == 39 ){
			$sqlData['twitter_content'] = "我已经在“碎花”标签下分享了10个以上的宝贝，获得了".$medal_title."勋章，如果你喜欢碎花好物，你也应该拥有它，欢迎围观。".$href_l."<img style='width:57px;height:57px;display:block;' src='http://www.meilishuo.com/css/images/medal/icons/s10.png'/>";
			$sqlData['twitter_htmlcontent'] = "我已经在“碎花”标签下分享了10个以上的宝贝，获得了".$medal_title."勋章，如果你喜欢碎花好物，你也应该拥有它，欢迎围观。{$href_l}<img src='http://www.meilishuo.com/css/images/medal/icons/s10.png' style='display:block;width:57px;height:57px;'/>";
		}elseif( $medal_id == 41 ){
			$sqlData['twitter_content'] = "在一周里我分享的宝贝收到了最多的喜欢，获得了".$medal_title."勋章，多谢姐妹们的厚爱哦，我会分享更多的好宝贝的，欢迎围观。".$href_l."<img style='width:57px;height:57px;display:block;' src='http://www.meilishuo.com/css/images/medal/icons/w2.png'/>";
			$sqlData['twitter_htmlcontent'] = "在一周里我分享的宝贝收到了最多的喜欢，获得了".$medal_title."勋章，多谢姐妹们的厚爱哦，我会分享更多的好宝贝的，欢迎围观。{$href_l}<img src='http://www.meilishuo.com/css/images/medal/icons/w2.png' style='display:block;width:57px;height:57px;'/>";
		}elseif( $medal_id == 42 ){
			$sqlData['twitter_content'] = "在一周里我分享的宝贝数最多，看看这伟大的贡献也得到了回报，获得了".$medal_title."勋章，我会继续这伟大的发宝事业的。欢迎围观。".$href_l."<img style='width:57px;height:57px;display:block;' src='http://www.meilishuo.com/css/images/medal/icons/w3.png'/>";
			$sqlData['twitter_htmlcontent'] = "在一周里我分享的宝贝数最多，看看这伟大的贡献也得到了回报，获得了".$medal_title."勋章，我会继续这伟大的发宝事业的。欢迎围观。{$href_l}<img src='http://www.meilishuo.com/css/images/medal/icons/w3.png' style='display:block;width:57px;height:57px;'/>";
		}elseif( $medal_id == 43 ){
			$sqlData['twitter_content'] = "在一周里我回答问题最多，获得了".$medal_title."勋章，我可真是个热心人儿啊，大家有什么问题尽管问我。欢迎围观。".$href_l."<img style='width:57px;height:57px;display:block;' src='http://www.meilishuo.com/css/images/medal/icons/w4.png'/>";
			$sqlData['twitter_htmlcontent'] = "在一周里我回答问题最多，获得了".$medal_title."勋章，我可真是个热心人儿啊，大家有什么问题尽管问我。欢迎围观。{$href_l}<img src='http://www.meilishuo.com/css/images/medal/icons/w4.png' style='display:block;width:57px;height:57px;'/>";
		}elseif( $medal_id == 44 ){
			$sqlData['twitter_content'] = "在一周里我回答的问题被选为最佳答案最多，获得了".$medal_title."勋章，我会继续努力的，欢迎围观。".$href_l."<img style='width:57px;height:57px;display:block;' src='http://www.meilishuo.com/css/images/medal/icons/w5.png'/>";
			$sqlData['twitter_htmlcontent'] = "在一周里我回答的问题被选为最佳答案最多，获得了".$medal_title."勋章，我会继续努力的，欢迎围观。{$href_l}<img src='http://www.meilishuo.com/css/images/medal/icons/w5.png' style='display:block;width:57px;height:57px;'/>";
		}elseif( $medal_id == 49 ){
			$sqlData['twitter_content'] = "我在美丽说上传了自己的真人头像，获得了".$medal_title."勋章，姐妹们也换上自己漂亮的照片当头像吧。".$href_l."<img style='width:57px;height:57px;display:block;' src='http://www.meilishuo.com/css/images/medal/icons/real.png'/>";
			$sqlData['twitter_htmlcontent'] = "我在美丽说上传了自己的真人头像，获得了".$medal_title."勋章，姐妹们也换上自己漂亮的照片当头像吧。{$href_l}<img src='http://www.meilishuo.com/css/images/medal/icons/real.png' style='display:block;width:57px;height:57px;'/>";
		//TODO
		}elseif( $medal_id == 50 ){
			$phref = "http://www.meilishuo.com/person/u/" . $this->userId;
			$phref_l = $this->getStringUrl($phref, true);
			$sqlData['twitter_content'] = "我开始美丽说啦！刚刚在 @美丽说 分享了第一个宝贝，得到了" . $medal_title . "勋章，快来送我小红心吧！".$phref_l."<img style='width:57px;height:57px;display:block;' src='http://www.meilishuo.com/css/images/medal/icons/freshman.png'/>";
			$sqlData['twitter_htmlcontent'] = "我开始美丽说啦！刚刚在 @美丽说 分享了第一个宝贝，得到了" . $medal_title . "勋章，快来送我小红心吧！{$phref_l}<img src='http://www.meilishuo.com/css/images/medal/icons/freshman.png' style='display:block;width:57px;height:57px;'/>";

		}elseif( $medal_id == 54 ){
			$phref = "http://www.meilishuo.com/person/u/".$this->userId . '?frm=huiliu_weibozhuce';
			$phref_l = $this->getStringUrl($phref, true);
			$sqlData['twitter_content'] = "我刚刚入驻了@美丽说 ，男生止步[阴险],求姑娘关注>>".$phref_l."<img style='width:57px;height:57px;display:block;' src='http://www.meilishuo.com/css/images/medal/icons/msina.png'/>";
			$sqlData['twitter_htmlcontent'] = "我刚刚入驻了@美丽说 ，男生止步[阴险],求姑娘关注>> {$phref_l}<img src='http://www.meilishuo.com/css/images/medal/icons/msina.png' style='display:block;width:57px;height:57px;'/>";
		}
		elseif( $medal_id == 55 ){
			$sqlData['twitter_content'] = "我刚刚绑定了人人网，可以随时把最新最IN的内容同步到人人网上和朋友分享哦，你们也可以马上去<a href='http://www.meilishuo.com/settings/sync'>绑定拿勋章</a>哦，欢迎围观！".$href_l."<img style='width:57px;height:57px;display:block;' src='http://www.meilishuo.com/css/images/medal/icons/mrenren.png'/>";
			$sqlData['twitter_htmlcontent'] = "我刚刚绑定了人人网，可以随时把最新最IN的内容同步到人人网上和朋友分享哦，你们也可以马上去<a href='http://www.meilishuo.com/settings/sync' target='_blank'>绑定拿勋章</a>哦，欢迎围观！{$href_l}<img src='http://www.meilishuo.com/css/images/medal/icons/mrenren.png' style='display:block;width:57px;height:57px;'/>";
		}
		elseif ($medal_id == 63) {
			$shortUrl = "http://wap.meilishuo.com/u/EAEaYb";
			$sqlData['twitter_content']     = "正在玩儿iPhone上的美丽说，超好玩儿~[爱你][爱你] 好多漂亮衣服、可爱东东！！ 拍照功能也很强大，强烈推荐美白滤镜，拍出来皮肤巨好！[偷笑]~~简直是神器！！以后开会、上课、搭地铁，统统变成Happy的逛街时间啦~ 强烈推荐你们也下载，地址在这里>> $shortUrl 。<img src='" . PICTURE_URL . "/css/images/medal/icons/iphone.png' style='display:block;width:57px;height:57px;'/>";
			$sqlData['twitter_htmlcontent'] = "正在玩儿iPhone上的美丽说，超好玩儿~[爱你][爱你] 好多漂亮衣服、可爱东东！！ 拍照功能也很强大，强烈推荐美白滤镜，拍出来皮肤巨好！[偷笑]~~简直是神器！！以后开会、上课、搭地铁，统统变成Happy的逛街时间啦~ 强烈推荐你们也下载，地址在这里>> $shortUrl 。<img src='" . PICTURE_URL . "/css/images/medal/icons/iphone.png' style='display:block;width:57px;height:57px;'/>";
		}
		elseif ($medal_id == 64) {
			$chanelUrl = "http://webapp.meilishuo.com/webapp/luck/chanel/start";
			$pchanelUrl = $this->getStringUrl($chanelUrl, true);
			$sqlData['twitter_content'] = "此时此刻，我泪流满面，我获得了美丽说限量版【Chanel纪念勋章】！要知道8月19日是可可香奈儿的生日呀！她说'流行稍纵即逝，只有风格永存'，她用时装告诉女人，敢爱敢恨，才能活出优雅惊艳的人生。你也快去领取一个吧！" . $pchanelUrl .">><img src='" . PICTURE_URL . "/css/images/medal/icons/chanel2.png' style='display:block;width:57px;height:57px;'/>";
			$sqlData['twitter_htmlcontent'] = "此时此刻，我泪流满面，我获得了美丽说限量版【Chanel纪念勋章】！要知道8月19日是可可香奈儿的生日呀！她说'流行稍纵即逝，只有风格永存'，她用时装告诉女人，敢爱敢恨，才能活出优雅惊艳的人生。你也快去领取一个吧！{$pchanelUrl} <img src='" . PICTURE_URL . "/css/images/medal/icons/chanel2.png' style='display:block;width:57px;height:57px;'/>";
		}
		elseif ($medal_id == 65) {
			$chanelUrl = "http://webapp.meilishuo.com/webapp/luck/thin/start";
			$pchanelUrl = $this->getStringUrl($chanelUrl, true);
			$sqlData['twitter_content'] = "吼吼吼~~我获得了美丽说限量版【护瘦符】勋章！我要在美丽说和姐妹们一起变得更苗条更美丽~你也去测测你这辈子还能减几斤吧？完成测试就能得勋章哦~>>>" . $pchanelUrl ." <img src='" . PICTURE_URL . "/css/images/medal/icons/thin.png' style='display:block;width:57px;height:57px;'/>";
			$sqlData['twitter_htmlcontent'] = "吼吼吼~~我获得了美丽说限量版【护瘦符】勋章！我要在美丽说和姐妹们一起变得更苗条更美丽~你也去测测你这辈子还能减几斤吧？完成测试就能得勋章哦~>>>" . $pchanelUrl ." <img src='" . PICTURE_URL . "/css/images/medal/icons/thin.png' style='display:block;width:57px;height:57px;'/>";
		}
		elseif ($medal_id == 66) {
			$sqlData['twitter_content'] = "我在#美丽许愿池 想要什么大声说#活动中，获得了美丽说和有品网联合推出的“有品勋章”一枚！<img src='" . PICTURE_URL . "/css/images/medal/icons/youpin.png' style='display:block;width:57px;height:57px;'/>";
			$sqlData['twitter_htmlcontent'] = "我在#美丽许愿池 想要什么大声说#活动中，获得了美丽说和有品网联合推出的“有品勋章”一枚！<img src='" . PICTURE_URL . "/css/images/medal/icons/youpin.png' style='display:block;width:57px;height:57px;'/>";
		}
		elseif ($medal_id == 67) {
			$chanelUrl = "http://webapp.meilishuo.com/webapp/luck/yanyu/start";
			$pchanelUrl = $this->getStringUrl($chanelUrl, true);
			$sqlData['twitter_content'] = 		"[笑] 我获得了美丽说【桃花符】！让桃花来得更猛烈一些吧！！！你也去测测十一假期你会有怎样的艳遇吧！完成测试就领取【桃花符】哦>>>". $pchanelUrl ." <img src='" . PICTURE_URL . "/css/images/medal/icons/peach.png' />";
			$sqlData['twitter_htmlcontent'] = 	"[笑] 我获得了美丽说【桃花符】！让桃花来得更猛烈一些吧！！！你也去测测十一假期你会有怎样的艳遇吧！完成测试就领取【桃花符】哦>>>". $pchanelUrl ." <img src='" . PICTURE_URL . "/css/images/medal/icons/peach.png' />";
		}
		else{
			$sqlData['twitter_content'] = "我获得了".$medal_title."勋章，一起来看看：$href_l";
			$sqlData['twitter_htmlcontent'] = "我获得了".$medal_title."勋章，一起来看看：{$href_l}";
		}
		//我在#美丽许愿池 想要什么大声说#活动中，获得了美丽说和有品网联合推出的“有品勋章”一枚！
		$sqlData['twitter_create_ip'] = "127.0.0.1";
		$sqlData['style']  = 1;
		$sqlData['twitter_create_time'] = time();
		$sqlData['twitter_reply_show'] = 1;

		/*importer('corelib.twitterFactory');
		$twitterObj = twitterFactory::createOperation('twitter');
		$twitterObj->_setData(  $sqlData , $this->userId );
		$twitterObj->_publish();
		*/

		$twittid = $twitterObj->_getData();
		/*
		 * 获得勋章时同步到新浪和人人
		 * add by gzq
		 * */
		//$logHandle = new zx_log ( "cccdebug", "normal" );
		if (isset($twittid['twitter_id']) && $medal_id > 0) {
			$medalHelper = new Medal();
			$medalInfo = $medalHelper->getMedalInfoByMids(array($medal_id));
			ShareHelper::sync($this->userId, 'medal', $twittid['twitter_id'], 0, 0, null, null, array('image' => 'http://img.meilishuo.net/css/images/medal/icons/' . $medalInfo[0]['medal_icon']));
		}
		//twitterModel::getInstance()->addNewTwitter( $sqlData );
		return true;
	}

	//缩短url使用
  	protected function getStringUrl ( $str, $checkStatus ) {
    	//echo $str.":".$checkStatus;die();
		$checkStatus = strpos($str, "http://");
		if ( $checkStatus === false ) {
			return $str;
		} else {
			$partern = '/http:\/\/[a-zA-Z.\/\=\-\_\?\&0-9%\>\<\;:\+,]*/si';
			$matchArr = array();
			preg_match( $partern, $str, $matchArr);

//			$matchArr2 = array();
		//创建缩短URL
			$url = $matchArr[0];
			$md5 = md5($url);
			$crc32 = abs(crc32($md5));
			$click_url = "";
		    if( strpos( $url , "taobao") === false){
			    ;
		    }else{
		       $taobaoUtilHelper = new \Snake\Package\TaobaoApi\TaobaokeItemsDetailGetRequest();
			   preg_match("/id=([0-9]*)/i" , $url , $matchs);

			   if(!empty( $matchs[1] )){
				   $taobaoUtilHelper->setNumiids($matchs[1]);
			       $taobaokeItem = $taobaoUtilHelper->execute($taobaoUtilHelper);
			       if(isset($taobaokeItem['taobaoke_item_details']['taobaoke_item_detail']['click_url'])){
			           $click_url = !empty($taobaokeItem['taobaoke_item_details']['taobaoke_item_detail']['click_url'])?$taobaokeItem['taobaoke_item_details']['taobaoke_item_detail']['click_url']:'';
			       }
			   }
		    }
			$url_id = urlModel::getInstance()->insertUrlInfo($url, $crc32, time(), $click_url);
			$base64 = urlModel::getInstance()->yourls_dec2base($url_id);
			$shortUrl = DIRECT_URL . 'u/' . $base64;

			//创建缩短URL - END
			$urlInfo = $url;
			if(isset($urlInfo)){
				$directUrl = $urlInfo;
			}
			else{
				$directUrl = $crc32 ;
			}

			$tempStr = "<a href='{$shortUrl}' title='原链接：".$directUrl."' target='_blank'>".$shortUrl."</a>";
			$checkStatus = $matchArr[0];
			$str = str_replace($matchArr[0],  $tempStr, $str );
			return $str;
		}
	}

	/**
	 * 计算一条表达式是否成立
	 * @param unknown_type $exp	例如：twitter:>=:50
	 */
	private function computingExp($e) {
		$params = explode(':', $e);
		switch ($params[0]) {
			case MEDAL_EXP_REPLY:
				return $this->computing($this->stat['reply_num'], $params[2], $params[1]);
			case MEDAL_EXP_GOODS:
				return $this->computing($this->stat['goods_num'], $params[2], $params[1]);
			case MEDAL_EXP_QUESTION:
				return $this->computing($this->stat['question_num'], $params[2], $params[1]);
			case MEDAL_EXP_ANSWER:
				return $this->computing($this->stat['answer_num'], $params[2], $params[1]);
			case MEDAL_EXP_BEST_ANSWER:
				return $this->computing($this->stat['best_answer_num'], $params[2], $params[1]);
			case MEDAL_EXP_PERSON:
				return $this->computing($this->stat['goods_num'], $params[2], $params[1]);
			case MEDAL_EXP_GIRL:
				return true;
			case MEDAL_EXP_TOPIC:
				$topicId = $params[1];
				$operator = $params[2];
				$num = $params[3];
				//TODO
				//$this->topicNum = topicModel::getInstance()->getUserTopicNum($this->userId, $topicId);
				//return $this->computing($this->topicNum, $num, $operator);
		}
	}

	private function computing($num1, $num2, $operator) {
		$num1 = intval($num1);
		$num2 = intval($num2);

		switch($operator) {
			case '>':
				return ($num1 > $num2);
			case '<':
				return ($num1 < $num2);
			case '>=':
				return ($num1 >= $num2);
			case '<=':
				return ($num1 <= $num2);
			case '=':
				return ($num1 == $num2);
		}
	}

	/**
	 * 从静态变量$cache中获取数据
	 * @param unknown_type $type
	 * @param unknown_type $exp
	 * @param unknown_type $arg1
	 */
	private static function getCommonInfo($type, $exp, $arg1=false) {

		//
		if ($type == 'medal_info') {
			if (!isset( self::$cache['medal_info'][$exp])) {
				self::$cache['medal_info'][$exp] = Medal::getInstance()->getMedalInfoByExp($exp);
			}
			return self::$cache['medal_info'][$exp];

		} 
		elseif ($type == 'topic_info') {
		}
	}

	//TODO
	public function handleMedalForWebapp() {
		$medalHelper = new Medal();
		if (isset($_COOKIE['MEILISHUO_APP'])) {
			$appInfoOrg = variableModel::getInstance()->variableGet('webapp_' . $_COOKIE['MEILISHUO_APP'], array(), FALSE);
			$appInfo = unserialize($appInfoOrg);
			if (!empty($appInfo['medalId'])) {
				$exist = $medalHelper->getUserMedalNum($this->userId, $appInfo['medalId']);
				if (empty($exist)) {
					$medalHelper->addUserMedal($this->userId, $appInfo['medalId'], FALSE);
					$medalInfo = $medalHelper->getMedalInfoByMids(array($appInfo['medalId']));
					$this->sendSysMsg($medalInfo[$appInfo['medalId']]);
					//发勋章的同时作为一条twitter发出去
					//$this->publishTwitter($medalInfo[$appInfo['medalId']]['medal_title'], $appInfo['medalId']);
				}
			}
		}
	}

	/**
	 * 为每周排名第一的用户发勋章
	 * @param unknown_type $date	星期一的UNIX_TIMESTAMP
	 */
	public static function awardMedalToTopUserWeekly($mondayTime) {
		$medalHelper = new Medal();
		$date = date('Ymd', $mondayTime);

		$map = array(
			'ask' => 40,
			'pop' => 41,
			'goods' => 42,
			'answer' => 43,
			'best_answer' => 44,
		);

		$topInfo = UserStatistic::getInstance()->getUserStatTopWeekly($date);
		$medalInfo = $medalHelper->getMedalInfoByMids( $map );

		$msgDate = date('n月j日', $mondayTime).'-'.date('n月j日', $mondayTime+6*24*3600);
		$altDate = date('Y年n月j日', $mondayTime + 6 * 24 * 3600);

		$altMap = array(
			'ask' => "{$altDate}获得，一周问问题位居榜首",
			'pop' => "{$altDate}获得，一周分享的宝贝收到“喜欢”“想买”“收藏”最多次",
			'goods' => "{$altDate}获得，一周分享的宝贝数最多",
			'answer' => "{$altDate}获得，一周回答的问题数量最多",
			'best_answer' => "{$altDate}获得，一周回答问题获得最佳答案最多",
		);

		foreach($map as $k=>$mid) {
			$uid = $topInfo[$k]['user_id'];
			//发勋章
			$medalHelper->addUserMedal($uid, $mid, true);
			//修改勋章的提示信息
			$medalHelper->updateUserMedal($uid, $mid, false, $altMap[$k]);
			//发私信
			$medalLibHelper = new medalLib($uid);
			$topInfo[$k]['date'] = $msgDate;
			$medalLibHelper->sendSysMsg($medalInfo[$mid], $topInfo[$k]);
			//$medalLibHelper->publishTwitter($medalInfo[$mid]['medal_title']);
		}
	}
}
