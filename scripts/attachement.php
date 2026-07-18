<?php

use ItIsAllMail\Utils\EmailParser;

define('__AppVendorAutoload', true); require_once("includes.php");
use Symfony\Component\Yaml\Yaml;

$appConfig = Yaml::parseFile($GLOBALS["__AppConfigFile"]);

$msg = EmailParser::parseMessage(file_get_contents("php://stdin"));

// TODO: see attachment view in roadmap
