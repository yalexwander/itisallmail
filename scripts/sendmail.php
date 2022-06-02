<?php

/**
  Tries to emulate classical sendmail, reading email from standard input. Acccepts args:
  -c <command> - command to be executed on given message
 */

require_once("includes.php");

use ItIsAllMail\SendMailProcessor;

$config = yaml_parse_file($__AppConfigFile);

$processor = new SendMailProcessor($config);

$options = getopt("c:");

exit(
    $processor->process(file_get_contents("php://stdin"), $options)
);

