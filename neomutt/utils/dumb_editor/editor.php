<?php

/**
  Fake editor to put needed body into email file. Now it is only suitable to
  work with simple emails without attachements or any MIME parts.

  Command line usage:

  php editor.php -i <email file> [ -b <email body content> -c <citation text file> ]

 */

$options = getopt("b:i:c");

if (! empty($options["b"])) {
    $srcMessage  = file_get_contents($options["i"]);
    $srcMessage = $srcMessage . $options["b"] . "\n";

    file_put_contents($options["i"], $srcMessage);
}
