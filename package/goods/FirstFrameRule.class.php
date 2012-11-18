<?php
namespace Snake\Package\Goods;

class FirstFrameRule {	
	private $poster = array();
	private $offset = NULL;

	function __construct($poster = array(), $offset = NULL) {
		$this->poster = $poster;
		$this->offset = $offset;
	}

	public function firstFrameAdjust() {
		if (empty($this->poster) || $this->offset != 0) {
			return $this->poster;
		}
		$posterTmp = $this->posterAdjust($this->poster, 30, 300);
		$posterTmp = $this->posterAdjust($posterTmp, 50, 500);
		if (!empty($posterTmp)) {
			$this->poster = $posterTmp;
		}
		return $this->poster;	
	}


	private function posterAdjust($sourcePoster = array(), $countForward = 30, $countLike = 300) {
		if (empty($sourcePoster)) {
			return $sourcePoster;
		}
		$poster = array();
		$frontTwitter = array();
		$afterTwitter = array();
		foreach ($this->poster as $p) {
			if ($p['count_forward'] < $countForward || $p['count_like'] < $countLike) {
				array_push($afterTwitter, $p);	
			}
			else {
				array_push($frontTwitter, $p);	
			}
		}
		$poster = array_merge($frontTwitter, $afterTwitter);
		return $poster;
	}
	


	


	

} 
