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
    $rawMessage = file_get_contents("php://stdin");

    if (getenv('IAM_DEBUG')) {
        file_put_contents('sendmail_dump_' . microtime(true), $rawMessage);
    }

    $result = $processor->process($rawMessage, $options);
    exit($result);
} catch (\Exception $e) {
    print $e;
    exit(1);
}
