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

    /**
     * Return array of all posts in thread
     */
    public function getPosts(Source $source): array
    {
        $posts = [];

        $posts = [
            new SerializationMessage([
                "from" => "example" . "@" . $this->getCode(),
                "subject" => "example subject",
                "parent" => null,
                "created" => new \DateTime(),
                "id" => "some id",
                "body" => "text here",
                "thread" => "thread id",
                "uri" => "url where can be fetched"
            ])
        ];

        return $posts;
    }
   
}
