<?php
namespace Snake\Modules\app360;

Use Snake\Package\Goods\Attribute;
Use \Snake\Package\Manufactory\Poster360;
Use \Snake\Package\Goods\SearchGoodsEx;
Use Snake\Libs\Cache\Memcache;

class App360_poster extends \Snake\Libs\Controller{

	private $page = 0;
	private $frame = 0;
	private $word = "";	

	const maxFrame = FRAME_SIZE_MAX; 
	const pageSize = 40;

	private $hashMap = array(
		"圆领T恤"=>"圆领T恤",
		"V领T恤"=>"V领T恤",
		"吊带式T恤"=>"吊带T恤",
		"不对称长T恤"=>"不对称T恤",
		"打结T恤"=>"打结T恤",
		"露背背心"=>"露背背心",
		"夏日宽松背心"=>"宽松背心",
		"吊带衫"=>"吊带衫",
		"百褶雪纺背心"=>"百褶雪纺背心",
		"娃娃式罩衫"=>"娃娃衫",
		"衬衫"=>"衬衫",
		"工字背心"=>"工字背心",
		"波西米亚上衣"=>"雪纺衫",
		"抹胸上衣"=>"抹胸上衣",
		"抽绳蕾丝吊带"=>"收腰吊带",
		"吊带超短背心"=>"短背心",
		"束腰马甲"=>"抹胸",
		"不规则背心"=>"不规则背心",
		"直身连衣裙"=>"宽松连衣裙",
		"A字连衣裙"=>"A字连衣裙",
		"合身连衣裙"=>"包身连衣裙",
		"裹胸连衣裙"=>"裹胸连衣裙",
		"吊带露背连衣裙"=>"挂脖连衣裙",
		"开叉吊带连衣裙"=>"吊带连衣裙",
		"蓬撑连衣裙"=>"蓬蓬连衣裙",
		"吊带裙"=>"吊带连衣裙",
		"衬衫裙"=>"衬衫裙",
		"A字裙"=>"A字裙",
		"铅笔裙"=>"铅笔裙",
		"宽摆裙"=>"短裙",
		"雪纺裙"=>"雪纺裙",
		"花边荷叶裙"=>"荷叶裙",
		"百褶裙"=>"百褶裙",
		"网纱裙"=>"网纱裙",
		"短裤"=>"短裤",
		"裙裤"=>"裙裤",
		"七分裤"=>"七分裤",
		"牛仔短裤"=>"牛仔短裤",
		"连体裤"=>"连体裤",
		"小腿裤"=>"小脚裤",
		"宽腿裤"=>"阔腿裤",
		"打褶裤"=>"宽腿裤",
		"打底裤"=>"打底裤",
		"彩色丹宁裤"=>"彩色牛仔裤",
		"平底凉鞋"=>"平底凉鞋",
		"踝带鞋"=>"一字扣",
		"麻边厚底系带鞋"=>"草编凉鞋",
		"红底鞋"=>"红底鞋",
		"T字高跟鞋"=>"T字鞋",
		"芭蕾平底鞋"=>"芭蕾舞鞋",
		"夹脚鞋"=>"人字拖",
		"玛丽珍鞋"=>"玛丽珍鞋",
		"牛津鞋"=>"牛津鞋",
		"马克森软鞋"=>"豆豆鞋",
		"帆船鞋"=>"帆船鞋",
		"坡跟"=>"坡跟鞋",
		"鱼嘴鞋"=>"鱼嘴",
		"中跟"=>"中跟",
		"粗跟"=>"粗跟",
		"细高跟"=>"细高跟",
		"尖头鞋"=>"尖头",
		"链条包"=>"链条包",
		"圆筒包"=>"枕头包",
		"保龄球包"=>"保龄球包",
		"剑桥包"=>"剑桥包",
		"托特包"=>"购物包",
		"乞丐包"=>"水饺包",
		"邮差包"=>"邮差包",
		"马鞍包"=>"马鞍包",
		"手抓包"=>"晚宴包",
		"手拿包"=>"手拿包",
		"宽沿帽"=>"宽沿帽",
		"宽腰带"=>"宽腰带",
		"夸张配饰"=>"夸张",
		"太阳镜"=>"太阳镜",
		"三角头巾"=>"头巾"
	);

	public function run() {
		if (!$this->_init()) {
			return FALSE;
		}
		//$this->offset = $this->frame * self::pageSize; 
		$tidsAndNumData = $this->getTidsAndTotalNum();
		$tids = $tidsAndNumData['tids'];
		$totalNum = $tidsAndNumData['totalNum'];

		if (empty($tids)) {
			if (empty($responsePosterData)) {
				$responsePosterData = array('tInfo' => array(), 'totalNum' => 0);
			}
			$this->view = $responsePosterData;
			return TRUE;
		}

		$posterObj = new Poster360();
		$posterObj->setVariables($tids, 0);
		$posterObj->abtestPic(130);
		$poster	= $posterObj->getPoster();

		$responsePosterData = array('tInfo' => $poster, 'totalNum' => $totalNum);

		/*if (empty($this->userId) && !empty($responsePosterData['tInfo']) && (empty($this->inTest) || $this->testUseCache)) {
			$cacheHelper->set($cacheKeyForPosters, $responsePosterData, 600);
		}*/

		$this->view = $responsePosterData;
		return TRUE;
	} 

	private function getTidsAndTotalNum() {
		$attributeObj = new Attribute();
		$searchName = $this->word;
		if (isset($this->hashMap[$this->word])) {
			$searchName = $this->hashMap[$this->word];
		}
		$searchHelper = new SearchGoodsEx();
		$searchHelper->setWordName($searchName);
		$searchHelper->setOffset($this->frame);
		$searchHelper->setPageSize(self::pageSize);
		$searchRes = $searchHelper->search();
		$tids = \Snake\Libs\Base\Utilities::DataToArray($searchRes['matches'], 'attrs');
		$tids = \Snake\Libs\Base\Utilities::DataToArray($tids, 'twitter_id');
		$totalNum = min($searchRes['total'], $searchRes['total_found']);
		$data['tids'] = $tids;
		$data['totalNum'] = $totalNum;
		return $data;
	}

	private function _init() {
		if (!$this->setFrame()) {
			return FALSE;
		}
		if (!$this->setWord()) {
			return FALSE;	
		}
		/*if	(!$this->setPage()) {
			return FALSE;
		}*/
		return TRUE;
	}
	private function setWord() {
		$word = urldecode($this->request->REQUEST['name']);
		if (empty($word)) {
			$this->errorMessage(400, 'empty word');
			return FALSE;
		}
		$this->word = $word;
		return TRUE;
	}
	private function setFrame() {
		$frame = intval($this->request->REQUEST['frame']);
		if (!isset( $frame) || !is_numeric($frame)) {
			$this->errorMessage(400, 'bad frame');
			return FALSE;
		}
		/*if ($frame < 0 || $frame >= FRAME_SIZE_MAX) {
			$this->errorMessage(400, 'out of frame');
			return FALSE;
		}*/
		$this->frame = $frame;
		return TRUE;
	}

	private function setPage() {
		$page = intval($this->request->REQUEST['page']);
		if (!isset($page) || !is_numeric($page)) {
			$this->errorMessage(400, 'bad page');
			return FALSE;
		}
		if ($page < 0)  {
			$this->errorMessage(400, 'page is negative');
			return FALSE;
		}
		$this->page = $page;
		return TRUE;
	}
	
	private function errorMessage($code, $message) {
		self::setError(400, $code, $message);
		//$this->head  = 400;
		//$this->view  = array('code' => $code, 'message' => $message);
		return TRUE;
	}
}
