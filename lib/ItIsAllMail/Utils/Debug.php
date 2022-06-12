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


    public static function dumpMessage(Message $msg): string
    {
        return <<<EOT
        subject: {$msg->getSubject()}
        from: {$msg->getFrom()}
        parent: {$msg->getParent()}
        created: {$msg->getCreated()->format("Y-m-d H:i:s")}
        id: {$msg->getId()}
        body: {$msg->getBody()}
====\n
EOT;
    }


    public static function dumpResponse($response) : string {
        $result = "";

        foreach ($response->getHeaders() as $name => $values) {
            $result .= $name . ': ' . implode(', ', $values) . "\n";
        }

        $result .= "\n" . $response->getBody()->getContents();

        return $result;
    }

}
