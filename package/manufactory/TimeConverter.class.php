<?php
namespace Snake\Package\Manufactory;

/**
 * 时间转换
 * @package manufactory
 * @author weiwang
 * @since 2012.08.27
 */
class TimeConverter{

	/**
	 * 待转化的时间
	 *
	 * @var int 
	 * @access private
	 */
	private $time = 0;	

	public function __construct($time){
		$this->time = $time;
	}

	/**
	 * 获得转化后的时间
	 *
	 * @return string
	 * @access public
	 */	
	public function convert() {
	    $now = date("Y-m-d");
		$yearNow = date("Y");
		$yearLast = date("Y" , $this->time);
		if( $yearNow == $yearLast){
			$timeValue = ceil ( (time() - $this->time) / 60 );
			if ($timeValue < 0) { 
				$timeValue = 0 - $timeValue;
				$str = "0分钟前";
			}    
			elseif ($timeValue < 20) {
				$timeValue = ltrim( $timeValue, '-' );
				$str = " {$timeValue}分钟前 ";
			}    
			elseif (date("m" , $this->time) == date("m") && date("d" , $this->time) == date("d")) { //一个月以内的时间
				$str = '今天 ' . date ( "G:i", $this->time);
			}    
			elseif(date("m" , $this->time) == date("m") && date("d" , $this->time) == date("d")-1){
				$str = '昨天 ' . date ( "G:i", $this->time);
			}    
			else{//今年内的时间 并且 一天以上的时间
				$str = date ( "m月d日 G:i", $this->time);
			}    
		}else{ //一年以上的时间
			$str = date ( "Y年m月d日 G:i", $this->time);
		}    
		return $str;		
	}
}
