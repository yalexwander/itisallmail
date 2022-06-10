<?php

require_once("includes.php");

use ItIsAllMail\PostingQueue;
use ItIsAllMail\Utils\EmailParser;
use ItIsAllMail\Factory\AddressMapperFactory;

$appConfig = yaml_parse_file($__AppConfigFile);

$options = getopt("m:");

$rawMessage = file_get_contents($options["m"]);

$msg = EmailParser::parseMessage($rawMessage);

$mapper = (new AddressMapperFactory($appConfig))->findMapper($msg);
$source = $mapper->mapMessageToSource($msg);

print_r($source);exit(1);
