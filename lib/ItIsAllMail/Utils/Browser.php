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
        'Chrome/94.0.4606.61 Safari/537.36',
        'Mozilla/5.0 (X11; Linux x86_64; rv:93.0) Gecko/20100101 Firefox/93.0',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.81 Safari/537.36'
    ];

    public static function get(string $url, array $headers = [], array $cookies = []): array
    {
        $client = new Client([
            'headers' => array_merge(
                [
                    'User-Agent' => self::$userAgents[0],
                ],
                $headers
            )
        ]);

        $data = '';
        if (getenv('CIM_DEBUG_BROWSER_CACHE')) {
            if (file_exists(getenv('CIM_DEBUG_BROWSER_CACHE'))) {
                $data = file_get_contents(getenv('CIM_DEBUG_BROWSER_CACHE'));
            }

            return [
                "status" => "ok",
                "data" => $data,
                "headers" => [],
                "cookies" => []
            ];
        }

        $data = $client->request('GET', $url)->getBody();

        if (getenv('CIM_DEBUG_BROWSER_CACHE')) {
            file_put_contents(getenv('CIM_DEBUG_BROWSER_CACHE'), $data);
        }

        return [
            "status" => "ok", // ok, connection_fail, proxy_fail, connection_hanged
            "data" => $data,
            "headers" => [],
            "cookies" => []
        ];
    }

    public static function getAsString(string $url, array $headers = [], array $cookies = []): ?string
    {
        $result = self::get($url, $headers, $cookies);
        return $result["data"];
    }

    public static function getRandomUserAgent(): string
    {
        return self::$userAgents[ array_rand(self::$userAgents) ];
    }
}
