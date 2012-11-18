<?php
namespace Snake\Package\Mall;

Use \Snake\Package\Mall\MakeSql ;

class Test{
	function getSql(){
		/*$list = array(
					"table" => "t_dolphin_mall_profile",
					"colum" => "uid,banner,notice",
					"where" => array("uid" => 765,"gid" => 121),
					"where_in" => array("uid" => '765,124', "gid" => '121,122'),
					"order" => "uid desc",
					"limit" => "0,10"
		);
		*/
		/*
		$list = array(
					"table" => "t_dolphin_mall_profile",
					"insert" => array('uid' => 765,
									  'gid' => 111,
									  'banner' => 'sssss'
					)
		
		);
		*/
		/*
		$list = array(
					"table" => 't_dolphin_mall_profile',
					"update" => array('uid' => 764,'gid' => 124),
					"where" => array('uid' => 765, 'gid' => 111),
					"where_in" => array('uid' => '737,222', 'gid' => '222,221')
		
		);
		*/
		$list = array(
					"table" => 't_dolphin_mall_profile',
					"where" => array('uid' => 765, 'gid' => 111),
					"where_in" => array('uid' => '737,222', 'gid' => '222,221')
		
		);
		$sqlMonitor = new MakeSql('delete',$list);
		print_r($sqlMonitor);
		return $sqlMonitor;
	}
	
}
