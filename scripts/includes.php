<?php

// setup basic paths for config files and load autoladers

$__AppMainDir = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR;

require_once($__AppMainDir . "vendor" . DIRECTORY_SEPARATOR . "autoload.php");
require_once($__AppMainDir . "lib" . DIRECTORY_SEPARATOR . "autoload.php");

$__AppConfDir = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "conf" . DIRECTORY_SEPARATOR;

$__AppConfigFile = $__AppConfDir . "config.yml";
$__AppSourcesFile = $__AppConfDir . "sources.yml";
