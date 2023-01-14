<?php

// setup basic paths for config files and load autoladers

$__AppMainDir = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR;

require_once($__AppMainDir . "vendor" . DIRECTORY_SEPARATOR . "autoload.php");
require_once($__AppMainDir . "lib" . DIRECTORY_SEPARATOR . "autoload.php");

$__AppConfDir = $__AppMainDir . "conf" . DIRECTORY_SEPARATOR;
if (getenv('IAM_TEST_ENV')) {
    $__AppConfDir = $__AppMainDir . "tests" . DIRECTORY_SEPARATOR . "conf" . DIRECTORY_SEPARATOR . getenv('IAM_TEST_ENV') . DIRECTORY_SEPARATOR;
}

$__AppConfigFile = $__AppConfDir . "config.yml";
