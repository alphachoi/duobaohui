<?php
namespace Snake\Package\Oauth;

use \Snake\Package\Oauth\DouBanClient;

class DouBanPublic
{
	const SERVER_URL = 'http://api.douban.com';
	protected $_APIKey = NULL;
	protected $_client = NULL;
	protected $_format = 'json';
	
	/*
	 * 构造函数
	 * @param string $apiKey 豆瓣开放平台APP KEY
	 * @param string $secret 豆瓣开放平台APP SECRET
	 *
	 * */
	public function __construct($apiKey = NULL, $secret = NULL) {
		$this->_client = new DouBanClient($apiKey, $secret);
		$this->_APIKey = $apiKey;
    }
	
	/*
	 * 请求用户授权Request Token,获取认证的URL
	 * @param string $key
	 * @param string $secret 
	 * @param string $callback 认证成功后跳转的URL
	 *
	 * @return string
	 * */
	public function getAuthorizationURL($key = NULL, $secret = NULL, $callback = NULL) {
		return $this->_client->getAuthorizationUrl($key, $secret, $callback);
	}
	
	/*
	 * 获取未授权的Request Token
	 *
	 * @return string
	 * */
	public function getRequestToken() {
        return $this->_client->getRequestToken();
    }

	/*
	 * 使用授权后的Request Token 换取 Access Token
	 * @param string $key 
	 * @param string $secret 
	 * 
     * @return array array("oauth_token" => the access token,
	 *                "oauth_token_secret" => the access secret,
	 *				  "douban_user_id" => user_id 
	 *                )
	 * */
	public function getAccessToken($key = NULL, $secret = NULL) {
        return $this->_client->getAccessToken($key, $secret);
    }

	/*
	 * 获取认证用户信息
	 * @param string $peopleId 用户编号
	 *
	 * @return array
	 * */
	public function getPeopleInfo($peopleId) {
		$url = self::SERVER_URL . '/people/' . $peopleId . '?alt=' . $this->_format . '&apikey=' . $this->_APIKey;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec($ch);
		curl_close($ch);
		/*把json转换为数组*/
		$result = json_decode($result, TRUE);
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

	public function programmaticLogin($tokenKey = NULL, $tokenSecret = NULL) {
		return $this->_client->login($tokenKey, $tokenSecret);
	}
	
	/*
	public function getEntry($url = NULL, $className = NULL) {
		$authHeaderArr = $this->_client->getAuthHeader('GET', $url);
		$authHeader = $authHeaderArr[0];
		$headerStr = $authHeaderArr[1];
		$this->_client->clearHeaders();
		if ($authHeader) {
			$this->_httpClient->setHeaders($authHeader);
		} 
	    else if ($this->_APIKey) {
			$param = 'apikey=' . urlencode($this->_APIKey);
			if (stristr($url, '?')) {
				$url = $url . '&' . $param;
			} else {
				$url = $url . '?' . $param;
			}
		}
		return parent::getEntry($url, $className);
	}
	
    public function getFeed($url = NULL, $className = NULL)
	{
		$authHeaderArr = $this->_client->getAuthHeader('GET', $url);
		$authHeader = $authHeaderArr[0];
		$headerStr = $authHeaderArr[1];
		$this->_client->clearHeaders();
		if ($authHeader) {
			$this->_httpClient->setHeaders($authHeader);
		} 
	        else if ($this->_APIKey) {
			$param = 'apikey=' . urlencode($this->_APIKey);
			if (stristr($url, '?')) {
				$url = $url . '&' . $param;
			} else {
				$url = $url . '?' . $param;
			}
		}
		return parent::getFeed($url, $className);
	}
	
	public function post($data, $uri = null, $remainingRedirects = null, 
			$contentType = null, $extraHeaders = null) 
	{
		if ($extraHeaders == NULL) {
			$extraHeaders = array();
		}
		$HeadersArr = $this->_client->getAuthHeader('POST', $uri);
		$Headers = $HeadersArr[0];
		$tmp = array();
		$tmp = array_merge($Headers, $extraHeaders);
		$extraHeaders = $tmp;
		return parent::post($data, $uri, $remainingRedirects, 
			$contentType, $extraHeaders);
	}
	
	public function put($data, $url = NULL, $remainingRedirects = null, 
			$contentType = null, $extraHeaders = null)
	{
		 if ($extraHeaders == NULL) {
                        $extraHeaders = array();
                }
                $HeadersArr = $this->_client->getAuthHeader('PUT', $url);
                $Headers = $HeadersArr[0];
                $tmp = array();
                $tmp = array_merge($Headers, $extraHeaders);
                $extraHeaders = $tmp;
		$this->_httpClient->setHeaders($extraHeaders);
		return parent::put($data, $url, $remainingRedirects, 
			$contentType, $extraHeaders);
	}
	
	public  function delete($url)
	{
		$extraHeadersArr = $this->_client->getAuthHeader('DELETE', $url);
                $extraHeaders = $extraHeadersArr[0];
                $headerStr = $extraHeadersArr[1];
		if (stristr($url, '?')) {
			$url = $url . '&' . $headerStr;
		} else {
			$url = $url . '?' . $headerStr;
		}
		$this->_httpClient->setHeaders($extraHeaders);
		$response = parent::delete($url);

	}

	//people	
	public function getAuthorizedUid()
	{
		$url = self::SERVER_URL . "/people/" . urlencode("@me");
		return $this->getEntry($url, 'Zend_Gdata_DouBan_PeopleEntry');

	}
	
	public function getFriends($uid = NULL)
	{
		if ($uid !== NULL) {
			$url = self::SERVER_URL . "/people/" . $uid . "/friends";
		}
		return $this->getFeed($url, 'Zend_Gdata_DouBan_PeopleFeed');
	}
	
	public function getContacts($uid = NULL)
	{
		if ($uid !== NULL) {
			$url = self::SERVER_URL . "/people/" . $uid . "/contacts";
		}
		return $this->getFeed($url, 'Zend_Gdata_DouBan_PeopleFeed');
	}

	public function getPeople($peopleId = NULL, $location = NULL)
	{
		if ($peopleId !== NULL) {
			$url = self::SERVER_URL . "/people/" . $peopleId;
		} else if ($location instanceof Zend_Gdata_Query) {
			$url = $location->getQueryUrl();
		} else {
			$url = $location;
		}
		return $this->getEntry($url, 'Zend_Gdata_DouBan_PeopleEntry');
	}

	public function getPeopleFeed($location = NULL)
	{
		if ($location == NULL) {
			$url = self::SERVER_URI . "/people";
		} else if ($location instanceof Zend_Gdata_Query) {
			$url = $location->getQueryUrl();
		} else {
			$url = $location;
		}
		return $this->getFeed($url, 'Zend_Gdata_DouBan_PeopleFeed');
	}
	
	public function searchPeople($queryText, $startIndex = NULL, $maxResults = NULL)
	{
		$query =new Zend_Gdata_Query(self::SERVER_URL . "/people/");
		$query->setQuery($queryText);
		$query->setMaxResults($maxResults);
		$query->setStartIndex($startIndex);
		return $this->getPeopleFeed($query);
		
	}*/
}
?>
