<?php

require_once(ROOT_PATH . '/libs/amqplibs/vendor/symfony/Symfony/Component/ClassLoader/UniversalClassLoader.php');

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
    'PhpAmqpLib' => ROOT_PATH . '/libs/amqplibs/',
));

$loader->register();
