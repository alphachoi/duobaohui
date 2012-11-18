<?php
namespace Snake\Package\Manufactory;

/**
 * 单推页得分享痕迹
 * @package manufactory
 * @author weiwang
 * @since 2012.08.29
 */
class ShareRepin{

	/**
	 * twitter的类型
	 *
	 * @var int 
	 * @access public
	 */
	public $showType = 7;

	/**
	 * twitter从哪个杂志社来的id
	 *
	 * @var int 
	 * @access public
	 */
	public $fromGroupId = 0;

	/**
	 * twitter从哪个杂志社来的名称
	 *
	 * @var string
	 * @access public
	 */
	public $fromGroupName = "";

	/**
	 * twitter被分享到的杂志社id
	 *
	 * @var int
	 * @access public
	 */
	public $toGroupId = 0;

	/**
	 * twitter被分享到的杂志社名称
	 *
	 * @var string
	 * @access public
	 */
	public $toGroupName = "";

	/**
	 * 原twitter的作者id
	 *
	 * @var int 
	 * @access public
	 */
	public $sourceTwitterAuthor = 0;

	/**
	 * 源twitter的作者昵称
	 *
	 * @var string
	 * @access public
	 */
	public $sourceTwitterAuthorNick = "";

	/** 
     * twitter的宝贝id
     *
     * @var int 
     * @access public
     */
    public $twitterGoodsId = 0;

	public function getRepin() {

		$repin = '';
		$flag = 1;
		if (!empty($this->fromGroupId)) {
			if (!empty($this->toGroupId)) {
				$repin .= '<span class="gray_f">从</span><span><a href="/person/u/' . $this->sourceTwitterAuthor . '" target="_blank">' . $this->sourceTwitterAuthorNick . '</a></span><span class="gray_f">的</span><span><a class="red f12" href="/group/' . $this->fromGroupId . '" target="_blank">#' . $this->fromGroupName . '#</a></span><span class="gray_f">分享了这个';
			}
			else {
				$repin .= '<span class="gray_f">分享了一个';
			}
		}
		else {
			if (!empty($this->toGroupId)) {
				$repin .= '<span class="gray_f">分享到</span><span><a class="red f12" href="/group/' . $this->toGroupId . '" target="_blank">#' . $this->toGroupName . '#</a></span>';	
				$flag = 0;
			}
			else {
				$repin .= '<span class="gray_f">分享了一个';	
			}
		}
		if (1 == $flag) {
			if ((7 == $this->showType || 8 == $this->showType) && !empty($this->twitterGoodsId)) {
				$repin .= "宝贝";
			}
			else {
				$repin .= "图片";
			}
			$repin .= '</span>';
		}
		
		return $repin;
	}

}
