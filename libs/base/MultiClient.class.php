<?php
namespace Snake\Libs\Base;
/*
 * author: jianxu@meilishuo.com
 * support: zuoyu@meilishuo.com, jianxu@meilishuo.com
 */
use \Snake\Libs\Thrift\Packages\ZooRequest;
use \Snake\Libs\Thrift\Transport\TFramedTransport;
use \Snake\Libs\PlatformService\TMlsSocket;
use \Snake\Libs\Thrift\Protocol\TBinaryProtocolAccelerated;
use \Snake\Libs\Thrift\Packages\BatchRpcServiceClient;
use \Snake\Libs\Thrift\Transport\TTransportException;

require_once($GLOBALS['THRIFT_ROOT'] . '/Thrift.php');
require_once($GLOBALS['THRIFT_ROOT'] . '/transport/TBufferedTransport.php');
require_once($GLOBALS['THRIFT_ROOT'] . '/transport/TFramedTransport.php');
require_once($GLOBALS['THRIFT_ROOT'] . '/protocol/TBinaryProtocol.php');
require_once($GLOBALS['THRIFT_ROOT'] . '/packages/batch_rpc/BatchRpcService.php');

class MultiClient {


	private static $client = NULL;
	private $thriftClient = NULL;

	public static function getClient($user_id = 0) {
		is_null(self::$client) && self::$client = new self($user_id);
		return self::$client;
	}
	
	private $user_id;
	private $host = IOHOST; 
	/**
	 * construction
	 **/
	private function __construct($user_id) {
		$this->user_id = $user_id;
	}

	function router($requests = array()) {
		
		if(empty($requests)) {
			return FALSE;
		}

		$multi_requests = array();

		foreach ($requests AS &$value) {
                        $func = $value['multi_func'];
			unset($value['multi_func']);
			$one_piece = call_user_func(array($this, $func), $value);
			if (isset($one_request['error'])) {
				die("{$func} error: {$one_request['message']}");
			}
			$multi_requests[] = $one_piece; 
		}
                $multi_responses = $this->batchHttpReq($multi_requests);
		$responses = array();
                if ($multi_responses === FALSE) {
                    return $responses;
                }
		foreach ($multi_responses AS $multi_response) {
			$text = json_decode($multi_response->text, TRUE);
			$responses[] = $text;
		}
		return $responses;
	}

	private function getThriftClient() {
		if(!is_null($this->thriftClient)){
			return $this->thriftClient;
		}
		$count = count($GLOBALS['BATCH_RPC_SERVICE']);
		$idxFirst = rand(0,$count - 1);


		$socket = new TMlsSocket($GLOBALS['BATCH_RPC_SERVICE'][$idxFirst]['host'],
								$GLOBALS['BATCH_RPC_SERVICE'][$idxFirst]['port']);
                

		$transport  = new TFramedTransport($socket);
		$protocol = new TBinaryProtocolAccelerated($transport);
		$thriftClient = new BatchRpcServiceClient($protocol);
		$socket->setSendTimeout($GLOBALS['BATCH_RPC_SERVICE'][$idxFirst]['timeout']);
		$socket->setRecvTimeout($GLOBALS['BATCH_RPC_SERVICE'][$idxFirst]['timeout']);
		try{
			$socket->open();
		}
		catch(Exception $e){
                    \Snake\Libs\Base\Utilities::MlscacheLog('ERROR',"act=getThriftClientFirst id=$idxFirst err=connect msg={$e->getMessage()}");
		}
    catch(\Snake\Libs\Thrift\TException $e) {
        \Snake\Libs\Base\Utilities::MlscacheLog('ERROR',"act=getThriftClientFirst id=$idxFirst err=connect msg={$e->getMessage()}");
    }
		if(!$socket->isOpen()){
			for($i = 0; $i < $count && $i < 2; ++ $i){
				if($i == $idxFirst){
					continue;
				}
				$socket = new TMlsSocket($GLOBALS['BATCH_RPC_SERVICE'][$i]['host'],
								$GLOBALS['BATCH_RPC_SERVICE'][$i]['port']);
		                $socket->setSendTimeout($GLOBALS['BATCH_RPC_SERVICE'][$i]['timeout']);
		                $socket->setRecvTimeout($GLOBALS['BATCH_RPC_SERVICE'][$i]['timeout']);
				try{
					$socket->open();
				}
				catch(Exception $e){
                                    \Snake\Libs\Base\Utilities::MlscacheLog('ERROR',"act=getThriftClient id=$i err=connect msg={$e->getMessage()}");
				}
        catch(\Snake\Libs\Thrift\TException $e) {
            \Snake\Libs\Base\Utilities::MlscacheLog('ERROR',"act=getThriftClientFirst id=$idxFirst err=connect msg={$e->getMessage()}");
        }
				if($socket->isOpen()){
					break;
				}
				
			}
			
		}
		if(!$socket->isOpen()){
			return NULL;
		}
		$this->thriftClient = $thriftClient;
		return $this->thriftClient;
	}
        /**
         * 向并发层发出多个http rpc调用请求.
         * @param $arrHttpRequest: array,每个array为new ZooRequest('method'=>'GET'|'POST'|'DELETE','url'=>'http://zoo.meilishuo.com/twitter/xxx?k=1&b=2,
         *                         'body'=>'post请求包体','headers'=>array('key1'=>'value1','key2'=>'value2')
         */
	public function batchHttpReq($arrHttpRequest){
		if(!is_array($arrHttpRequest) || empty($arrHttpRequest)){
			return  FALSE;
		}
		try{
			$thriftClient = $this->getThriftClient();
			if(is_null($thriftClient)){
				return FALSE;
			}
			$resp = $thriftClient->batchHttpReq($arrHttpRequest);
			return $resp;
		}
                catch(TTransportException $sock_exception) {
                    \Snake\Libs\Base\Utilities::MlscacheLog('ERROR',"act=batchHttpReq err={$sock_exception->getMessage()}");
                    return FALSE;
                }
		catch(Exception $e){
                    \Snake\Libs\Base\Utilities::MlscacheLog('ERROR',"act=batchHttpReq err={$e->getMessage()}");
		    return FALSE;
		}
	}

	function build_multi_request($url, $parameters, $upload = false) {
		$piece = array();
		$piece['body'] = '';
		$piece['headers']['MEILISHUO'] = 'UID:' . $parameters['self_id'];
		unset($parameters['self_id']);
		$piece['method'] = $parameters['method'];
		unset($parameters['method']);
		$piece['url'] = $url;
		if ('GET' == $piece['method']) {
			if (!empty($parameters)) {
				$piece['url'] = $url . '?' . http_build_query($parameters);
			}
			return new ZooRequest($piece);
		}
		else {
			$headers = array();
			if (!$upload && (is_array($parameters) || is_object($parameters)) ) {
				$piece['body'] = http_build_query($parameters);
			} 
			else {
				$piece['body'] = self::build_http_query_multi($parameters);
				$piece['headers']['Content-Type'] = " multipart/form-data; boundary=" . self::$boundary;
			}
			return new ZooRequest($piece);
		}
	}

	public static function build_http_query_multi($params) {
		if (!$params) return '';

		uksort($params, 'strcmp');

		$pairs = array();

		self::$boundary = $boundary = uniqid('------------------');
		$MPboundary = '--'.$boundary;
		$endMPboundary = $MPboundary. '--';
		$multipartbody = '';

		foreach ($params as $parameter => $value) {

			if( in_array($parameter, array('pic', 'image')) && $value{0} == '@' ) {
				$url = ltrim( $value, '@' );

				if ($stream = fopen($url, 'rb')) {
           			$content = stream_get_contents($stream);
           			fclose($stream);
        		}    				

				//$content = file_get_contents( $url );
				$array = explode( '?', basename( $url ) );
				$filename = $array[0];

				$multipartbody .= $MPboundary . "\r\n";
				$multipartbody .= 'Content-Disposition: form-data; name="' . $parameter . '"; filename="' . $filename . '"'. "\r\n";
				$multipartbody .= "Content-Type: image/unknown\r\n\r\n";
				$multipartbody .= $content. "\r\n";
			} else {
				$multipartbody .= $MPboundary . "\r\n";
				$multipartbody .= 'content-disposition: form-data; name="' . $parameter . "\"\r\n\r\n";
				$multipartbody .= $value."\r\n";
			}

		}

		$multipartbody .= $endMPboundary;
		return $multipartbody;
	}
	
	/////////////////////
	//twitter operation//
	/////////////////////

	//twitter_like (get, post, delete)
	public function twitter_like($params) {
		if (empty($params)) {
			$response['error'] = TRUE;
			$response['message'] = 'empty necessary parameters';
			return $response;
		}

		$url = $this->host . 'twitter/' . $twitter_id . '/likes';

		return self::build_multi_request($url, $params, FALSE);
	}
	
	//TODO 50%
	//twitter_stat single twitter (only post)
	public function twitter_stat_add($params) {
		if (empty($params) || empty($params['twitter_id'])) {
			$response['error'] = 'empty necessary parameters';
			return $response;
		}

		$twitter_id = $params['twitter_id'];
		$url = $this->host . 'twitter/' . $twitter_id . '/add_stat';

		return $this->connect->post($url, $params);
	}

	////////////////
	//twitter stat//
	////////////////

	//twitters_likes_count (post)
	public function twitters_stat($params) {
		if (empty($params)) {
			$response['error'] = TRUE;
			$response['message'] = 'empty necessary parameters';
			return $response;
		}

		$url = $this->host . 'twitters/twitter_stat';

		return self::build_multi_request($url, $params, FALSE);
	}

	public function twitter_likes_state($params) {
		if (empty($params)) {
			$response['error'] = TRUE;
			$response['message'] = 'empty necessary parameters';
			return $response;
		}

		$url = $this->host . 'twitters/likes_state';

		return self::build_multi_request($url, $params, FALSE);
	}

	//////////////////////////
	//one user like twitters//
	//////////////////////////

	//twitters_likes_count (get)
	public function user_likes_twitters($user_id, $params) {
		if (empty($params)) {
			$response['error'] = TRUE;
			$response['message'] = 'empty necessary parameters';
			return $response;
		}

		$url = $this->host . 'user/' . $user_id . '/likes/twitter';

		return self::build_multi_request($url, $params, FALSE);
	}

	///////////////
	//shareHelper//
	///////////////

	public function user_share_offsite($params) {
		if (empty($params)) {
			$response['error'] = TRUE;
			$response['message'] = 'empty necessay parameters';
			return $response;
		}

		$url = $this->host . 'user/share';

		return self::build_multi_request($url, $params, FALSE);
	}

	public function pop_group_twitter($params) {
		if(empty($params)) {
			$response['error'] = TRUE;
			$response['message'] = 'empty necessary parameters';
			return $response;
		}

		$url = $this->host . 'group/group_twitter_mix';

		return self::build_multi_request($url, $params, FALSE);
	}
}
