<?php
namespace Snake\Package\Goods;

class TestMoresimilar extends Abtest {

	const SYMBOL = "moresimilar";


	private $testTids = array(
		612066029, 624388192, 622945785, 611129607, 612976299,
		615973252, 617063389, 622245923, 611388907, 624318505,
		623164643, 616926495, 615996198, 617522287, 624120529,
		622697621, 623993057, 615350903, 627881449,
	);

	private $imgIndex = array(
		612066029=>array('pic' => "http://imgtest.meiliworks.com/pic/_o/eb/2b/0500b515f1b8b293c8e34ac1f342_200_354.jpg", 'w' => 200, 'h' => 354 ),
		624388192=>array('pic' => "http://imgtest.meiliworks.com/pic/_o/a5/1f/850def5b69b955c5b87a7a196a01_200_365.jpg", 'w' => 200, 'h' => 365 ),
		622945785=>array('pic' => "http://imgtest.meiliworks.com/pic/_o/57/f0/216a006efecd191accd543405a7b_200_352.jpg", 'w' => 200, 'h' => 352 ),
		611129607=>array('pic' => "http://imgtest.meiliworks.com/pic/_o/46/af/59e49cb2c2a286ab777b5396de10_200_353.jpg", 'w' => 200, 'h' => 353 ),
		612976299=>array('pic' => "http://imgtest.meiliworks.com/pic/_o/14/92/f2d1d04b74a80d9a97670bf5ea93_200_364.jpg", 'w' => 200, 'h' => 364 ),
		615973252=>array('pic' => "http://imgtest.meiliworks.com/pic/_o/9a/7c/f2f41aab17c67791e027666a5397_200_320.jpg", 'w' => 200, 'h' => 320 ),
		617063389=>array('pic' => "http://imgtest.meiliworks.com/pic/_o/ec/9b/34169cd4290561cbf2d1cf66bc30_200_352.jpg", 'w' => 200, 'h' => 352 ),
		622245923=>array('pic' => "http://imgtest.meiliworks.com/pic/_o/4e/6a/63446e6c35f61ff4babb8828e7b2_200_319.jpg", 'w' => 200, 'h' => 319 ),
		611388907=>array('pic' => "http://imgtest.meiliworks.com/pic/_o/f4/de/d17a7d853cb74576e5bafd1f7ef7_200_353.jpg", 'w' => 200, 'h' => 353 ),
		624318505=>array('pic' => "http://imgtest.meiliworks.com/pic/_o/1d/82/4f7e3d8c2ca6f53308d5f1b73ccd_200_353.jpg", 'w' => 200, 'h' => 353 ),
		623164643=>array('pic' => "http://imgtest.meiliworks.com/pic/_o/c8/2e/69804e98215272857f0a1d14c0fb_200_354.jpg", 'w' => 200, 'h' => 354 ),
		616926495=>array('pic' => "http://imgtest.meiliworks.com/pic/_o/c5/c4/2fb5318e6735ba6701d170d8dced_200_357.jpg", 'w' => 200, 'h' => 357 ),
		615996198=>array('pic' => "http://imgtest.meiliworks.com/pic/_o/67/af/287a4835ed2a416930580f9af06a_200_353.jpg", 'w' => 200, 'h' => 353 ),
		617522287=>array('pic' => "http://imgtest.meiliworks.com/pic/_o/9b/55/3374aa834a6ee165b32d3e13e818_200_385.jpg", 'w' => 200, 'h' => 385 ),
		624120529=>array('pic' => "http://imgtest.meiliworks.com/pic/_o/25/2c/c2b3f2c239cf74679bf05888169d_200_363.jpg", 'w' => 200, 'h' => 363 ),
		//622353733=>array('pic' => "http://imgtest.meiliworks.com/pic/_o/f2/c5/3ebb15adc87b4d1c5d458d9395eb_200_354.jpg", 'w' => 200, 'h' => 354 ),
		622697621=>array('pic' => "http://imgtest.meiliworks.com/pic/_o/95/28/bff4a6bca7967786994f1613ac59_200_354.jpg", 'w' => 200, 'h' => 354 ),
		623993057=>array('pic' => "http://imgtest.meiliworks.com/pic/_o/9e/3b/ec0a2facc373e5645f8a1696a6cf_200_354.jpg", 'w' => 200, 'h' => 354 ),
		615350903=>array('pic' => "http://imgtest.meiliworks.com/pic/_o/72/18/dc6aa0283e1e0e002516f390b295_200_337.jpg", 'w' => 200, 'h' => 337 ),
		627881449=>array('pic' => "http://imgtest.meiliworks.com/pic/_o/c0/a0/b294feb8b2fec3f02c4e097c03be_200_353.jpg", 'w' => 200, 'h' => 353 ),
	);
	public function isAbtest() {
		if (parent::abtest()) {
			return TRUE;
		}
		return FALSE;
	}

	public function getTestTids($testName, $frame, $page) {
		$tids = array();
		if ($testName == self::SYMBOL && $page == 0 && $frame < 1 && $frame >= 0) {
			$tids = $this->testTids;
		}	
		if (date("Y-m-d") >= '2012-11-16') {
			return array();
		}
		return $tids;
	}

	public function judgeTidInTetst($tid = 0) {
		if (date("Y-m-d") >= '2012-11-16') {
			return array();
		}
		return in_array($tid, $this->testTids);	
	}



	public function getImgByTid($tid = 0) {
		if (isset($this->imgIndex[$tid])) {
			return $this->imgIndex[$tid];
		}	
		return FALSE;
	}
} 
