<?php

namespace ItIsAllMail\Driver;

class HabrDateParser
{

    public static function parseArticleDate(string $rawDate): \DateTimeInterface
    {
        // see bug https://bugs.php.net/bug.php?id=51950
        $preDate = substr($rawDate, 0, 19) . substr($rawDate, 23, 1);

        $finalDate = \DateTime::createFromFormat(\DateTime::ISO8601, $preDate);

        if (! $finalDate) {
            throw new \Exception("Failed to parse date \"$preDate\"");
        }

        return $finalDate;
    }

    public static function parseCommentDate(string $rawDate): \DateTimeInterface
    {
        return self::parseArticleDate($rawDate);
    }
}
