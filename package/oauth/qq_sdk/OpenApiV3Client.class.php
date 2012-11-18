<?php
namespace Snake\Package\Oauth\Qq_sdk;

Use \Snake\Package\Oauth\Qq_sdk\OpenApiV3;
 
/**
 * 提供访问腾讯开放平台 OpenApiV3 的接口
 */
class OpenApiV3Client
{
	/**
	 * 构造函数
	 *
	 * @param int $appid 应用的ID
	 * @param string $appkey 应用的密钥
	 */
	function __construct($appid, $appkey) {
		$this->oauth = new OpenApiV3($appid, $appkey);
	}
	
	/**
	 * 获取好友资料
	 *
	 * @param string $openid openid
	 * @param string $openkey openkey
	 * @param string $pf 平台 pf值以及对应的平台的列表包括但不仅限于如下：
	 qzone：空间；pengyou：朋友；qplus：Q+；tapp：微博；qqgame：QQGame；所要访问的平台, pf的其他取值参考wiki文档: http://wiki.open.qq.com/wiki/API3.0%E6%96%87%E6%A1%A3 
	 * @return array 好友资料数组
	 */
	function get_user_info($openid, $openkey, $pf) {
		$params = array(
			'openid' => $openid,
			'openkey' => $openkey,
			'pf' => $pf,
		);  
		
		$script_name = '/v3/user/get_info';

		return $this->oauth->api($script_name, $params);
	}

	/**
     * 批量获取多个用户的基本信息，包括昵称、头像等 每次最多可返回100个用户的信息
	 * 
	 * @param string $fopenids 需要获取数据的openid列表，中间以_隔开
	 * @param string $pf 平台
	 *
	 */
	function get_multi_info($openid, $openkey, $pf, $fopenids) {
		$params = array(
			'openid' => $openid,
			'openkey' => $openkey,
			'pf' => $pf,
			'fopenids' => $fopenids
		);  
		
		$script_name = '/v3/user/get_multi_info';

		return $this->oauth->api($script_name, $params);
	}

	/**
	 * 验证登录用户是否安装了应用
	 *
	 * @param string $openid openid
	 * @param string $openkey openkey
	 * @param string $pf 平台
	 * @return array 资料数组
	 */
	function is_setup($openid, $openkey, $pf) {
		$params = array(
			'openid' => $openid,
			'openkey' => $openkey,
			'pf' => $pf,
		);  
		
		$script_name = '/v3/user/is_setup';

		return $this->oauth->api($script_name, $params);
	}

	/**
     * 验证登录用户是否为某个认证空间的粉丝
	 * 
	 * @param string $pf 平台 'qzone'
	 * @param string $page_id 表示认证空间的QQ号码
	 *
	 */
	function is_fans($openid, $openkey, $pf, $page_id) {
		$params = array(
			'openid' => $openid,
			'openkey' => $openkey,
			'pf' => $pf,
			'page_id' => $page_id
		);  
		
		$script_name = '/v3/page/is_fans';

		return $this->oauth->api($script_name, $params);
	}

	/**
	 * 验证是否平台好友，即验证fopenid是否是openid的好友
	 *
	 * @param string $openid openid
	 * @param string $openkey openkey
	 * @param string $pf 平台
	 * @param string $charset gbk
	 * @param string $fopenid 待验证的好友QQ号码转化得到的ID
	 * @return array 数组
	 */
	function is_friend($openid, $openkey, $pf, $charset = 'gbk', $fopenid) {
		$params = array(
			'openid' => $openid,
			'openkey' => $openkey,
			'pf' => $pf,
			'charset' => $charset,
			'fopenid' => $fopenid,
		);  
		
		$script_name = '/v3/relation/is_friend';

		return $this->oauth->api($script_name, $params);
	}
}

