<?php

namespace ItIsAllMail\Driver;

use ItIsAllMail\Interfaces\FetchDriverInterface;
use ItIsAllMail\AbstractFetcherDriver;
use ItIsAllMail\HtmlToText;
use ItIsAllMail\Message;
use ItIsAllMail\Utils\Browser;
use ItIsAllMail\Utils\Debug;
use ItIsAllMail\Utils\URLProcessor;
use voku\helper\HtmlDomParser;
use voku\helper\SimpleHtmlDom;

class HabrFetcherDriver extends AbstractFetcherDriver implements FetchDriverInterface
{
    protected $driverCode = "habr.com";
    protected $defaultCommentDate;

    public function __construct(array $opts)
    {
        parent::__construct($opts);

        $this->defaultCommentDate = new \DateTime('2000-01-01');
    }

    /**
     * Return array of all posts in thread, including original article
     */
    public function getPosts(array $source): array
    {
        $posts = [];

        if (null === $this->getLastURLVisited($source["url"])) {
            $posts[] = $this->getFirstPost($source);
        }

        $posts = array_merge($posts, $this->getComments($source));

        return $posts;
    }
    /**
     * Make post from the article itself
     */
    public function getFirstPost(array $source): Message
    {
        $html = Browser::getAsString($source["url"]);
        Debug::debug("Downloaded post page");
        $dom = HtmlDomParser::str_get_html($html);

        $postContainer = $dom->findOne(".tm-article-presenter__body");
        $author = $postContainer->findOne("a.tm-user-info__userpic")->getAttribute("title");
        $postText = $this->postToText(
            $postContainer->findOne(".tm-article-body")
        );
        $postDate = $postContainer->findOne(".tm-article-snippet__datetime-published > time")->getAttribute("datetime");
        $postTitle = $postContainer->findOne(".tm-article-snippet__title")->text();
        $postId = $this->getThreadIdFromURL($source["url"]);

        $this->defaultCommentDate = $this->parseArticleDate($postDate);

        $msg = new Message([
            "from" => $author . "@" . $this->getCode(),
            "subject" => $postTitle,
            "parent" => null,
            "created" => $this->defaultCommentDate,
            "id" => $postId . "@" . $this->getCode(),
            "body" => $postText,
            "thread" => $postId . "@" . $this->getCode()
        ]);

        $this->setLastURLVisited($source["url"], $source["url"]);

        return $msg;
    }

    /**
     * Parse comments to array
     */
    public function getComments(array $source): array
    {
        $commentsURL = URLProcessor::normalizeStartURL($source["url"]);
        $commentsURL .= "comments/";

        $threadId = $this->getThreadIdFromURL($commentsURL);

        $html = Browser::getAsString($commentsURL);
        Debug::debug("Downloaded comments page");
        $dom = HtmlDomParser::str_get_html($html);

        $defaultCommentDate = new \DateTime('2000-01-01');
        $comments = [];
        foreach ($dom->findMulti("article.tm-comment-thread__comment") as $node) {
            $commentTextWidget = $node->findOneOrFalse(".tm-comment__body-content");

            $postId = $this->getCommentIdFromLink(
                $node->findOne("a")->getAttribute("name")
            );

            $parent = $node->parentNode()->parentNode()->parentNode();

            if ($parent !== null and $parent->getAttribute("class") === "tm-comment-thread") {
                $parent = $this->getCommentIdFromLink(
                    $parent->findOne("article > a.tm-comment-thread__target")->getAttribute("name")
                );

                if ($parent == $postId) {
                    $parent = $threadId;
                }
            } else {
                $parent = $threadId;
            }

            // by default we treat comment as deleted
            $commentTitle = "DELETED";
            $commentBody = "DELETED";
            $commentAuthor = "UFO";
            $commentDate = $this->defaultCommentDate;

            // if comment is not deleted
            if ($commentTextWidget) {
                $commentTitle = preg_replace("/[\r\n]/", " ", $commentTextWidget->text());
                $commentBody = $this->postToText($commentTextWidget);
                $commentAuthor = $node->findOne(".tm-user-info__username")->text();
                $commentDate = $this->parseCommentDate(
                    $node->findOne(".tm-comment-thread__comment-link")->text()
                );
            }

            $comments[] = new Message([
                "from" => $commentAuthor . "@" . $this->getCode(),
                "subject" => $commentTitle,
                "parent" => $parent . "@" . $this->getCode(),
                "created" => $commentDate,
                "id" => $postId . "@" . $this->getCode(),
                "body" => $commentBody,
                "thread" => $threadId  . "@" . $this->getCode()
            ]);
        }

        return $comments;
    }

    /**
     * Convert to text readable by CLI mail client
     */
    protected function postToText(SimpleHtmlDom $node): string
    {
        return (new HtmlToText($node->outerHtml()))->getText();
    }

    /**
     * Parse habr.com date representation to DateTime
     */
    protected function parseCommentDate(string $rawDate): \DateTimeInterface
    {
        $months = [
            "января"   => "01",
            "февраля"  => "02",
            "марта"    => "03",
            "апреля"   => "04",
            "мая"      => "05",
            "июня"     => "06",
            "июля"     => "07",
            "августа"  => "08",
            "сентября" => "09",
            "октября"  => "10",
            "ноября"   => "11",
            "декабря"  => "12"
        ];

        $datePrepared = "";
        if (strpos($rawDate, "вчера") !== false) {
            $date = new \DateTime();
            $date->sub(new \DateInterval("P1D"));
            $datePrepared = preg_replace("/вчера/", $date->format("j m Y"), $rawDate);
        } elseif (strpos($rawDate, "сегодня") !== false) {
            $date = new \DateTime();
            $datePrepared = preg_replace("/сегодня/", $date->format("j m Y"), $rawDate);
        } else {
            $datePrepared = preg_replace_callback(
                "/(" . implode("|", array_keys($months)) . ")/",
                function ($m) use ($months) {
                    return $months[$m[1]];
                },
                $rawDate
            );
        }
        $datePrepared = str_replace("в ", "", $datePrepared);
        $datePrepared = substr($datePrepared, 0, 16);

        $finalDate = \DateTime::createFromFormat("d.m.Y H:i", $datePrepared);

        if (! $finalDate) {
            throw new \Exception("Failed to parse date $datePrepared");
        }

        return $finalDate;
    }


    protected function getCommentIdFromLink(string $raw): string
    {
        $id = null;
        preg_match("/comment_([0-9]+)/", $raw, $id);
        return $id[1];
    }

    protected function getThreadIdFromURL(string $url): string
    {
        $id = null;
        preg_match("/\/([0-9]+)\/(comments)*/", $url, $id);
        return $id[1];
    }
}
