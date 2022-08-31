<?php

$catalogDir =
    __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." .
    DIRECTORY_SEPARATOR . "mailboxes" . DIRECTORY_SEPARATOR . "catalog" . DIRECTORY_SEPARATOR . "new";

$blankMessageFilename = $catalogDir . DIRECTORY_SEPARATOR . "blank-message-to-delete.eml";

file_put_contents($blankMessageFilename, "Subject: None\nFrom: None\n\nNone");
