<?php
define('PUBLIC_PATH', dirname(__FILE__) . '/');
define('ROOT_PATH', dirname(__FILE__) . '/../');
include_once(ROOT_PATH . '/trickster-cms/cms/core/controller.class.php');
$controller = controller::getInstance(ROOT_PATH . 'project/config/');
$controller->dispatch();