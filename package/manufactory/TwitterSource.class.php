<?php
namespace Snake\Package\Manufactory;

/**
 * 推来源
 * @package manufactory
 * @author weiwang
 * @since 2012.08.29
 */
class TwitterSource {

	/**
	 * twitter的来源
	 *
	 * @var string
	 * @access private 
	 */
	private $sourceCode = "web";

	function __construct($sourceCode){
		$this->sourceCode = $sourceCode;
	}

	/**
	 * 获取twitter的来源
	 *
	 * @return string 
	 * @access public 
	 */
	public function getSource() {
		$source = "";
		if ($this->sourceCode == 'pickup') {
			$source .= '来自<a href="/pickup" target="_blank">美丽说拾宝工具</a>';	
		}
		/*else {
			$source .= "";
			//$source .= '来自<span class="red">分享按钮</span>';	
		}*/
		return $source;
	}

}
