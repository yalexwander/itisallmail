<?php

require_once("includes.php");

use ItIsAllMail\SendMailProcessor;

$config = yaml_parse_file($__AppConfigFile);

$processor = new SendMailProcessor($config);

exit(
    $processor->process(file_get_contents("php://stdin"))
);
