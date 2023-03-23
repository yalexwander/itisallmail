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
use voku\helper\SimpleHtmlDomInterface;

require_once(__DIR__ . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . "HabrDateParser.php");

class HabrCatalogDriver extends AbstractCatalogDriver implements CatalogDriverInterface
{

    protected string $driverCode = "habr.com";

    public function queryCatalog(string $query, array $opts = []): array
    {
        $html = $this->getHTMLForQuery($query);
        Debug::saveResponseToDebugQueue($html);
        $dom = HtmlDomParser::str_get_html($html);

        $result = [];

        foreach ($dom->findMulti("article.tm-articles-list__item") as $postContainer) {
            $author = $postContainer->findOne("a.tm-user-info__userpic")->getAttribute("title");

            $postText = $this->postToText(
                $postContainer->findOne(".article-formatted-body")
            );

            $postDate = HabrDateParser::parseArticleDate(
                $postContainer->findOne(".tm-article-datetime-published > time")->getAttribute("datetime")
            );
            $postTitle = $postContainer->findOne(".tm-article-snippet__title")->text();
            $postId = $postContainer->getAttribute("id");

            $postURI = $postContainer->findOne("h2.tm-article-snippet__title>a")->getAttribute("href");
            $postURI = "https://" . $this->getCode() . $postURI;

            $msg = new SerializationMessage([
                "from" => $author . "@" . $this->getCode(),
                "subject" => $postTitle,
                "parent" => null,
                "created" => $postDate,
                "id" => $postId . "@" . $this->getCode(),
                "body" => $postText,
                "thread" => $postId . "@" . $this->getCode(),
                "uri" => $postURI
            ]);

            $result[] = $msg;
        }

        return $result;
    }

    public function canHandleQuery(string $query, array $opts = []): bool
    {

        if (preg_match('/habr\.com/', $query)) {
            return true;
        }

        if ($opts["catalog_default_driver"] === $this->getCode()) {
            return true;
        }

        return false;
    }

    protected function postToText(SimpleHtmlDomInterface $node): string
    {
        return (new HtmlToText($node->outerHtml()))->getText();
    }

    public function getCode(string $catalogType = null): string
    {
        return $this->driverCode;
    }

    protected function getHTMLForQuery(string $query): string
    {
        $url = $query;
        $cookies = [];
        $languages = [ "ru", "en" ];

        if (! preg_match('/^https:\/\//', $query)) {
            $domain = "https://habr.com";

            $url = $domain . "/" . $languages[0] . "/" .  "all";

            if (preg_match('/^(en|ru) (.+)$/', $query, $queryParam)) {
                $query = $queryParam[1];
                $languages = [ $queryParam[0] ];
            }

            if (preg_match('/^(all|top|news)$/', $query, $queryParam)) {
                $url = $domain . "/" . $languages[0] . "/" .  $queryParam[0];
            }

            if (preg_match('/^hub\/(.+)$/', $query, $queryParam)) {
                $url = $domain . "/" . $languages[0] . "/" . $queryParam[0];
            }
        }

        $cookies = [
            "fl" => implode(",", $languages)
        ];

        return Browser::getAsString($url, [], $cookies);
    }
}
