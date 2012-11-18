<?php
namespace Snake\Package\Manufactory;

/**
 * 分享痕迹
 * @package manufactory
 * @author weiwang
 * @since 2012.08.13
 */
class Repin{

	/**
	 * twitter的类型
	 *
	 * @var int 
	 * @access public
	 */
	public $showType = 7;

	/**
	 * twitter所属的杂志社id
	 *
	 * @var int 
	 * @access public
	 */
	public $groupId = 0;

	/**
	 * 原推所属杂志id
	 *
	 * @var int 
	 * @access public
	 */
	public $sourceTwitterGroupId = 0;

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
	 * 源twitter的类型
	 *
	 * @var int
	 * @access public
	 */
	public $sourceTwitterShowType = 7;

	/**
	 * 被分享到得杂志id
	 *
	 * @var int
	 * @access public
	 */
	public $toGroupId = 0;

	/**
	 * 被分享到得杂志名称
	 *
	 * @var string
	 * @access public
	 */
	public $toGroupName = "";

	public function getRepin() {
		$repin = '';
		if ((7 == $this->showType || 2 == $this->showType) && empty($this->groupId)) {
			if (7 == $this->showType) {
				$repin = "分享了一个宝贝";	
			}
			elseif(2 == $this->showType) {
				$repin = "分享了一张图片";	
			}
		}
		elseif (9 == $this->showType) {
			//喜欢的原推被删掉了
			if (empty($this->sourceTwitterAuthor) || empty($this->sourceTwitterAuthorNick)) {
				$repin = "喜欢了一";	
			}
			else {
				$repin = "喜欢了<a href='/person/u/$this->sourceTwitterAuthor' target='_blank'>" . $this->sourceTwitterAuthorNick . "</a>的一";	
			}

			if (in_array($this->sourceTwitterShowType, array(3, 8, 7, 9))) {
				$repin .= "个宝贝";	
			}
			elseif(2 == $this->sourceTwitterShowType) {
				$repin .= "张图片";	
			}
			else {
				$repin .= "个宝贝";	
			}
		}

		if (!empty($this->groupId) && !empty($this->toGroupName)) {
			//原推被删除了
			if (empty($this->sourceTwitterAuthor) || empty($this->sourceTwitterGroupId) || 2 == $this->showType || 7 == $this->showType) {
				$repin = "<span>分享到</span>";	
			}
			else {
				$repin = "<span>把</span><a href='/person/u/$this->sourceTwitterAuthor' target='_blank'>" . $this->sourceTwitterAuthorNick . "</a><span>的分享收进杂志</span>";	
			}
			$repin .= "<a href='/group/$this->toGroupId' target='_blank'>#" . $this->toGroupName . "#</a>";
		}
		elseif (in_array($this->showType, array(3, 8))) {
			$repin = "分享了一个宝贝";	
		}
		if (empty($repin)) {
			if (7 == $this->showType) {
				$repin = "分享了一个宝贝";	
			}
			else {
				$repin = "分享了一个图片";	
			}
		}
		return $repin;
	}

}
