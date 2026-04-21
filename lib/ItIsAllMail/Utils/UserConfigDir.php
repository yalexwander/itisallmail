<?php

namespace ItIsAllMail\Utils;

/*
  For getting configs from user based folder
*/

class UserConfigDir
{
    protected static string $dirName = "iam";

    public static function getDir(): string
    {
        $dirPath = "";

        if (PHP_OS_FAMILY === 'Windows') {
            $dirPath = (getenv("HOMEDRIVE") . getenv("HOMEPATH") . DIRECTORY_SEPARATOR . self::$dirName);
        } else {
            $dirName = getenv("HOME") . DIRECTORY_SEPARATOR . ".config" . DIRECTORY_SEPARATOR . self::$dirName;
        }

        if (! is_dir($dirName)) {
            mkdir($dirName);
        }

        return $dirName;
    }
}
