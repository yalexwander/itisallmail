<?php


namespace ItIsAllMail\Driver;

use ItIsAllMail\Interfaces\FetchDriverInterface;
use ItIsAllMail\DriverCommon\AbstractFetcherDriver;
use ItIsAllMail\Factory\CatalogDriverFactory;
use ItIsAllMail\HtmlToText;
use ItIsAllMail\CoreTypes\SerializationMessage;
use ItIsAllMail\Utils\Browser;
use ItIsAllMail\Utils\Debug;
use ItIsAllMail\Utils\URLProcessor;
use voku\helper\HtmlDomParser;
use voku\helper\SimpleHtmlDom;
use voku\helper\SimpleHtmlDomInterface;
use ItIsAllMail\CoreTypes\Source;

class DummyFetcherDriver extends AbstractFetcherDriver implements FetchDriverInterface
{
    protected string $driverCode = "dummy";

    public function __construct(array $appConfig, array $opts)
    {
        parent::__construct($appConfig, $opts);
    }

    public function getPosts(Source $source): array
    {
        $posts = [];

        $data = json_decode(Browser::getAsString($source["url"]), true);

        foreach ($data as $msg) {
            $posts[] =
                new SerializationMessage([
                    "from" => $msg["from"],
                    "subject" => $msg["title"],
                    "parent" => $msg["parent"],
                    "created" => new \DateTime($msg["date"]),
                    "id" => $msg["id"] . "@" . $this->getCode(),
                    "body" => $msg["data"],
                    "thread" => $msg["thread"],
                    "uri" => "http://dummy.me/" . $msg["id"]
                ]);
        }

        // print_r($posts);die("\n");

        return $posts;
    }
}
