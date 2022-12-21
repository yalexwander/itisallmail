<?php

namespace ItIsAllMail\Utils;

use ItIsAllMail\CoreTypes\SerializationMessage;

class Debug
{

    public static function debug(string $str): void
    {
        if (getenv('IAM_DEBUG')) {
            self::log($str);
        }
    }


    public static function log(string $str): void
    {
        print (new \DateTime())->format("Y-m-d H:i:s: ");
        print $str;
        print "\n";
    }


    public static function dumpMessage(SerializationMessage $msg): string
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


    public static function dumpResponse(mixed $response): string
    {
        $result = "";

        foreach ($response->getHeaders() as $name => $values) {
            $result .= $name . ': ' . implode(', ', $values) . "\n";
        }

        $result .= "\n" . $response->getBody()->getContents();

        return $result;
    }


    public static function saveResponseToDebugQueue(string $data): ?string
    {
        if (! getenv('IAM_DEBUG')) {
            return null;
        }

        $saveDir = "/tmp/iam-dumps";
        $queueSize = 100;

        $existingFiles = glob($saveDir . DIRECTORY_SEPARATOR . "*");
        if (count($existingFiles) > $queueSize) {
            unlink($existingFiles[0]);
        }

        $tmpFile = $saveDir . DIRECTORY_SEPARATOR . microtime() . ".dump";

        file_put_contents($tmpFile, $data);

        return $tmpFile;
    }
}
