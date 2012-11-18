<?php
namespace Snake\Package\YoudaoApi;

class YihaodianClient {
	
	/**
	 * yihaodian api 请求url
	 * @var string
	 */
	public $yihaodianUrl = YIHAODIAN_URL;

	/**
	 * tracker_u 网盟标示
	 * @var int
	 * @access protected
	 */
	protected $trackerU = YIHAODIAN_TRACKER_U;

	/**
	 * secret key
	 */
	public $yihaodianSecretKey = NULL;

	private $paramArr = array();

	public function __construct() {
	}

	private function createSign() {
		$sign = '';
	    ksort($this->paramArr);
	    foreach ($this->paramArr as $key => $val) {
	       if ($key != '' && $val != '') {
	           $sign .= $key . $val;
	       }
	    }
	    $sign .= $this->yihaodianSecretKey;
	    $sign = strtoupper(md5($sign)); //Md5方式
	    return $sign;
	}

	//组参函数
	private function createStrParam () {
	    $strParam = '';
	    foreach ($this->paramArr as $key => $val) {
	       if ($key != '' && $val !='') {
	           $strParam .= $key.'='.urlencode($val).'&';
	       }
	    }
		//生成签名
		$sign = $this->createSign();
		$strParam .= 'sign=' . $sign;
	    return $strParam;
	}

	//发送请求得到淘宝的数据
	private function getTaobaoJsonData(){

		//组织参数
		$strParam  = $this->createStrParam();

		//访问服务
		$url    = $this->yihaodianUrl . $strParam;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		//$result = file_get_contents($url); 因为cpu 100％问题被去掉
        if ($result === FALSE)
            return '';
		$result = $this->getJsonData($result);
		return $result;
	}

	public function execute(YihaodianApi $req) {
		//参数数组
		$this->paramArr = array_merge(array(
		    'method' => $req->getMethod(),//'taobao.taobaoke.shops.convert',
		    'format' => 'json',
		    'v' => '1.0',
			'type' => 0,
			'tracker_u' => $this->trackerU,
		    'time' => time(),
		), $req->getParamArr());

		$result = $this->getTaobaoJsonData();
		return $result;
	}

	private function getJsonData($json) {
		return $json;	
	}
}
