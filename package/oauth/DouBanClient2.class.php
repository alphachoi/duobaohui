<?php
namespace Snake\Package\Oauth;

Use \Snake\Package\Oauth\DouBanOAuth2;
Use \Snake\libs\Cache\Memcache;

/**
 * 豆瓣操作类
 */
class DoubanClient2
{
	/**
	 * 构造函数
	 *
	 * @access public
	 * @param mixed $akey 微博开放平台应用APP KEY
	 * @param mixed $skey 微博开放平台应用APP SECRET
	 * @param mixed $access_token OAuth认证返回的token
	 * @param mixed $refresh_token OAuth认证返回的token secret
	 * @return void
	 */
	function __construct( $akey , $skey , $access_token , $refresh_token = NULL)
	{
		$this->oauth = new DouBanOAuth2( $akey , $skey , $access_token , $refresh_token );
	}

	function getCurrentPeopleInfo($user_id) {
		$param = array();
		$param['alt'] = 'json';
		$result = $this->oauth->get("http://api.douban.com/people/{$user_id}", $param);
        /*把json转换为数组*/
        $userInfo['id'] = substr($result['uri']['$t'], strripos($result['uri']['$t'], '/') + 1); 
        $userInfo['uid'] = $result['db:uid']['$t'];
        $userInfo['signature'] = empty($result['db:signature']['$t']) ? '' : $result['db:signature']['$t'];
        $userInfo['location'][0] = empty($result['db:location']['$t']) ? '' : $result['db:location']['$t'];
        $userInfo['location'][1] = empty($result['db:location']['@id']) ? '' : $result['db:location']['@id'];
        $userInfo['title'] = $result['title']['$t'];
        $userInfo['content'] = $result['content']['$t'];
        $userInfo['homepage'] = $result['link'][1]['@href'];
        $userInfo['image'] = str_replace('icon/u', 'icon/ul', $result['link'][2]['@href']);
        if (strpos($userInfo['image'], 'ulser_normal') !== FALSE) {
            $userInfo['image'] = 'http://img3.douban.com/mpic/o493916.jpg';
        }   
		return $userInfo;
	}
}

