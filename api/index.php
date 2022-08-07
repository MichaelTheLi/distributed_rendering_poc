<?php

define('API_ROOT_DIR', __DIR__);
define('ROOT_DIR', API_ROOT_DIR .'/..');

require_once API_ROOT_DIR . '/vendor/autoload.php';

$app = new \PoF\App();
$app->run();
