<?php

namespace ItIsAllMail\Driver\Habr;

use ItIsAllMail\Interfaces\FetchDriverInterface;
use ItIsAllMail\AbstractFetcherDriver;
use ItIsAllMail\HtmlToText;
use ItIsAllMail\Message;
use Symfony\Component\DomCrawler\Crawler;

class HabrDriver extends AbstractFetcherDriver implements FetchDriverInterface
{
    protected $crawler;
    protected $driverCode = "habr.com";
    protected $threadId;

    /**
     * Return array of all posts in thread, including original article
     */
    public function getPosts(array $source): array
    {
        $posts = [];

        if (null === $this->getLastURLVisited($source["url"])) {
            $posts[] = $this->getFirstPost($source);
            $this->setLastURLVisited($source["url"], $source["url"]);
        }

        $posts = array_merge($posts, $this->getComments($source));

        return $posts;
    }
    /**
     * Make post from the article itself
     */
    public function getFirstPost(array $source): Message
    {
        $html = file_get_contents($source["url"]);
        $this->crawler = new Crawler($html);

        $postContainer = $this->crawler->filter(".tm-article-presenter__body")->first();
        $author = $postContainer->filter("a.tm-user-info__userpic")->first()->attr("title");
        $postText = $this->postToText(
            $postContainer->filter(".tm-article-body")->first()
        );
        $postDate = $postContainer->filter(".tm-article-snippet__datetime-published > time")->first()->attr("datetime");
        $postTitle = $postContainer->filter(".tm-article-snippet__title")->first()->text();
        $postId = $this->getThreadIdFromURL($source["url"]);
        $this->threadId = $postId;

        $msg = new Message([
            "from" => $author . "@" . $this->getCode(),
            "subject" => $postTitle,
            "parent" => null,
            "created" => $this->parseArticleDate($postDate),
            "id" => $postId . "@" . $this->getCode(),
            "body" => $postText,
            "thread" => $postId . "@" . $this->getCode()
        ]);

        return $msg;
    }

    /**
     * Parse comments to array
     */
    public function getComments(array $source): array
    {
        $commentsURL = $source["url"];

        // fix url to prevent double slash
        if (strstr($source["url"], "/") === (strlen($source["url"]) - 1)) {
            $commentsURL .= "/";
        }

        $commentsURL .= "comments/";

        $this->threadId = $this->getThreadIdFromURL($commentsURL);

        $html = file_get_contents($commentsURL);
        $this->crawler = new Crawler($html);

        $comments = [];
        $this->crawler->filter("article.tm-comment-thread__comment")->each(
            function ($node, $i) use (&$comments) {

                // skip deleted comments
                $isBanned = $node->filter(".comment__message_banned")->count() > 0;

                if ($isBanned) {
                    return;
                }


                $parent = $node->closest(".tm-comment-thread__children");

                if ($parent !== null) {
                    $parent = $parent->closest("section")
                        ->filter("article.tm-comment-thread__comment")->first()
                        ->filter("a")->first();
                    $parent = $this->getCommentIdFromLink($parent->attr("name"));
                } else {
                    $parent = $this->threadId;
                }

                $comments[] = new Message([
                    "from" => $node->filter(".tm-user-info__username")->first()->text()  . "@" . $this->getCode(),
                    "subject" => $node->filter(".tm-comment__body-content")->first()->text(),
                    "parent" => $parent . "@" . $this->getCode(),
                    "created" => $this->parseCommentDate(
                        $node->filter(".tm-comment-thread__comment-link")->first()->text()
                    ),
                    "id" => $this->getCommentIdFromLink(
                        $node->filter("a")->first()->attr("name")
                    )  . "@" . $this->getCode(),
                    "body" => $this->postToText($node->filter(".tm-comment__body-content")->first()),
                    "thread" => $this->threadId  . "@" . $this->getCode()
                ]);
            }
        );

        return $comments;
    }

    /**
     * Convert to text readable by CLI mail client
     */
    protected function postToText(Crawler $node): string
    {
        return (new HtmlToText($node->html()))->getText();
    }

    /**
     * Parse habr.com date representation to DateTime
     */
    protected function parseArticleDate(string $rawDate): \DateTimeInterface
    {
        // see bug https://bugs.php.net/bug.php?id=51950
        $preDate = substr($rawDate, 0, 19) . substr($rawDate, 23, 1);

        $finalDate = \DateTime::createFromFormat(\DateTime::ISO8601, $preDate);

        if (! $finalDate) {
            throw new \Exception("Failed to parse date $preDate");
        }

        return $finalDate;
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
