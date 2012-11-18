<?php
namespace Snake\Package\Goods\Helper;

/**
 * 一个监控工具小集合
 *
 * @author Wei Wang 
 * @author Xuan Zheng
 * @package 宝库
 */

class CallMeHelper {

	/**
	 * 发送邮件
	 * @param string the content want send
	 * @return boolean
	 */
	static public function sendEmail($title = '', $content = '', $to = array('xuanzheng@meilishuo.com')) {
		$to = implode(",", $to);
		$subject = "=?UTF-8?B?".base64_encode($title)."?=";
		$headers = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type:text/plain; charset=utf-8' . "\r\n";
		return mail($to, $subject, $content, $headers);
	}

}
