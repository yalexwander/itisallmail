<?php

namespace ItIsAllMail\Driver;

use ItIsAllMail\Interfaces\CatalogDriverInterface;
use ItIsAllMail\AbstractCatalogDriver;

use ItIsAllMail\HtmlToText;
use ItIsAllMail\Message;
use ItIsAllMail\Utils\Browser;
use ItIsAllMail\Utils\Debug;
use ItIsAllMail\Utils\URLProcessor;
use voku\helper\HtmlDomParser;
use voku\helper\SimpleHtmlDom;

require_once(__DIR__ . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . "HabrDateParser.php");

class HabrCatalogDriver extends AbstractCatalogDriver implements CatalogDriverInterface {

    protected $config;
    protected $driverCode = "habr.com";

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function queryCatalog(string $query, array $opts = []) : array {
        $url = $this->createUrlFromQuery($query);

        $html = Browser::getAsString($url);
        $dom = HtmlDomParser::str_get_html($html);

        $result = [];

        foreach ($dom->findMulti("article.tm-articles-list__item") as $postContainer) {
            $author = $postContainer->findOne("a.tm-user-info__userpic")->getAttribute("title");

            $postText = $this->postToText(
                $postContainer->findOne(".article-formatted-body")
            );

            $postDate = DateParser::parseArticleDate(
                $postContainer->findOne(".tm-article-snippet__datetime-published > time")->getAttribute("datetime")
            );
            $postTitle = $postContainer->findOne(".tm-article-snippet__title")->text();
            $postId = $postContainer->getAttribute("id");

            $msg = new Message([
                "from" => "all" . "@" . $this->getCode(),
                "subject" => $postTitle,
                "parent" => null,
                "created" => $postDate,
                "id" => $postId . "@" . $this->getCode(),
                "body" => $postText,
                "thread" => $postId . "@" . $this->getCode()
            ]);

            $result[] = $msg;
        }

        return $result;
    }

    public function canHandleQuery(string $query, array $opts = []): bool {

        if (preg_match('/habr\.com/', $query)) {
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

    public function getCode($catalogType = null) : string {
        return $this->driverCode;
    }

    protected function createUrlFromQuery(string $query) : string {
        if (preg_match('/^https:\/\//', $query)) {
            return $query;
        }

        $domain = "https://habr.com";
        $language = "ru";

        if (preg_match('/^en (.+)$/', $query, $queryParam)) {
            $query = $queryParam[0];
            $language = "en";
        }

        if (preg_match('/^(all|top)$/', $query, $queryParam)) {
            return $domain . "/" . $language . "/" . $queryParam[0];
        }

        if (preg_match('/^hub\/(.+)$/', $query, $queryParam)) {
            return $domain . "/" . $language . "/hub/" . $queryParam[0];
        }

        return $domain . "/" . $language . "/" .  "top";
    }
}
