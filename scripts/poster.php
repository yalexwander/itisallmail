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

try {
    $result = $poster->post($msg, $source);

    if ($result["status"]) {
        exit(0);
    }
    else {
        print_r($result);
        exit(1);
    }
} catch (\Exception $e) {
    print $e;
    exit(1);
}
