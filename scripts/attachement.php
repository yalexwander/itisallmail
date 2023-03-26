<?php

use ItIsAllMail\Utils\EmailParser;

require_once("includes.php");

$appConfig = yaml_parse_file($__AppConfigFile);

$msg = EmailParser::parseMessage(file_get_contents("php://stdin"));

// TODO: see attachement view in roadmap
