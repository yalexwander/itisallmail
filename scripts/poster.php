<?php

/**
 * Options:
 * -m - file with message to post
 * -r - load refernced message from reply register
 */

require_once("includes.php");

use ItIsAllMail\PostingQueue;
use ItIsAllMail\Utils\EmailParser;
use ItIsAllMail\Factory\AddressMapperFactory;
use ItIsAllMail\Factory\PosterDriverFactory;

$appConfig = yaml_parse_file($GLOBALS["__AppConfigFile"]);

$options = getopt("m:r");

$jsonMessage = file_get_contents($options["m"]);

$msg = EmailParser::parseJSONMessage($jsonMessage);
if (isset($options["r"]) and empty($msg->getReferencedMessage())) {
    $msg->setReferencedMessage(
        EmailParser::loadReferencedMessageFromRegister("reply")
    );
}

$mapper = (new AddressMapperFactory($appConfig))->findMapper($msg);
$source = $mapper->mapMessageToSource($msg);

$posterFactory = new PosterDriverFactory($appConfig);
$poster = $posterFactory->findPoster($msg);

try {
    $poster->checkBeforePost($msg, $source);
    $result = $poster->post($msg, $source);

    if ($result["status"]) {
        exit(0);
    } else {
        print_r($result);
        exit(1);
    }
} catch (\Exception $e) {
    print $e;
    exit(1);
}
