<?php

require_once("includes.php");

use ItIsAllMail\PostingQueue;
use ItIsAllMail\Utils\EmailParser;
use ItIsAllMail\Factory\AddressMapperFactory;
use ItIsAllMail\Factory\PosterDriverFactory;

$appConfig = yaml_parse_file($__AppConfigFile);

$options = getopt("m:");

$rawMessage = file_get_contents($options["m"]);

$msg = EmailParser::parseMessage($rawMessage);

$mapper = (new AddressMapperFactory($appConfig))->findMapper($msg);
$source = $mapper->mapMessageToSource($msg);

$posterFactory = new PosterDriverFactory($appConfig);
$poster = $posterFactory->findPoster($msg);

$result = $poster->post($msg, $source);
$logxf=fopen("/tmp/zlog.txt","a");fputs($logxf,print_r($result, true)  . "\n");fclose($logxf);chmod("/tmp/zlog.txt", 0666);

if ($result["status"]) {
    exit(0);
}
else {
    print_r($result);
    exit(1);
}

