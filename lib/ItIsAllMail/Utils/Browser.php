<?php

/**
 * This is light static wrap around GuzzleHttp\Client. While it is enough for
 * most cases, if you think you need more contol on browser, please use the
 * GuzzleHttp\Client itself.
 */

namespace ItIsAllMail\Utils;

use GuzzleHttp\Client;

class Browser
{
    protected static array $userAgents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:108.0) Gecko/20100101 Firefox/108.0',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36',
        'Mozilla/5.0 (X11; Linux x86_64; rv:108.0) Gecko/20100101 Firefox/108.0',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.2 Safari/605.1.15',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:108.0) Gecko/20100101 Firefox/108.0',
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
        if (getenv('IAM_DEBUG_BROWSER_CACHE')) {
            if (file_exists(getenv('IAM_DEBUG_BROWSER_CACHE'))) {
                $data = file_get_contents(getenv('IAM_DEBUG_BROWSER_CACHE'));

                return [
                    "status" => "ok",
                    "data" => $data,
                    "headers" => [],
                    "cookies" => []
                ];
            }
        }

        $data = $client->request('GET', $url)->getBody();

        if (getenv('IAM_DEBUG_BROWSER_CACHE')) {
            file_put_contents(getenv('IAM_DEBUG_BROWSER_CACHE'), $data);
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
