<?php

namespace ItIsAllMail\Utils;

/**
 * Mail headers are pretty strict when it comes to symbols it can contain. So
 * putting the data you parsed from site into a header is not a good
 * idea. There are we have few functions to solve thise issue.
 */

use ItIsAllMail\Constants;

class MailHeaderProcessor
{
    public static function rusTranslit(string $value): string
    {
        $converter = array(
            'а' => 'a',    'б' => 'b',    'в' => 'v',    'г' => 'g',    'д' => 'd',
            'е' => 'e',    'ё' => 'e',    'ж' => 'zh',   'з' => 'z',    'и' => 'i',
            'й' => 'y',    'к' => 'k',    'л' => 'l',    'м' => 'm',    'н' => 'n',
            'о' => 'o',    'п' => 'p',    'р' => 'r',    'с' => 's',    'т' => 't',
            'у' => 'u',    'ф' => 'f',    'х' => 'h',    'ц' => 'c',    'ч' => 'ch',
            'ш' => 'sh',   'щ' => 'sch',  'ь' => '',     'ы' => 'y',    'ъ' => '',
            'э' => 'e',    'ю' => 'yu',   'я' => 'ya',

            'А' => 'A',    'Б' => 'B',    'В' => 'V',    'Г' => 'G',    'Д' => 'D',
            'Е' => 'E',    'Ё' => 'E',    'Ж' => 'Zh',   'З' => 'Z',    'И' => 'I',
            'Й' => 'Y',    'К' => 'K',    'Л' => 'L',    'М' => 'M',    'Н' => 'N',
            'О' => 'O',    'П' => 'P',    'Р' => 'R',    'С' => 'S',    'Т' => 'T',
            'У' => 'U',    'Ф' => 'F',    'Х' => 'H',    'Ц' => 'C',    'Ч' => 'Ch',
            'Ш' => 'Sh',   'Щ' => 'Sch',  'Ь' => '',     'Ы' => 'Y',    'Ъ' => '',
            'Э' => 'E',    'Ю' => 'Yu',   'Я' => 'Ya',
        );

        $value = strtr($value, $converter);
        return $value;
    }

    /**
     * Make at least something readable and compatible with RFC2822 from
     * nickname typical for ru-net
     */
    public static function sanitizeNonLatinAddress(string $input): string
    {
        return preg_replace(
            '/[^A-Za-z0-9_\#\@\-]+/',
            '_',
            self::rusTranslit($input)
        );
    }

    public static function sanitizeSubjectHeader(string $subject): string {
        if (mb_strlen($subject) > Constants::IAM_HEADER_SUBJECT_TRUNCATE_LENGTH) {
            $subject = mb_substr($subject, 0, Constants::IAM_HEADER_SUBJECT_TRUNCATE_LENGTH) . "...";
        }
        $subject = preg_replace('/( +)|([\r\n])/', ' ', $subject);

        return $subject;
    }
}
