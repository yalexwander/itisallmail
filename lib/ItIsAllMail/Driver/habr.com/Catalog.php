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
    protected $driverCode = "catalog.habr.com";

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function queryCatalog(string $query, array $opts = []) : array {
        $dom = HtmlDomParser::str_get_html(file_get_contents("/tmp/index.html"));

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

        return false;
    }

    protected function postToText(SimpleHtmlDom $node): string
    {
        return (new HtmlToText($node->outerHtml()))->getText();
    }

    public function getCode($catalogType = null) : string {
        return $this->driverCode;
    }
}
