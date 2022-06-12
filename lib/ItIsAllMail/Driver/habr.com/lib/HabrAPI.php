<?php

namespace ItIsAllMail\Driver;

use ItIsAllMail\Utils\Browser;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie;
use GuzzleHttp\Cookie\SetCookie;
use ItIsAllMail\Utils\Debug;

class HabrAPI {

    protected $credentials;
    protected $client;
    protected $cookieJar;

    public function __construct($credentials)
    {
        $this->credentials = $credentials;
    }

    public function auth() : bool {
        $this->cookieJar = new CookieJar();
        $this->cookieJar = CookieJar::fromArray([
            "connect_sid" => $this->credentials["secret"],
        ], "habr.com");

        $this->client = new Client([
            'cookies' => true,
            'headers' => [
                'User-Agent' => Browser::getRandomUserAgent()
            ],
        ]);

        return true;
    }

    /**
     * $comment [
         "article" => Article id
         "parent" => Parent comment if presented
         "text" => comment text
       ]
     */
    public function sendComment(array $comment) : array {
        
        $jsonRequest = [
            "isMarkdown" => true,
            "parentId" => $comment["parent"],
            "text" => [
                "source" => $this->formatTextToPseudoMarkdown($comment["text"])
            ],
            "editorVersion" => 2
        ];

        $jsonData = json_encode($jsonRequest, JSON_UNESCAPED_UNICODE);

        // print_r($jsonData);exit(1);
 
        $csrfToken = $this->getCSRFToken($comment["source"]["url"]);

        if (empty($csrfToken)) {
            throw new \Exception("Failed to get CSRF token");
        }

        $response = $this->client->request(
            'POST',
            "https://habr.com/kek/v2/comments/" . $comment["article"] . "/add",
            [
                "cookies" => $this->cookieJar,
                "headers" => [
                    'Content-Type' => 'application/json',
                    'csrf-token' => $csrfToken
                ],
                "body" => $jsonData,
            ]
        );

        return json_decode($response->getBody()->getContents(), true);
    }

    public function getCSRFToken(string $url) : string {
        $commentsUrl = $url . "comments";

        $response = $this->client->request(
            "GET",
            $commentsUrl,
            [
                "cookies" => $this->cookieJar,
                "headers" => [
                    "Referer" => $url
                ]
            ]
        );
        $content = $response->getBody()->getContents();
        preg_match('/<meta name="csrf-token" content="(.+?)">/', $content , $token);

        return $token[1];
    }

    protected function formatTextToPseudoMarkdown(string $text) : string {
        $paragraphs = explode("\n", $text);

        $md = [
            "type" => "doc",
            "content" => []
        ];
        
        foreach ($paragraphs as $p) {
            if (empty($p)) {
                continue;
            }
            
            $md["content"][] = [
                "type" => "paragraph",
                "attrs" => [
                    "align" => null,
                    "simple" => false,
                    "persona" => false
                ],
                "content" => [
                    [
                        "type" => "text",
                        "text" => $p
                    ]
                ]
            ]; 
        }

        $json = json_encode($md, JSON_UNESCAPED_UNICODE);
        return $json;
    }

}
