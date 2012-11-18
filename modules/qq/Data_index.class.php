<?php
namespace Snake\Modules\Qq;


use \Snake\libs\Cache\Memcache AS Memcache;
use \Snake\Package\Group\GroupSquare AS GroupSquare;
use \Snake\Package\Group\Groups AS Groups;
use \Snake\Package\Group\GroupUser AS GroupUser;
use \Snake\Package\Twitter\Twitter;



class Data_index extends \Snake\Libs\Controller {

	public function run() {
		
		$volumeName = 
			array(
				'发型那些事', 
				'约会穿衣手册', 
				//'日韩潮流速递', 
				'街拍那些范儿', 
				'傻瓜，让我给你一个家', 
				//'吃货都来这里勾搭', 
				'家有萌宠', 
				'黑白世界'
				);

		$xmlDoc = new \DOMDocument();
		$xmlDoc->formatOutput = true;
		$xmlstr = "<?xml version='1.0' encoding='utf-8' ?><sddindex></sddindex>";
		$xmlDoc->loadXML($xmlstr); 

		foreach ($volumeName AS $key => $value) {
			
			$x = $xmlDoc->getElementsByTagName('sddindex');
			$sdd = $xmlDoc->createElement("sdd"); 
			$x->item(0)->appendChild($sdd);

			$loc = $xmlDoc->createElement("loc");
			$sdd->appendChild($loc);
			$linkUrl = 'http://open.meilishuo.com/qq/board?bid=' . $key . "&token=9cc33f233c15ad8c";
			$text = $xmlDoc->createTextNode($linkUrl);//iconv("UTF-8", "GB2312",$linkUrl)); 
			$loc->appendChild($text);
			
			$time = date('Y-m-d', time("now"));
			$lastmod = $xmlDoc->createElement("lastmod");
			$sdd->appendChild($lastmod);
			$text = $xmlDoc->createTextNode($time);
			$lastmod->appendChild($text);
			
		}	

		$this->view = $xmlDoc->saveXML();
		return ;

	}


}
