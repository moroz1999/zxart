<?php

use ZxArt\IpBan\RequestGuard;

define('PUBLIC_PATH', dirname(__FILE__) . '/');
define('ROOT_PATH', dirname(__FILE__) . '/../');
include_once(ROOT_PATH . 'vendor/artweb-ou/trickster-cms/cms/core/controller.class.php');
$controller = controller::getInstance(ROOT_PATH . 'project/config/');

$app = $controller->getApplication();
$guard = $app->getService(RequestGuard::class);
if (!$guard->isAllowed($_SERVER)) {
    http_response_code(403);
    echo "You've been blocked automatically. <a href='mailto:moroz1999@gmail.com'>Send a note</a> if it is a mistake";
    exit;
}

$controller->dispatch();