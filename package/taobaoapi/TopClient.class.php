<?php
namespace Snake\Package\TaobaoApi;
/*
 * 淘宝客
 */
class TopClient{

	////淘宝api地址
	public $taobaoUrl = TAOBAO_URL;

	//填写自己申请的AppKey
	public $taobaoAppkey = TAOBAO_APPKEY1;

	//填写自己申请的$appSecret
	public $taobaoAppsecret = TAOBAO_APPSECRET1;

	//淘宝客taobao.taobaoke.report.get接口用到的top_session 有效期1年，2012－03－05
	public $topSession = TAOBAO_TOPSESSION;

	//传递给淘宝的所有参数集合
	private $paramArr = array();

	//继承自TaobaoApi的类
	private $req = NULL;


	/**
	 *
	 * @param unknown_type $options
	 * @return TopClient 
	 */
    public function __construct() {
	}

	private function createSign() {
	   // global $appSecret;
	    $sign = $this->taobaoAppsecret;
	    ksort($this->paramArr);
	    foreach ($this->paramArr as $key => $val) {
	       if ($key != '' && $val != '') {
	           $sign .= $key . $val;
	       }
	    }
	    $sign = strtoupper(md5($sign));  //Hmac方式
	//    $sign = strtoupper(md5($sign.$appSecret)); //Md5方式
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
	    return $strParam;
	}

	//解析xml函数
	private function getXmlData($strXml) {
		$pos = strpos($strXml, 'xml');
		if ($pos) {
			$xmlCode = simplexml_load_string($strXml,'SimpleXMLElement', LIBXML_NOCDATA);
			$arrayCode = \Snake\Libs\Base\Utilities::objectToArray($xmlCode);
			return $arrayCode ;
		} else {
			return '';
		}
	}

	//发送请求得到淘宝的数据
	private function getTaobaoXmlData( ){
		//生成签名
		$sign = $this->createSign( );

		//组织参数
		$strParam  = $this->createStrParam();
		$strParam .= 'sign=' . $sign;

		//访问服务
		$url    = $this->taobaoUrl.$strParam;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		$result = curl_exec($ch);
		//$result = file_get_contents($url); 因为cpu 100％问题被去掉
        if ($result === FALSE)
            return '';
		$result = $this->getXmlData($result);
		return $result;
	}

	public function execute(TaobaoApi $req) {
		//参数数组
		$this->paramArr = array_merge(array(
		    'api_key' => $this->taobaoAppkey,
		    'method' => $req->getMethod(),//'taobao.taobaoke.shops.convert',
		    'format' => 'xml',
		    'v' => '2.0',
		    'timestamp' => date('Y-m-d H:i:s'),
		    'fields' => $req->getFields(),//'user_id,shop_title,click_url,commission_rate',
			'sign_method'=>'HmacMD5', //选择md5方式的时候,这行注释掉
			//'sign_method'=>'md5', //选择Hmac方式的时候,这行注释掉
		), $req->getParamArr());

		$result = $this->getTaobaoXmlData();
		return $result;
	}

}

