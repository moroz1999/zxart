<?php
declare(strict_types=1);

define('PUBLIC_PATH', __DIR__ . '/../../htdocs/');
define('ROOT_PATH', __DIR__ . '/../../');
include_once(ROOT_PATH . 'trickster-cms/cms/core/controller.class.php');
$controller = controller::getInstance(__DIR__ . '/../../project/config/');