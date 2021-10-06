<?php

namespace ItIsAllMail\Utils;

class Debug
{

    public static function debug(string $str): void
    {
        if (getenv('CIM_DEBUG')) {
            self::log($str);
        }
    }


    public static function log(string $str): void
    {
        print (new \DateTime())->format("Y-m-d H:i:s: ");
        print $str;
        print "\n";
    }
}
