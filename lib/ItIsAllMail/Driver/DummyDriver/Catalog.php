<?php

namespace ItIsAllMail\Driver;

use ItIsAllMail\Interfaces\CatalogDriverInterface;
use ItIsAllMail\DriverCommon\AbstractCatalogDriver;
use ItIsAllMail\HtmlToText;
use ItIsAllMail\CoreTypes\SerializationMessage;
use ItIsAllMail\Utils\Browser;
use ItIsAllMail\Utils\Debug;
use ItIsAllMail\Utils\URLProcessor;
use voku\helper\HtmlDomParser;
use voku\helper\SimpleHtmlDom;

class DummyCatalogDriver extends AbstractCatalogDriver implements CatalogDriverInterface
{
    protected string $driverCode = "dummy";

    public function queryCatalog(string $query, array $opts = []): array
    {

        $result = [
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
        return $result;
    }

    public function canHandleQuery(string $query, array $opts = []): bool
    {

        if (preg_match('/dummy/', $query)) {
            return true;
        }

        if ($opts["catalog_default_driver"] === $this->getCode()) {
            return true;
        }

        return false;
    }

    protected function postToText(SimpleHtmlDom $node): string
    {
        return (new HtmlToText($node->outerHtml()))->getText();
    }
}
