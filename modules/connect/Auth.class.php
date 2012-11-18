<?php
namespace Snake\Modules\Connect;

/**
 * @package connect 互联接口
 * @author yishuliu@meilishuo.com
 * 用户互联授权获得request_token, 并用request_token换取access_token的过程
 *
 **/

use \Snake\Package\Connect\ConnectFactory AS ConnectFactory;

class Auth extends \Snake\Libs\Controller {

    private $userId = NULL;
	private $type = NULL;
	private $refer = NULL;
	private $Ip = NULL;
	private $state = NULL;
	private $requestParams = array();
	private $queryCookie = NULL;
	private $Santor = NULL;
	protected $outSites = array('renren', 'weibo', 'qzone', 'baidu', 'taobao', 'wangyi', 'txweibo', 'douban');
    
    public function run() {
		if (!$this->_init()) {
			return FALSE;
		}
		$connectFactory = new ConnectFactory();
		$params = array();
		$params = $this->requestParams;
		$params['queryCookie'] = $this->queryCookie;
		$params['ip'] = $this->Ip;
		$params['state'] = $this->state;
		$params['santorini_mm'] = $this->Santor;
		$params['request'] = $this->request;
		$params['baseUrl'] = $this->BaseUrl;
		$params['frm'] = isset($this->request->GET['r']) ? $this->request->GET['r'] : '';
		$result = $connectFactory->Auth($this->type, $params);
        //$logHelper = new \Snake\Libs\Base\SnakeLog('qzone_auth', 'normal');
        //$logHelper->w_log(print_r($params,TRUE));
        $this->view = $result;
		return TRUE;
    }   

	/*
     * 初始化变量
     */
    private function _init() {
		//$this->setRefer();
		$this->setRequestCode();
		$this->setQueryCookie();
		$this->setState();
		$this->setIp();
		$this->setSantorini();
		$this->setBaseUrl();
        if (!$this->setType()) {
            return FALSE;
        }   
        return TRUE;
    }

    private function setType() {
		$path = $this->request->path_args;
		$type = !empty($path[0]) ? $path[0] : '';
		if (!in_array($type, $this->outSites) || empty($type)) {
            $this->setError(400, 40501, 'outSites type is illegal');
            return FALSE;
        }   
        $this->type = $type;
        return TRUE;
    }    

    private function setRefer() {
        $this->refer = isset($this->request->refer) ? $this->request->refer : 0;
        return TRUE;
    }    

    private function setRequestCode() {
		$params = array();
		$request = $this->request->REQUEST;
		if (!empty($request)) {
			foreach ($request as $key => $value) {
				$params[$key] = $value;
			}
		}
		//print_r($request);die('$$');
        $this->requestParams = $params;
        return TRUE;
    }    

    private function setQueryCookie() {
        $this->queryCookie = isset($this->request->COOKIE['MEILISHUO_QUERY']) ? $this->request->COOKIE['MEILISHUO_QUERY'] : "";
        return TRUE;
    }    

	private function setFrm() {
		$this->frm = isset($this->request->GET['frm']) ? $this->request->GET['frm'] : '';
		return TRUE;
	}

	private function setState() {
		$this->state = isset($this->request->GET['state']) ? $this->request->GET['state'] : '';
		return TRUE;
	}

	private function setIp() {
		$this->Ip = isset($this->request->ip) ? $this->request->ip : '';
		return TRUE;
	}

    private function setBaseUrl() {
        $this->BaseUrl = isset($this->request->GET['baseUrl']) ? $this->request->GET['baseUrl'] : '';
    }

	private function setSantorini() {
		$this->Santor = isset($this->request->COOKIE['santorini_mm']) ? $this->request->COOKIE['santorini_mm'] : '';
		return TRUE;
	}

	private function errorMessage($code, $message) {
		self::setError(400, $code, $message);
        return TRUE;
    }   
}

