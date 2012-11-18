<?php

namespace Snake\Package\Oauth;

use \Snake\Package\Oauth\RenrenOauth AS RenrenOauth;
use \Snake\Package\Oauth\RenrenException;

class RenrenClient
{
	//sig url, give oauth the mark 'client'
	public $source = 'client';

	public $renren_host = 'http://api.renren.com/restserver.do';

	function __construct( $akey, $skey, $access_token, $refresh_token = NULL, $ip = NULL)
	{
		$this->oauth = new RenrenOauth( $akey, $skey, $ip, $access_token, $refresh_token );
	}

	public function get_user_info($uid = NULL, $fields = 'default', $v = 1, $method = 'users.getInfo') {
	//public function get_user_info($uid = NULL, $v = 1, $method = 'users.getInfo', $access_token) {
		$params = array();
		if ($fields == 'default') {
			$params['fields'] = 'uid,name,sex,mainurl';
		}
		if (!empty($uid)) {
			$params['uid'] = $uid;
		}
		$params['v'] = $v;
		$params['method'] = $method;

		return $this->oauth->post($this->renren_host, $params, FALSE, $this->source);
	}
	
	/* *
	* hasAppPermission
	* 检查用户是否授予应用扩展权限
	* @author yishu Liu
	* @param sig 签名认证。是用当次请求的所有参数计算出来的值。
	* @param method users.hasAppPermission
	* @param v API的版本号，固定值为1.0
	* @param ext_perm 用户可操作的扩展授权，例如email
	* @param api_key 申请应用时分配的api_key
	* @param session_key 当前用户的session_key
	* @param access_token OAuth2.0验证授权后获得的token。当传入此参数时，api_key和session_key可以不用传入。
	* @return result 0表示对当前的功能没有权限操作，1表示可以操作
	* */
	public function hasAppPermission($method = 'users.hasAppPermission', $v = 1, $ext_perm = NULL) {
		$params = array();
		if(!empty($ext_perm)){
			$params['ext_perm'] = $ext_perm;
		}
		$params['v'] = $v;
		$params['method'] = $method;
		return $this->oauth->post($this->renren_host, $params, FALSE, $this->source); 
	}

	//发布分享
	public function share($method = 'share.share', $v = 1, $type = 2, $url, $comment, $session_key) {
		$params = array();
		$params['v'] = $v;
		$params['type'] = $type;
		$params['method'] = $method;
		$params['url'] = $url;
		//$params['ugc_id'] = $ugc_id;
		//$params['user_id'] = $user_id;
		$params['comment'] = $comment;
		$params['session_key'] = $session_key;
		return $this->oauth->post($this->renren_host, $params, FALSE, $this->source); 
	}

	//给指定的用户发送通知。
	public function sendNotice($v = 1, $method = 'notifications.send', $to_ids, $notification, $type) {
		$params = array();
		$params['v'] = $v;
		$params['method'] = $method;
		$params['to_ids'] = $to_ids;
		$params['notification'] = $notification;
		$params['type'] = $type;
		return $this->oauth->post($this->renren_host, $params, FALSE, $this->source); 
	}

	//
	public function publishFeed($method = 'feed.publishFeed', $v =1, $name, $description, $url, $image, $pageId = '699103070',  $session_key) {
		$params = array();
		$params['v'] = $v;
		$params['name'] = $name;
		$params['method'] = $method;
		$params['url'] = $url;
		//$params['ugc_id'] = $ugc_id;
		//$params['user_id'] = $user_id;
		$params['image'] = $image;
		if (!empty($pageId)) {
			$params['page_id'] = $pageId;
		}
		$params['image'] = $image;
		$params['description'] = $description;
		$params['session_key'] = $session_key;
		return $this->oauth->post($this->renren_host, $params, FALSE, $this->source); 
	}

	//fetatus.setd dialog 是一个可以发送自定义新鲜事的widget
	public function feedDialog($url, $redirect_uri, $name, $description, $display, $caption, $image, $action_name, $action_link, $message) {
		$params = array();
		$params['url'] = $url;
		$params['redirect_uri'] = $redirect_uri;
		$params['name'] = $name;
		$params['description'] = $description;
		$params['display'] = $display;
		$params['caption'] = $caption;
		$params['image'] = $image;
		$params['action_name'] = $action_name;
		$params['action_link'] = $action_link;
		$params['message'] = $message;
		return $this->oauth->post($this->renren_host, $params, FALSE, $this->source); 
	}

	//用户更新状态 此API需要用户授予 status_update 权限(在OAuth2.0授权中由scope参数指定)。
	public function statusSet($method = 'status.set', $v =1, $status, $pageId = '699103070', $session_key) {
		$params = array();
		$params['method'] = $method;
		$params['v'] = $v;
		$params['status'] = $status;
		$params['session_key'] = $session_key;
		if (!empty($pageId)) {
			$params['page_id'] = $pageId;
		}
		return $this->oauth->post($this->renren_host, $params, FALSE, $this->source); 
	}

	//创建一篇日志
	public function addBlog($method = 'blog.addBlog', $v =1, $title, $content, $pageId = '699103070', $session_key) {
		$params = array();
		$params['method'] = $method;
		$params['v'] = $v;
		$params['title'] = $title;
		$params['content'] = $content;
		if (!empty($pageId)) {
			$params['page_id'] = $pageId;
		}
		$params['session_key'] = $session_key;
		return $this->oauth->post($this->renren_host, $params, FALSE, $this->source); 
	}

}
