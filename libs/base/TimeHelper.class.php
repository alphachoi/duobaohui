<?php 
/********************************************* 
计算程序运行时间的类 
浪子 http://www.oso.com.cn 
Sep 11,2000 
本类用来计算程序在机器上执行的时间。 
***********************************************/ 
namespace Snake\Libs\Base;
 
class TimeHelper { 

	private $StartTime = 0; 
	private $StopTime = 0; 
	private $TimeSpent = 0; 
 
	public function __construct() {}

	function start(){ 
		$this->StopTime = 0; 
		$this->TimeSpent = 0;
		$this->StartTime = microtime(); 
	} 
 
	function stop(){ 
		$this->StopTime = microtime(); 
	} 
 
	function spent( $showUnit = true ) { 
		$retStr = "";
		if ($this->TimeSpent) { 
			return $this->TimeSpent; 
		}
		else { 
			$StartMicro = substr($this->StartTime, 0, 10); 
			$StartSecond = substr($this->StartTime, 11, 10); 
			$StopMicro = substr($this->StopTime, 0, 10); 
			$StopSecond = substr($this->StopTime, 11, 10); 
			$start = doubleval($StartMicro) + $StartSecond; 
			$stop = doubleval($StopMicro) + $StopSecond; 
			$this->TimeSpent = $stop - $start; 
			if ( $this->TimeSpent < 0.0001 ) {
				$retStr =  0;
			}
			else {
				$retStr =  substr($this->TimeSpent, 0, 8) * 1000; // ."ms";
			} 
			if ( $showUnit ) {
				return $retStr;
			}
			else {
				return intval($retStr);
			}
		} 
	} // end function spent(); 
	
	function clear(){
		$this->StartTime = 0; 
		$this->StopTime = 0; 
		$this->TimeSpent = 0;
	}
 
} //end class timer; 
 
/* 这里是一个简单的例子： 
$timer = new timer; 
$timer->start(); 
$temp=0; 
for($i=0;$i<1000;$i++) for($j=0;$j<$i;$j++) $temp ++; 
$timer->stop(); 
echo "循环 $temp 次，运行时间为 ".$timer->spent(); 
*/ 
