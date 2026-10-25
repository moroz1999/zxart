<?php
declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

// A test that runs in its own process fails on any output written before its
// result, and the Illuminate helpers raise deprecations while the child process
// compiles them on loading the Composer autoloader, before PHPUnit sets
// anything up. The child process reads php/errors.ini as well, which leaves
// deprecations out; it inherits the scan directory from this environment.
$scanDirectories = getenv('PHP_INI_SCAN_DIR') ?: PHP_CONFIG_FILE_SCAN_DIR;
putenv('PHP_INI_SCAN_DIR=' . $scanDirectories . PATH_SEPARATOR . __DIR__ . '/php');
