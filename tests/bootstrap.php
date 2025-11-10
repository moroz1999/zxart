<?php
declare(strict_types=1);

namespace {
    // Composer autoload must be loaded first to keep bootstrap minimal
    require __DIR__ . '/../vendor/autoload.php';

    // Allow mocking of final classes in tests only
    \DG\BypassFinals::enable();

    define('PUBLIC_PATH', __DIR__ . '/../htdocs/');
    define('ROOT_PATH', __DIR__ . '/../');

    include_once(ROOT_PATH . 'trickster-cms/cms/core/controller.class.php');
    controller::getInstance(ROOT_PATH . 'project/config/');
}
