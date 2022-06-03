<?php

namespace ItIsAllMail\Utils;

use GuzzleHttp\Client;

class Browser
{
    protected static $userAgents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko)' .
        'Chrome/94.0.4606.81 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:93.0) Gecko/20100101 Firefox/93.0',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko)' .
        ' Chrome/94.0.4606.61 Safari/537.36',
        'Mozilla/5.0 (X11; Linux x86_64; rv:93.0) Gecko/20100101 Firefox/93.0',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.81 Safari/537.36'
    ];

    public static function getAsString(string $url, array $headers = [], array $cookies = []): ?string
    {
        $client = new Client([
            'headers' => [
                'User-Agent' => self::$userAgents[0],
            ]
        ]);

        return $client->request('GET', $url)->getBody();
    }
}
