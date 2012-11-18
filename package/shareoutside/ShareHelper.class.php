<?php
namespace Snake\Package\Shareoutside;

/**
 * 分享到外站相关接口
 * @author yishuliu@meilishuo.com
 */

Use \Snake\Package\Twitter\Twitter AS Twitter;
Use \Snake\Package\Picture\Picture;
Use \Snake\Package\Picture\PictureFactory AS PictureFactory;
Use \Snake\Package\User\Helper\RedisUserConnectHelper AS RedisUserConnectHelper;
Use \Snake\Package\Oauth\SaeTClient;
Use \Snake\Package\Oauth\QzoneClient;
Use \Snake\Package\Oauth\TX_OAuth2;

/**
 * TODO 目前只支持分享到qzone,sina微博；腾讯微博,qplus暂不支持,
 * task和zoo脚本目前在dolphin上维护
 * @since 2012-06-20
 * @version 1.0
 */
class ShareHelper {
	public function __construct() {

	}
	
	//TODO 目前只支持分享到qzone,sina微博；腾讯微博暂不支持
	/**
	 * 分享到外站同步接口
	 * @author yishuliu@meilishuo.com
     * @param $userId int
	 * @param $syncType string, 可为goods,group或者为空
	 * @param $tid int 
	 * @param $offsite 为0表示全部分享,3表示分享到微博，4表示分享到qzone，8表示分享到腾讯微博，11表示分享到qplus
	 * @param $medal_id int 勋章id
	 * @param $content 分享出去的内容
	 * @param $access 用户授权token信息,i.e. 新浪为$access = array('2.00btcoPC0Swx4Vfa679081924tgYuC')；qzone为array('access_token' =>'C77B688321C4B99A1F4314C0F56E4470', 'openId' => 'B310D52746854C14D0B713DE73A8F678')。注意数组的key值错误将导致不能获取对应项. 此项可以为空，为空时将从redis中查，但不可与user_id同时为空。
	 * @param $extras 分享图片链接，包括分享title，分享图片,分享到外站时此项不能为空
	 */
	/*<code>
	    $extras['image'] =  "http://imgtest-lx.meilishuo.net/css/images/group/xxy1.gif";
        $extras['title'] = '[多图]';
        $extras['url'] = "http://www.meilishuo.com/group/" . 370762 . '?frm=connectqplus';
        $extras['source'] = 1; 
        $content = '我刚刚在 @美丽说 创建了杂志社';	
		分享到qzone
		ShareHelper::sync(7580188, '', '', $offsite = 4, 0, $content, $access = array('access_token' =>'C77B688321C4B99A1F4314C0F56E4470', 'openId' => 'B310D52746854C14D0B713DE73A8F678'), $extras);
		分享到新浪微博
		ShareHelper::sync(3899328, '', '', $offsite = 3, 0, $content, $access = array('2.00apbqnC0Swx4Vd3b7bc1773makU5B'), $extras);
	  </code>
	*/
	static function sync($userId = 0, $syncType, $tid = 0, $offsite = 0, $medal_id = 0, $content = '', $access = array(), $extras = array()) {
        if ((empty($userId) && empty($access)) || (empty($tid) && empty($content))) {
            return FALSE;
        }   
        if (empty($content)) {
			$fields = array('twitter_htmlcontent', 'twitter_images_id');
        	$tObj = new Twitter($fields);
			$twitterInfo = $tObj->getTwitterByTids(array($tid));
			
            if(empty($twitterInfo[0])) {
                return FALSE;
            }   
            $twitterInfo = $twitterInfo[0];
            $twitter_content = $twitterInfo['twitter_htmlcontent'];
            $tmpArr = explode("<br/>", $twitter_content);
            $content = $tmpArr[0];
            $content = strip_tags($content);
            if ($syncType == 'group') {
                $content = $content . " " . $extras['groupUrl'];
            }   
            if (!empty($twitterInfo['twitter_images_id'])) {
            	$col = array('picid','n_pic_file','nwidth','nheight');
            	$pictureAssembler = new Picture($col);
            	$picInfo = $pictureAssembler->getPictureByPids(array($twitterInfo['twitter_images_id']));
            	$extras['image'] = $picInfo[0]['n_pic_file'];

				//$picFactory = new PictureFactory(array($twitterInfo['twitter_images_id']));
				//$picFactory->getPictureUrl('n');
				//$pictures = $pictureFactory->getPictures();
                //$extras['image'] = $pictures[$twitterInfo['twitter_images_id']]->n_pic_file;
            }   
        }   
        if ($offsite == 11) {
            $outSitesTokens = self::getOutSitesTokens($userId, 4); 
            $access = $outSitesTokens[4];
            $offsite = 11; 
            $outSitesTokens = array($offsite => $access);
        }   
        else {
            if (empty($access) || (!empty($access) && $offsite == 0)) {
                $outSitesTokens = self::getOutSitesTokens($userId, $offsite);
                if(empty($outSitesTokens)) {
                    return FALSE;
                }   
            }   
            else {
                $outSitesTokens = array($offsite => $access);
            }   
        }

		$image = isset($extras['image']) ? $extras['image'] : '';
		$gtid = $syncType == 'goods' ? $extras['tid'] : 0;
        $group_id = isset($extras['group_id']) ? $extras['group_id'] : 0;
        $request = array();
		$mClient = \Snake\Libs\Base\MultiClient::getClient(0);
        foreach($outSitesTokens as $outSites => $tokens) {
            switch($outSites) {
                case '1':
                    //TODO
                break;
                case '3':
                    if ($syncType == 'goods') {
                        //分享图片和杂志社分享到新浪微博概率调整到66%
                        /*$rand_num = rand(0, 3);
                        if ($rand_num == 0) {
                            return FALSE;
                        }*/
                        $wbcontent = self::getSignUrl($content, $gtid, 'weibo', $group_id);
                    }
                    /*elseif ($syncType == 'group') {
                    //分享图片和杂志社分享到新浪微博概率调整到66%
                        $rand_num = rand(0, 3);
                        if ($rand_num == 0) {
                            return FALSE;
                        }
                        $wbcontent = $content;
                    }*/
                    else {
                        $wbcontent = $content;
                    }
                    $akey = !empty($extras['akey']) ? $extras['akey'] : WB_AKEY;
                    $skey = !empty($extras['skey']) ? $extras['skey'] : WB_SKEY;

                    if (strpos($tokens[0], '2.00') === 0) {
                        $weiboRequest = array(
                            'multi_func' => 'user_share_offsite',
                            'method' => 'POST',
                            'type' => 'weibo2',
                            'access_token' => $tokens[0],
                            'wbcontent' => $wbcontent,
                            'image' => $image,
                            'akey' => $akey,
                            'skey' => $skey,
                            'self_id' => 0,
                            'user_id' => $userId,
                        );

                    }
                    else {
                        $weiboRequest = array(
                            'multi_func' => 'user_share_offsite',
                            'method' => 'POST',
                            'type' => 'weibo',
                            'oauth_token' => $tokens[0],
                            'oauth_token_secret' => $tokens[1],
                            'wbcontent' => $wbcontent,
                            'image' => $image,
                            'akey' => $akey,
                            'skey' => $skey,
                            'self_id' => 0,
                            'user_id' => $userId,
                        );
                    }
                    $request[] = $weiboRequest;

                break;
                case '4':
                    if ($syncType == 'goods') {
                        //分享图片和杂志社分享到新浪微博概率调整到66%
                        $rand_num = rand(0, 1);
                        if ($rand_num != 0) {
                            return FALSE;
                        }
                        $qzcontent = $content;//self::getSignUrl($content, $gtid, 'qzone', $group_id);
                        $extras['url'] = 'http://wap.meilishuo.com/share/' . $gtid . '?frm=huiliu_connectqzone';
                    }
                    elseif ($syncType == 'group') {
                        //分享图片和杂志社分享到新浪微博概率调整到66%
                        $rand_num = rand(0, 1);
                        if ($rand_num != 0) {
                            return FALSE;
                        }
                        //$extras['url'] = 'http://wap.meilishuo.com/share/' . $gtid . '?frm=huiliu_connectqzonerepin';
                        $qzcontent = $content;
                    }
                    else {
                        $qzcontent = $content;
                    }
                    if(!empty($extras['url'])) {
                        $url = $extras['url'];
                    }
                    else {
                        return FALSE;
                    }
                    $akey = !empty($extras['akey']) ? $extras['akey'] : QZONE_ID;
                    $skey = !empty($extras['skey']) ? $extras['skey'] : QZONE_KEY;
                    $comment = !empty($extras['comment']) ? $extras['comment'] : NULL;

                    $qzoneRequest = array(
                        'user_id' => $userId,
                        'multi_func' => 'user_share_offsite',
                        'method' => 'POST',
                        'type' => 'qzone',
                        'token' => $tokens['access_token'],
                        'open_id' => $tokens['openId'],
                        'qzcontent' => $qzcontent,
                        'url' => $url,
                        'image' => $image,
                        'akey' => $akey,
                        'skey' => $skey,
                        'comment' => $comment,
                        'self_id' => 0,
                    );

                    $request[] = $qzoneRequest;

                break;
                /*case '8':
                    if (!empty($extras['ip'])) {
                        $ip = $extras['ip'];
                    }
                    if (empty($ip)) {
                        $ip = rand(1,254) . "." . rand(1,254) . "." . rand(1,254) . "." . rand(1,254);
                    }
                    if ($syncType == 'goods') {
                        $tqcontent = self::getSignUrl($content, $gtid, 'tqq2', $group_id);
                    }
                    else {
                        $tqcontent = $content;
                    }
                    $akey = !empty($extras['akey']) ? $extras['akey'] : TX_AKEY;
                    $skey = !empty($extras['skey']) ? $extras['skey'] : TX_SKEY;

					if (empty($tokens[0]) || empty($tqcontent) || empty($ip)) {
						break;
					}
					if (empty($image)) {
						$image = 'empty';
					}

                    $tqqRequest = array(
                        'multi_func' => 'user_share_offsite',
                        'method' => 'POST',
                        'type' => 'tqq2',
                        'token' => $tokens[0],
                        'open_id' => $tokens[1],
                        'tqcontent' => $tqcontent,
                        'ip' => $ip,
                        'image' => $image,
                        'akey' => $akey,
                        'skey' => $skey,
                        'self_id' => 0,
						'user_id' => $userId,
                    );

                    $request[] = $tqqRequest;

                    break;*/
                case '11':
                    /*if ($syncType == 'goods') {
                        $qpluscontent = self::getSignUrl($content, $gtid, 'qplus');
                        $extras['url'] = 'http://wap.meilishuo.com/share/' . $gtid . '?frm=shareqplus';
                    }*/
                    $qpluscontent = $content;
                    if(!empty($extras['url'])) {
                        $url = $extras['url'];
                    }
                    else {
                        return FALSE;
                    }
                    if (!empty($extras['source'])) {
                        $source = $extras['source'];
                    }
                    else {
                        $source = 1;
                    }

                    $akey = !empty($extras['akey']) ? $extras['akey'] : QZONE_ID;
                    $title = isset($extras['title'])? $extras['title'] : NULL;
                    $skey = !empty($extras['skey']) ? $extras['skey'] : QZONE_KEY;

                    $qplusRequest = array(
                        'multi_func' => 'user_share_offsite',
                        'method' => 'POST',
                        'type' => 'qplus',
                        'token' => $tokens['access_token'],
                        'open_id' => $tokens['openId'],
                        'title' => $title,
                        'url' => $url,
                        'image' => $image,
                        'akey' => $akey,
                        'skey' => $skey,
                        'comment' => $qpluscontent,
                        'source' => $source,
                        'self_id' => 0,
                    );
					$request[] = $qplusRequest;
                    break;
                }
        }
        $mClient->router($request);
        return TRUE;
	}

    private static function getSignUrl($content, $gtid, $type, $group_id) {
        $rand = rand(1, 4);
        switch($rand) {
            case 1:
                $result = $content . " 美丽说看到的>>" . "http://wap.meilishuo.com/share/" . $gtid . "?frm=connect" . $type;
                break;
            case 2:
                $result = "逛美丽说时看到的，" . $content . " 你们觉得怎么样？地址在这里>> http://wap.meilishuo.com/share/" . $gtid . "?frm=connect" . $type;
                break;
            case 3:
                $result =  $content . " 喜欢，地址在这里>> http://wap.meilishuo.com/share/" . $gtid . "?frm=connect" . $type;
                break;
            case 4:
                $result = $content . ">> http://wap.meilishuo.com/share/" . $gtid . "?frm=connect" . $type;
                break;
            default:
                $result = $content . "http://wap.meilishuo.com/share/" . $gtid . "?frm=connectweibo" . $type;
        }
        if (!empty($group_id)) {
            $time = time();
            $result = $content . ">> http://wap.meilishuo.com/group/" . $group_id . "?frm=huiliu_connect" . $type . '&time=' . $time;
        }
        else {
            $result = $content . ">> http://wap.meilishuo.com/group?frm=huiliu_connect" . $type;
        }

        return $result;
    }

    static function getOutSitesTokens($uid, $offsite = 0) {
        $tokens = array();
        $outSites = array();
        if ($offsite == 0) {
            $outSites = array(1, 3, 4, 8);
        }
        else {
            $outSites = array($offsite);
        }
        foreach($outSites as $outSite){
            $outTokens = self::getEachTokens($uid, $outSite);
            if (!empty($outTokens)) {
                $tokens[$outSite] = $outTokens;
            }
        }
        return $tokens;
    }

    static function getEachTokens($uid, $outSide) {
        $tokens = array();
        switch($outSide) {
            case '1':
                $renrenTokens = RedisUserConnectHelper::getUserToken('renren', $uid);
                if (!empty($renrenTokens)) {
                    $tokens = explode(',', $renrenTokens);
                }
            break;
            case '3':
                $weiboTokens = RedisUserConnectHelper::getUserToken('weibo', $uid);
                if (!empty($weiboTokens)) {
                    $tokens = explode(',', $weiboTokens);
                }
            break;
            case '4':
                $qzoneToken = RedisUserConnectHelper::getUserToken('qzone', $uid);
                $qzoneId = RedisUserConnectHelper::getUserAuth('qzone', $uid);
                if (!empty($qzoneToken) && !empty($qzoneId)) {
                    $tokens['access_token'] = $qzoneToken;
                    $tokens['openId'] = $qzoneId;
                }
            break;
            case '8':
                $txweiboTokens = RedisUserConnectHelper::getUserToken('txweibo', $uid);
                if (!empty($txweiboTokens)) {
                    $tokens = explode(',', $txweiboTokens);
                }
            break;
        }
        return $tokens;
    }

	//qzone share, base on qzone oauth 2
	static function qzoneShare($user_id, $access_token, $openid, $content, $url, $image = NULL, $option = array('akey' => QZONE_ID, 'skey' => QZONE_KEY), $comment = NULL) {
        $qc = new QzoneClient($option['akey'], $option['skey'], $access_token, $openid);

        if (empty($qc)) {
            return FALSE;
        }
        $accesslog = new \Snake\Libs\Base\SnakeLog('access_qzone', 'normal');
        $msg = "[" . $user_id . "]\t[" . $url . "]\t[" . $access_token . "]\t[" . $openid . "]\t[" . $option['akey'] . "]\t[" . $option['skey'] ."]";
        $accesslog->w_log($msg);
		if (empty($image)) {
			$ret = $qc->add_topic($comment);
		}
		else {
			$ret = $qc->add_share($content, $url, $comment, NULL, $image);
		}
        if ('ok' != $ret['msg']) {
            $logHandle = new \Snake\Libs\Base\SnakeLog('error_qzone', 'normal');
            $msg = $msg . "\t[" . json_encode($ret) . "]";
            $logHandle->w_log($msg);
        }

    }

    static function qplusShare($access_token, $openid, $title, $url, $image = NULL, $option = array('akey' => QZONE_ID, 'skey' => QZONE_KEY), $comment, $source, $user_id) {
        $qc = new QzoneClient($option['akey'], $option['skey'], $access_token, $openid);

        $logHandle = new \Snake\Libs\Base\SnakeLog('qplusShare', 'normal');
        $msg = "[" . $user_id . "]\t[" . $url . "]\t[" . $openid . "]";
        $logHandle->w_log($msg);

        if (empty($qc)) {
            $logHandle->w_log('empty qplusFeeds');
            return FALSE;
        }

        $ret = $qc->add_feeds($title, $url, NULL, $comment, NULL, $image, $source, 4);
        if ($ret['retcode'] > 0) {
            $errorLog = new \Snake\Libs\Base\SnakeLog('error_qplus', 'normal');
            $msg = $msg . "\t[" . json_encode($ret) . "]";
            $errorLog->w_log($msg);
        }
    }

    //weibo share, base on oauth 1 and 2
    static function weiboShare($params = array(), $content, $image, $option = array('akey' => WB_AKEY, 'skey' => WB_SKEY), $user_id) {
        if (isset($params['access_token'])){
            $wc = new SaeTClient($option['akey'], $option['skey'], $params['access_token']);
        }
        if (empty($wc)) {
            return FALSE;
        }
        $accesslog = new \Snake\Libs\Base\SnakeLog('access_weibo', 'normal');
        $token = $params['access_token'];
        $msg = "[" . $user_id . "]\t[" . $content . "]\t[" . $token . "]";
        $accesslog->w_log($msg);
        if(empty($image)) {
            $ret = $wc->update($content);
        }
        else {
            $ret = $wc->upload($content, $image);
        }
        if (!empty($ret['error'])) {
            $logHandle = new \Snake\Libs\Base\SnakeLog('error_weibo', 'normal');
            $msg = $msg . "\t[" . json_encode($ret) . "]";
            $logHandle->w_log($msg);
        }
    }

	//TODO Task需要增加支持txweibo2.0的脚本
    //txweibo share, base on oauth 2
	/**
 	 * $param $ip required paramters
	 */
    static function txweiboShare($access_token, $openId, $content, $ip, $image, $option = array('akey' => TX_AKEY , 'skey' =>TX_SKEY)) {
        TX_OAuth2::init(TX_AKEY, TX_SKEY, $ip);

		if (!empty($image) || $image !== 'empty') {
			$params = array();
            $imageContent = file_get_contents($image);
			$params['pic'] = $imageContent;
			$params['content'] = $content;
        	$ret = TX_OAuth2::api('t/add_pic', $params, 'GET', FALSE, $txweibo_access_token, $openId);
		}
		else {
			$params = array();
			$params['content'] = $content;
        	$ret = TX_OAuth2::api('t/add', $params, 'GET', FALSE, $txweibo_access_token, $openId);
		}
        $ret = (array)json_decode($ret);

        if (!empty($ret['errcode'])) {
            $logHandle = new \Snake\Libs\Base\SnakeLog('txweiboShare', 'normal');
            $logHandle->w_log($content);
            $logHandle->w_log(print_r($ret, TRUE));
        }
    }
}
