<?php

define('SNAKE_PATH', __DIR__ . '/../..');

define('PLATFORM_SERVICE_PATH', SNAKE_PATH . '/libs/platformservice');

define('THRIFT_ROOT', SNAKE_PATH . '/libs/thrift');
$GLOBALS['THRIFT_ROOT'] = THRIFT_ROOT;

define('LOG_FILE_BASE_PATH' , '/home/work/webdata/logs/');

$GLOBALS['MLSSTORAGE'] = array (
        array('host' => '192.168.1.198', 'port' => '19090'),
    );

$GLOBALS['BATCH_RPC_SERVICE'] = array(
    array('host'=>'192.168.1.198','port'=>9190,'timeout'=>9000),
    array('host'=>'192.168.1.198','port'=>9191,'timeout'=>9000),
    );

?>
