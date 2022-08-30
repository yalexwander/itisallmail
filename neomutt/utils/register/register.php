#!/usr/bin/env php
<?php

/**
 * Due to neomutt command limits sometimes it just can not pass 2 messages in
 * one command in a proper way. So as workaround this script saves passed
 * message to a "register", and next command called from neomutt can access
 * not only last passed message, but a previous one too.
 *
 * Usage:
 *  cat mime_file | php register.php -s <register> // save to register
 *  php register.php -r <register> // get from register
 *  php register.php -c <register> // clear register
 */

require_once(
    __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR .
    "lib" . DIRECTORY_SEPARATOR . "ItIsAllMail" . DIRECTORY_SEPARATOR . "MUA" . DIRECTORY_SEPARATOR .
    "Register.php"
);

$options = getopt("s:g:c:");

if (! empty($options["s"])) {
    (new ItIsAllMail\MUA\Register())->set(
        $options["s"],
        file_get_contents("php://stdin")
    );
}
elseif(! empty($options["g"])) {
    print (new ItIsAllMail\MUA\Register())->get($options["g"]);
}
elseif(! empty($options["c"])) {
    (new ItIsAllMail\MUA\Register())->set(
        $options["c"],
        ""
    );
}
