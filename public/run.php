#! /home/service/php/bin/php
<?php
namespace Snake;

if ($argc < 2) {
	exit("Wrong parameters");
}

define('ROOT_PATH', __DIR__ . '/..');

require_once(ROOT_PATH . '/libs/base/Autoloader.class.php');
require_once(ROOT_PATH . '/config/production/config.inc.php');
require_once(ROOT_PATH . '/config/production/sphinx.config.php');
require_once(ROOT_PATH . '/config/production/platformservice.config.php');

$root_path_setting = array(
	'snake' => ROOT_PATH,
);
$autoloader = Libs\Base\Autoloader::get($root_path_setting);

// set config namespace for IO's config loader so IO can load proper
// configuration of MySQL, Redis, Memcache, etc.
Libs\Base\Config::setConfigNamespace('\\Snake\\Config\\Production');

$class = "\\Snake\\Scripts\\{$argv[1]}";
if (!class_exists($class)) {
	exit("Wrong class");
}

ini_set('default_socket_timeout', -1);

$worker = new $class();
$worker->run();

exit(0);
