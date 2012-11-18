<?php
namespace Snake;

define('ROOT_PATH', __DIR__ . '/..');


// require zoo's autoloader
require_once(ROOT_PATH . '/libs/base/Autoloader.class.php');
require_once(ROOT_PATH . '/config/testing/config.inc.php');
require_once(ROOT_PATH . '/config/testing/sphinx.config.php');
require_once(ROOT_PATH . '/config/testing/platformservice.config.php');


$root_path_setting = array(
	'snake' => ROOT_PATH,
);
$autoloader = Libs\Base\Autoloader::get($root_path_setting);

//start TimeHelper
$timeHelper = new Libs\Base\TimeHelper();
$timeHelper->start();

// configuration of MySQL, Redis, Memcache, etc.
Libs\Base\Config::setConfigNamespace('\\Snake\\Config\\Testing');


//GLOBAL_COOKIE
$GLOBAL_COOKIE_STRING = "";

$dispatcher = Libs\Dispatcher::get('xml');
$dispatcher->dispatch();

$timeHelper->stop();
//exec fastcgi_finish_request
if (function_exists(fastcgi_finish_request)) {
	fastcgi_finish_request();
}

//get the request and the user
$request = $dispatcher->get_request();
$userSession = $dispatcher->get_userSession();
$module = $dispatcher->get_module();
$action = $dispatcher->get_action();

$str = '';
$current_user_id = $userSession['user_id'];
$str .= "[" . $current_user_id . "]\t";
$str .= "[" . $module . "]\t";
$str .= "[" . $action . "]\t";

$sqlStr = $str;

$str .= "[" . $request->refer . "]\t";
$str .= "[" . $request->agent . "]\t";
$str .= "[" . Libs\Base\Utilities::getMemUsed() . "]\t";
$str .= "[" . $timeHelper->spent() . "]\t";

//sql relational
$sql_monitor = Libs\DB\SQLMonitor::getMonitor();
$queries = $sql_monitor->dump();
$sqlLogHandle = new Libs\Base\SnakeLog('xml_db_syslog', 'normal');

foreach ($queries as $query) {
	$sqlLine = $sqlStr . "[" . $query['sql'] . "]\t[" . $query['params'] . "]\t[" . $query['time_spent'] . "]";
	$sqlLogHandle->w_log($sqlLine);
}

//sql_run_nubmer
list($sql_run_number, $sql_average_time) = $sql_monitor->getQueriesStatistics();

$str .= "[" . $sql_run_number . "]\t";
$str .= "[" . $sql_average_time . "]\t";

$str .= "[" . $request->channel . "]\t";

$str .= "[" . $GLOBAL_COOKIE_STRING . "]\t";

//post
$posts = "";
foreach ($request->POST as $name => $content) {
	$posts .= "{$name}:{$content}";
}
$str .= "[" . urlencode($posts) . "]\t";

//request_uri
$str .= "[" . $request->requri . "]\t";

$str .= "[" . $request->ip . "]";

$logHandle = new Libs\Base\SnakeLog('xml_syslog', 'normal');
$logHandle->w_log($str);
