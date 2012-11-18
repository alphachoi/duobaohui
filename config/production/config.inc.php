<?php

$DOMAIN_NAME = "http://" . $_SERVER['SERVER_NAME'] . "/";

define('BASE_URL', $DOMAIN_NAME);
define('MEILISHUO_URL', 'http://www.duobaohui.com');
define('GLOBAL_DOMAIN', 'www.duobaohui.com');
define('ROOT_DOMAIN', 'duobaohui.com');
define('IOHOST', 'http://zoo.meilishuo.com/');
define('AVATAR_URL', 'http://imgtest.meiliworks.com');
//CDN配置,必须保证10个,且不要轻易改变顺序
$GLOBALS['GOODS_URL_PICTURE'] = array(
	"http://imgtest.meiliworks.com" ,
	"http://imgtest.meiliworks.com" ,
	"http://imgtest-dl.meiliworks.com" ,
	"http://imgtest-dl.meiliworks.com" ,
	"http://imgtest-lx.meilishuo.net",
	"http://imgtest-lx.meilishuo.net",
	"http://imgst-dl.meilishuo.net",
	"http://imgtest-lx.meilishuo.net",
	"http://imgst-dl.meiliworks.com" ,
	"http://img-tx.meilishuo.net" 
);
$GLOBALS['GOODS_URL_PICTURE_COUNT'] = count($GLOBALS['GOODS_URL_PICTURE']);

$cookie_domain = $_SERVER['SERVER_NAME'];

define('DEFAULT_COOKIEDOMAIN', '.duobaohui.com');
define('DEFAULT_COOKIE_SESSION', 'duobaohui_login_id');
define('DEFAULT_COOKIEPATH', "/");
define('DEFAULT_SESSION_NAME',  'santorini_mm' );
define('DEFAULT_COOKIE_LOGON', 'DEFAULT_COOKIE_LOGON');
define('CHANNEL', 'CHANNEL_FROM');
//海报页加载
define('FRAME_SIZE_MAX', 6);//加载最大帧数
define('WIDTH_PAGE_SIZE', 20);//加载最大帧数
//define for sphinx
define("SPHINX_SERVER", "172.16.0.111");
define("SPHINX_PORT", 9312);

/* mlsstorage-kailian */
$GLOBALS['USEMLSSTORAGE'] = TRUE;
$GLOBALS['NEWQUERY'] = TRUE;
$GLOBALS['MLSSTORAGE'] = array (
	array('host' => '172.16.0.184', 'port' => '19090'),
	array('host' => '172.16.0.184', 'port' => '19091'),
	array('host' => '172.16.0.184', 'port' => '19092'),
	array('host' => '172.16.0.184', 'port' => '19094'),
	array('host' => '172.16.0.185', 'port' => '19090'),
	array('host' => '172.16.0.185', 'port' => '19091'),
	array('host' => '172.16.0.185', 'port' => '19092'),
	array('host' => '172.16.0.185', 'port' => '19094'),
	array('host' => '172.16.0.186', 'port' => '19090'),
	array('host' => '172.16.0.186', 'port' => '19091'),
	array('host' => '172.16.0.186', 'port' => '19092'),
	array('host' => '172.16.0.186', 'port' => '19094'),
);  

//新浪微博API

define("WB_AKEY",'');
define("WB_SKEY",'');
// Qzone api
define('QZONE_ID', '100331321');
define('QZONE_KEY', '08cace6f07b401f8695b305f69c9d365');

// TX微博API
define("TX_AKEY", '');
define("TX_SKEY", '');

// 163微博API
define('WY_AKEY', '');
define('WY_SKEY', '');
