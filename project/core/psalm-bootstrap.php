<?php
define('PUBLIC_PATH', dirname(__FILE__) . '/../../zx/');
define('ROOT_PATH', dirname(__FILE__) . '/../../');

include_once(ROOT_PATH . 'vendor/artweb-ou/trickster-cms/cms/core/controller.class.php');
$controller = controller::getInstance(dirname(__FILE__) . '/../project/config/');