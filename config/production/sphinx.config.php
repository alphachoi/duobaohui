<?php
//Sphinx搜索
//config write by zhengxuan at 5.24
define("SPHINX_SERVER", "127.0.0.1");
define("SPHINX_PORT", 9312);
$GLOBALS['SPHINX']['MASTER']['HOST'] = '127.0.0.1';
$GLOBALS['SPHINX']['MASTER']['PORT'] = 9312;
$GLOBALS['SPHINX']['SLAVE'][] = array('HOST'=>'127.0.0.1','PORT'=>9912);
