<?php

$DOMAIN_NAME = "http://".$_SERVER['SERVER_NAME'] . "/";

define('BASE_URL',$DOMAIN_NAME);
define('MEILISHUO_URL', 'http://wwwtest.meilishuo.com');
define('GLOBAL_DOMAIN', 'meilishuo.com');
define('ROOT_DOMAIN', 'meilishuo.com');
define('IOHOST', 'http://zootest.meilishuo.com/');
define('AVATAR_URL', 'http://imgst-office.meilishuo.net');
// TBC: 将URL更改成本地环境的URL
/*
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
 */
$GLOBALS['GOODS_URL_PICTURE'] = array(
	"http://imgst-office.meilishuo.net" 
);
$GLOBALS['GOODS_URL_PICTURE_COUNT'] = count($GLOBALS['GOODS_URL_PICTURE']);

$cookie_domain = $_SERVER['SERVER_NAME'];

define('DEFAULT_COOKIEDOMAIN', '.meilishuo.com');
define('DEFAULT_COOKIEPATH', "/");
define('DEFAULT_SESSION_NAME',  'santorini_mm' );
define('DEFAULT_COOKIE_LOGON', 'DEFAULT_COOKIE_LOGON');
define('CHANNEL', 'CHANNEL_FROM');

//海报页加载
define('FRAME_SIZE_MAX', 6);//加载最大帧数
define('WIDTH_PAGE_SIZE', 20);//加载最大帧数
//define for sphinx
define('SPHINX_SERVER', '192.168.1.198');
define('SPHINX_PORT',   9312);

//新浪微博API
//by huazhulin from dolphin
define("WB_AKEY",'463778370');
define("WB_SKEY",'5debfe56a704c557370e8b38b848a9b7');
// Qzone api
define('QZONE_ID', '210915');
define('QZONE_KEY', 'a07ca0a76cd200132516256ef5b8b8f3');

// TX微博API
define("TX_AKEY", '95fd1cb5bf304d259fdaec43297d8b33');
define("TX_SKEY", '0c31da9fbfea6a54a9f91c5b1ac3a621');

// 163微博API
define('WY_AKEY', 'tmjviiCQmmElRydo');
define('WY_SKEY', 'vikYwBGilmdsjScjMEbEqlWcVCHqcMp6');

//人人应用API
define('RENREN_API_KEY', '9d98d5371349498086dce6af7fe87ffb');
define('RENREN_SECRET', 'b6942bece88146c096a4967c451e25e5');
