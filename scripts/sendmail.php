<?php

/**
  Tries to emulate classical sendmail, reading email from standard input. Acccepts args:
  -c <command> - command to be executed on given message
  -r - if set use data from register
 */

require_once("includes.php");

use ItIsAllMail\SendMailProcessor;

$config = yaml_parse_file($GLOBALS["__AppConfigFile"]);

$processor = new SendMailProcessor($config);

$options = getopt("c:r");


$result = 1;

try {
    $result = $processor->process(file_get_contents("php://stdin"), $options);
    exit($result);
} catch (\Exception $e) {
    print $e;
    exit(1);
}
