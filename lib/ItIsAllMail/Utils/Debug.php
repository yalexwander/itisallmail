<?php

namespace ItIsAllMail\Utils;

use ItIsAllMail\Message;

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


    public function dumpMessage(Message $msg): string
    {
        print <<<EOT
        subject: {$msg->subject}
        from: {$msg->from}
        parent: {$msg->parent}
        created: {$msg->created->format("Y-m-d H:i:s")}
        id: {$msg->id}
        body: {$msg->body}
====\n
EOT;
    }
}
