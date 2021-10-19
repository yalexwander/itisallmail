<?php

namespace ItIsAllMail\Driver\Habr;

use ItIsAllMail\Interfaces\FetchDriverInterface;
use ItIsAllMail\AbstractFetcherDriver;
use ItIsAllMail\HtmlToText;
use ItIsAllMail\Message;
use ItIsAllMail\Utils\Storage;
use ItIsAllMail\Utils\Debug;
use ItIsAllMail\Utils\MailHeaderProcessor;
use ItIsAllMail\Utils\URLProcessor;
use PHPHtmlParser\Dom;
use PHPHtmlParser\Dom\HtmlNode;

class LORDriver extends AbstractFetcherDriver implements FetchDriverInterface
{
    protected $crawler;
    protected $driverCode = "linux.org.ru";

    /**
     * Return array of all posts in thread, including original article
     */
    public function getPosts(array $source): array
    {
        $posts = [];

        $startUrl = URLProcessor::normalizeStartURL($source["url"]);
        $threadId = $this->getThreadIdFromURL($startUrl);

        $url = "";
        if (isset($source["ignore_last_page"]) and $source["ignore_last_page"]) {
            $url = $startUrl;
        } else {
            $url = $this->getLastURLVisited($threadId) ?? $startUrl;
        }

        while ($url) {
            Debug::log("Processing $url");

            $html = file_get_contents($url);
            $dom = new Dom();
            $dom->loadStr($html);

            foreach ($dom->find("article.msg") as $postNode) {
                $isStartPost = $postNode->find(".msg_body > footer > div.sign > a")[0] !== null;

                $author = $postNode->find(".msg_body div.sign > a")[0];
                if ($author !== null) {
                    $author = $author->text;
                } else {
                    continue;
                }

                $author = MailHeaderProcessor::sanitizeCyrillicAddress($author);

                $parent = $this->getParent($postNode, $threadId);
                $created = $this->getPostDate($postNode);
                $postId = $threadId . "#" . $this->getPostId($postNode);
                if ($isStartPost) {
                    $postId = $threadId;
                }

                $postText = $this->getPostText($postNode);
                $title = $this->getPostTitle($postNode, $postText);

                $msg = new Message(
                    [
                        "from" => $author . "@" . $this->getCode(),
                        "subject" => $title,
                        "parent" => $parent . "@" . $this->getCode(),
                        "created" => $created,
                        "id" => $postId . "@" . $this->getCode(),
                        "body" => $postText,
                        "thread" => $threadId . "@" . $this->getCode()
                    ]
                );

                $posts[] = $msg;
            }

            $nextPage = $dom->find("#comments .nav a.page-number");
            $nextPage = $nextPage[count($nextPage) - 1];
            if (($nextPage !== null) and strstr($nextPage->text, "→")) {
                $url = rtrim($dom->find("base")->getAttribute("href"), "/") . $nextPage->getAttribute("href");
                Debug::debug("New url: $url");
            } else {
                $this->setLastURLVisited($threadId, $url);
                $url = false;
            }
        }

        return $posts;
    }


    /**
     * Convert to text readable by CLI mail client
     */
    protected function getPostText(HtmlNode $post): string
    {
        $bodyWidget = $post->find(".msg_body")[0];

        $cleanedHTML = "";

        // bulky, but allows to parse op-post and comments in a same way
        foreach ($bodyWidget->getChildren() as $tag) {
            $tagName = $tag->getTag()->name();
            $class = $tag->getAttribute("class");

            if (
                ($tagName === "div" and
                 (
                     ($class === "sign") or
                     ($class === "reply") or
                     ($class === "fav-buttons")
                 )
                ) or
                $tagName === 'footer'
            ) {
                continue;
            } else {
                $cleanedHTML .= $tag->innerHtml;
            }
        }

        $text = (new HtmlToText($cleanedHTML))->getText();

        $text = preg_replace('/\n\n+$/s', '', $text);

        return $text;
    }

    /**
     * Generate something can be put into "Subject" field
     */
    protected function getPostTitle(HtmlNode $post, string $bodyText): string
    {
        $titleWidget = $post->find("h1 > a")[0];

        $title = "";
        if ($titleWidget !== null) {
            $title = $titleWidget->text;
        } else {
            $lines = explode("\n", $bodyText);
            foreach ($lines as $line) {
                // skip quoted lines
                if (strpos($line, ">") !== 0) {
                    $title .= $line . " ";
                }
                if (mb_strlen($title) > 512) {
                    break;
                }
            }
        }

        return $title;
    }


    protected function getThreadIdFromURL(string $url): string
    {
        $id = null;
        preg_match("/\/forum\/([^\/]+)\/([0-9]+)/", $url, $id);
        return $id[1] . "_" . $id[2];
    }

    protected function getParent(HtmlNode $node, string $defaultParent): string
    {
        $parent = $node->find("a")[0];
        if ($parent !== null) {
            if (preg_match('/forum\/([^\/]+)\/([0-9]+)\?cid=([0-9]+)/', $parent->getAttribute("href"), $matches)) {
                return $matches[1] . "_" . $matches[2] . "#" . $matches[3];
            }
        }

        return $defaultParent;
    }

    /**
     * Try to extract post date. We have at least 2 html formats here. For old
     * topics and new ones. Probably more, so fallback to current date.
     */
    protected function getPostDate(HtmlNode $post): \DateTime
    {
        $dateWidget = $post->find("div.sign > time")[0];

        if ($dateWidget !== null) {
            return new \DateTime($dateWidget->getAttribute('datetime'));
        } else {
            return new \DateTime();
        }
    }


    protected function getPostId(HtmlNode $post): string
    {
        preg_match('/\-([0-9]+)/', $post->getAttribute("id"), $id);
        return $id[1];
    }
}
