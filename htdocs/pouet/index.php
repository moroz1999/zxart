<?php
define('ROOT_PATH', dirname(__FILE__) . '/../');

include_once('../trickster/cms/core/controller.class.php');
$controller = controller::getInstance(dirname(__FILE__) . '/../project/config/');
$controller->setApplication('pouet');
$controller->dispatch();