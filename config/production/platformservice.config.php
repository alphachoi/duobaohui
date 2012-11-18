<?php

define('SNAKE_PATH', __DIR__ . '/../..');

define('PLATFORM_SERVICE_PATH', SNAKE_PATH . '/libs/platformservice');

define('THRIFT_ROOT', SNAKE_PATH . '/libs/thrift');
$GLOBALS['THRIFT_ROOT'] = THRIFT_ROOT;

define('LOG_FILE_BASE_PATH' , '/home/work/webdata/logs/');
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
$GLOBALS['BATCH_RPC_SERVICE'] = array(
	array('host'=>'172.16.0.22','port'=>9190,'timeout'=>1000),
	array('host'=>'172.16.0.22','port'=>9191,'timeout'=>1000),
	array('host'=>'172.16.0.22','port'=>9192,'timeout'=>1000),
	array('host'=>'172.16.0.22','port'=>9193,'timeout'=>1000),
	array('host'=>'172.16.0.22','port'=>22194,'timeout'=>1000),
	array('host'=>'172.16.0.22','port'=>22195,'timeout'=>1000),
	array('host'=>'172.16.0.22','port'=>22196,'timeout'=>1000),
	array('host'=>'172.16.0.22','port'=>22197,'timeout'=>1000),
	array('host'=>'172.16.0.22','port'=>22198,'timeout'=>1000),

	array('host'=>'172.16.0.163','port'=>9190,'timeout'=>1000),
	array('host'=>'172.16.0.163','port'=>9191,'timeout'=>1000),
	array('host'=>'172.16.0.163','port'=>9192,'timeout'=>1000),
	array('host'=>'172.16.0.163','port'=>9193,'timeout'=>1000),
	array('host'=>'172.16.0.163','port'=>22194,'timeout'=>1000),
	array('host'=>'172.16.0.163','port'=>22195,'timeout'=>1000),
	array('host'=>'172.16.0.163','port'=>22196,'timeout'=>1000),
	array('host'=>'172.16.0.163','port'=>22197,'timeout'=>1000),
	array('host'=>'172.16.0.163','port'=>22198,'timeout'=>1000),

	array('host'=>'172.16.0.150','port'=>9190,'timeout'=>1000),
	array('host'=>'172.16.0.150','port'=>9191,'timeout'=>1000),
	array('host'=>'172.16.0.150','port'=>9192,'timeout'=>1000),
	array('host'=>'172.16.0.150','port'=>9193,'timeout'=>1000),
	array('host'=>'172.16.0.150','port'=>22194,'timeout'=>1000),
	array('host'=>'172.16.0.150','port'=>22195,'timeout'=>1000),
	array('host'=>'172.16.0.150','port'=>22196,'timeout'=>1000),
	array('host'=>'172.16.0.150','port'=>22197,'timeout'=>1000),
	array('host'=>'172.16.0.150','port'=>22198,'timeout'=>1000),

//	array('host'=>'172.16.0.108','port'=>9190,'timeout'=>1000),
//	array('host'=>'172.16.0.108','port'=>9191,'timeout'=>1000),
//	array('host'=>'172.16.0.108','port'=>9192,'timeout'=>1000),
//	array('host'=>'172.16.0.108','port'=>9193,'timeout'=>1000),
//	array('host'=>'172.16.0.108','port'=>22194,'timeout'=>1000),
//	array('host'=>'172.16.0.108','port'=>22195,'timeout'=>1000),
//	array('host'=>'172.16.0.108','port'=>22196,'timeout'=>1000),
//	array('host'=>'172.16.0.108','port'=>22197,'timeout'=>1000),
//	array('host'=>'172.16.0.108','port'=>22198,'timeout'=>1000),

//	array('host'=>'172.16.0.100','port'=>9190,'timeout'=>1000),
//	array('host'=>'172.16.0.100','port'=>9191,'timeout'=>1000),
//	array('host'=>'172.16.0.100','port'=>9192,'timeout'=>1000),
//	array('host'=>'172.16.0.100','port'=>9193,'timeout'=>1000),
//	array('host'=>'172.16.0.100','port'=>22194,'timeout'=>1000),
//	array('host'=>'172.16.0.100','port'=>22195,'timeout'=>1000),
//	array('host'=>'172.16.0.100','port'=>22196,'timeout'=>1000),
//	array('host'=>'172.16.0.100','port'=>22197,'timeout'=>1000),
//	array('host'=>'172.16.0.100','port'=>22198,'timeout'=>1000),
);

?>
