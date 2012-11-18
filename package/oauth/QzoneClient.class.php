<?php

namespace Snake\Package\Oauth;

use \Snake\Package\Oauth\QzoneOauth AS QzoneOauth;
/**
 * @ignore
 */

class QzoneClient
{
	public $source = 'client';
	function __construct( $akey , $skey , $access_token , $openId)
	{
		$this->oauth = new QzoneOauth($akey, $skey, $access_token, $openId);
	}
	
	function get_user_info($format = 'json')
	{
		$params = array();
		$params['format'] = $format;
		return $this->oauth->get('https://graph.qq.com/user/get_user_info', $params, $this->source);
	}

	function add_share($title = NULL, $url = NULL, $comment = NULL, $summary = NULL, $images = NULL, $format = 'json', $source = '1', $type = NULL, $playurl = NULL)
	{
		if(empty($title) || empty($url)) {
			return FALSE;
		}
		$params = array();
		$params['title'] = $title;
		$params['url'] = $url;
		$params['comment'] = $comment;
		$params['summary'] = $summary;
		$params['images'] = $images;
		$params['format'] = $format;
		$params['source'] = $source;
		$params['type'] = $type;
		$params['playurl'] = $playurl;
		return $this->oauth->post('https://graph.qq.com/share/add_share', $params, FALSE, $this->source);
	}
	
	function notify_tasksys($opid = 1000) {
		$params = array();
		$params['opid'] = $opid;
		return $this->oauth->post('https://graph.qq.com/tasksys/notify_tasksys', $params, FALSE, $this->source);
	}

	function add_topic($con = NULL, $richtype = NULL, $richval = NULL, $format = 'json', $third_source = '1')
	{
		if(empty($con)) {
			return FALSE;
		}
		$params = array();
		if(!empty($richtype) && !empty($richval)){
			$params['richtype'] = $richtype;
			$params['richval'] = $richval;
		}
		$params['con'] = $con;
		$params['format'] = $format;
		$params['third_source'] = $third_source;
		
		return $this->oauth->post('https://graph.qq.com/shuoshuo/add_topic', $params, FALSE, $this->source);
	}
	
	function check_page_fans($page_id = '1379986183') {
		$params = array();
		$params['page_id'] = $page_id;
		$params['format'] = 'json';
		return $this->oauth->get('https://graph.qq.com/user/check_page_fans', $params, $this->source);
	}

	function add_t($content = NULL, $clientip = NULL, $format = 'json', $syncflag = 0) {
		if(empty($content)) {
			return FALSE;
		}
		$params = array();
		$params['content'] = $content;
		$params['format'] = 'json';
		return $this->oauth->post('https://graph.qq.com/t/add_t', $params, FALSE, $this->source);
	}
	function add_idol($name = NULL, $format = 'json') {
		if(empty($name)) {
			return FALSE;
		}
		$params = array();
		$params['name'] = $name;
		$params['format'] = 'json';
		return $this->oauth->post('https://graph.qq.com/relation/add_idol', $params, FALSE, $this->source);
	}
	function get_info($format = 'json') {
		$params = array();
		$params['format'] = 'json';
		return $this->oauth->get('https://graph.qq.com/user/get_user_info', $params, $this->source);
	}
	function get_fanslist($reqnum = 30, $startindex = 0, $mode = 0, $format = 'json') {
		$params = array();
		$params['reqnum'] = $reqnum;
		$params['startindex'] = $startindex;
		$params['mode'] = $mode;
		$params['format'] = 'json';
		return $this->oauth->get('https://graph.qq.com/relation/get_fanslist', $params, $this->source);
	}
	
	/** 
    *向Q+后台发布feeds信息
    *@param string $title
    *@param string $url
    *@param string $metatitle feeds标题带链接部分
    *@param string $comment 用户评论内容 最长40个中文字
    *@param string $summary 享的网页资源的摘要内容 最长80中文字
    *@param string $image 所分享的网页资源的代表性图片链接
    *@param string $source 取值说明：1.通过网页 2.通过应用 3.通过Q+
    *@param string $type 4表示网页；5表示视频（type=5时，必须传入playurl）。
    *@param string $playurl 长度限制为256字节。仅在type=5的时候有效
    */
	public function add_feeds($title, $url, $metatitle = NULL, $comment = NULL, $summary = NULL, $images = NULL, $fsource = 1, $type = 4, $playurl = NULL)
    {   
        $params = array();
        //增加9个参数       
        //$params['app_id'] = $this->app_id;
        //$params['app_token'] = $this->app_token;
        $params['title'] = $title;
        $params['url'] = $url;
        $params['metatitle'] = $metatitle;
        $params['comment'] = $comment;
        $params['summary'] = $summary;
        $params['images'] = $images;
        $params['source'] = $fsource;
        $params['type'] = $type;
        $params['playurl'] = $playurl;
		$params['format'] = 'json';
		return $this->oauth->post('https://cgi.qplus.com/openapi/share/add_feeds', $params, FALSE, 'qplus');
	}
}
