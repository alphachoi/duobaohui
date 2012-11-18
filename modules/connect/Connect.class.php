<?php
namespace Snake\Modules\Connect;

/**
 * @package connect 用户互联登录
 * @author yishuliu@meilishuo.com
 * 用户互联成功后相关插入数据库 redis操作
 *
 **/

Use \Snake\Package\Connect\ConnectFactory AS ConnectFactory;

class Connect extends \Snake\Libs\Controller {

    private $userId = NULL;
	private $type = NULL;
	private $refer = NULL;
	private $Ip = NULL;
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
		$params['santorini_mm'] = $this->Santor;
		$params['request'] = $this->request;
		$params['frm'] = $this->frm;
		$result = $connectFactory->LoginSuccess($this->type, $this->userId, $params);
		if (!isset($result['destUrl']) && !empty($result['error'])) {
			$this->setError(400, 40901, $result['error']);
			return FALSE;
		}
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
		$this->setFrm();
		$this->setIp();
		$this->setSantorini();
        if (!$this->setType()) {
            return FALSE;
        }
        return TRUE;
    }

    private function setType() {
		$path = $this->request->path_args;
		$type = !empty($path[0]) ? $path[0] : '';
		if (!in_array($type, $this->outSites)) {
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
        $this->requestParams = $params;
        return TRUE;
    }    

    private function setQueryCookie() {
        $this->queryCookie = isset($this->request->COOKIE['MEILISHUO_QUERY']) ? $this->request->COOKIE['MEILISHUO_QUERY'] : "";
        return TRUE;
    }    

	private function setFrm() {
		$this->frm = isset($this->request->GET['r']) ? $this->request->GET['r'] : '';
		return TRUE;
	}

	private function setIp() {
		$this->Ip = isset($this->request->ip) ? $this->request->ip : '';
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
