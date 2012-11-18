<?php
namespace Snake\Modules\Connect;

/**
 * @author yishuliu@meilishuo.com
 * 用户互联授权失败
 *
 **/

use \Snake\Package\Connect\ConnectFactory AS ConnectFactory;

class Fail extends \Snake\Libs\Controller {

    private $userId = NULL;
	private $type = NULL;
	private $refer = NULL;
	private $IP = NULL;
	private $requestParams = array();
	private $queryCookie = NULL;
	protected $outSites = array('renren', 'weibo', 'qzone', 'baidu', 'taobao', 'wangyi', 'txweibo', 'douban');
    
    public function run() {
        if (!$this->_init()) {
            return FALSE;
        }   
		$connectFactory = new ConnectFactory();
		$params = array();
		$params = $this->requestParams;
		$params['request'] = $this->request;
		$params['santorini_mm'] = $this->Santor;
		//$params['queryCookie'] = $this->queryCookie;
		//print_r($params); //['ip'] = $this->IP;
		$result = $connectFactory->LoginFail($this->type, $params);
		//print_r($result);
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
		$this->setSantorini();
		//$this->setFrm();
		$this->setIp();
        if (!$this->setType()) {
            return FALSE;
        }
        return TRUE;
    }

    private function setType() {
		$path = $this->request->path_args;
		$type = !empty($path[0]) ? $path[0] : '';
		if (!in_array($type, $this->outSites)) {
            $this->setError(400, 40502, 'outSites type is illegal');
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

	private function setIp() {
		$this->IP = isset($this->request->ip) ? $this->request->ip : '';
		return TRUE;
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
