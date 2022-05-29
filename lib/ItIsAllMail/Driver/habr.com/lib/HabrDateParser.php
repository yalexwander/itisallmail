<?php

namespace ItIsAllMail\Driver;

class DateParser {

    public static function parseArticleDate(string $rawDate): \DateTimeInterface
    {
        // see bug https://bugs.php.net/bug.php?id=51950
        $preDate = substr($rawDate, 0, 19) . substr($rawDate, 23, 1);

        $finalDate = \DateTime::createFromFormat(\DateTime::ISO8601, $preDate);

        if (! $finalDate) {
            throw new \Exception("Failed to parse date $preDate");
        }

        return $finalDate;
    }

    public static function parseCommentDate(string $rawDate): \DateTimeInterface
    {
        $months = [
            "января"   => "01",
            "февраля"  => "02",
            "марта"    => "03",
            "апреля"   => "04",
            "мая"      => "05",
            "июня"     => "06",
            "июля"     => "07",
            "августа"  => "08",
            "сентября" => "09",
            "октября"  => "10",
            "ноября"   => "11",
            "декабря"  => "12"
        ];

        $datePrepared = "";
        if (strpos($rawDate, "вчера") !== false) {
            $date = new \DateTime();
            $date->sub(new \DateInterval("P1D"));
            $datePrepared = preg_replace("/вчера/", $date->format("j m Y"), $rawDate);
        } elseif (strpos($rawDate, "сегодня") !== false) {
            $date = new \DateTime();
            $datePrepared = preg_replace("/сегодня/", $date->format("j m Y"), $rawDate);
        } else {
            $datePrepared = preg_replace_callback(
                "/(" . implode("|", array_keys($months)) . ")/",
                function ($m) use ($months) {
                    return $months[$m[1]];
                },
                $rawDate
            );
        }
        $datePrepared = str_replace("в ", "", $datePrepared);
        $datePrepared = substr($datePrepared, 0, 16);

        $finalDate = \DateTime::createFromFormat("d.m.Y H:i", $datePrepared);

        if (! $finalDate) {
            throw new \Exception("Failed to parse date $datePrepared");
        }

        return $finalDate;
    }
}
