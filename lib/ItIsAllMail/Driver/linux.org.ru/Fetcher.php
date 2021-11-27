<?php

namespace ItIsAllMail\Driver\Habr;

use ItIsAllMail\Interfaces\FetchDriverInterface;
use ItIsAllMail\AbstractFetcherDriver;
use ItIsAllMail\HtmlToText;
use ItIsAllMail\Message;
use ItIsAllMail\Utils\Storage;
use ItIsAllMail\Utils\Browser;
use ItIsAllMail\Utils\Debug;
use ItIsAllMail\Utils\MailHeaderProcessor;
use ItIsAllMail\Utils\URLProcessor;
use voku\helper\HtmlDomParser;
use voku\helper\SimpleHtmlDom;

class LinuxOrgRuFetcher extends AbstractFetcherDriver implements FetchDriverInterface
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
            $html = Browser::getAsString($url);

            $dom = HtmlDomParser::str_get_html($html);

            foreach ($dom->findMulti("article.msg") as $postNode) {
                $isStartPost = $postNode->findOneOrFalse(".msg_body > footer > div.sign > a");

                $author = $postNode->findOneOrFalse(".msg_body div.sign > a");

                if ($author !== false) {
                    $author = $author->text();
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

            $nextPage = $dom->findMulti("#comments .nav a.page-number");
            $nextPage = $nextPage->offsetGet($nextPage->count() - 1);

            if (($nextPage !== null) and strstr($nextPage->text(), "→")) {
                $url = rtrim($dom->findOne("base")->getAttribute("href"), "/") . $nextPage->getAttribute("href");
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
    protected function getPostText(SimpleHtmlDom $post): string
    {
        $bodyWidget = $post->findOne(".msg_body");

        $cleanedHTML = "";

        // bulky, but allows to parse op-post and comments in a same way
        foreach ($bodyWidget->childNodes() as $tag) {
            $tagName = $tag->getNode()->nodeName;
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
                $cleanedHTML .= $tag->outerHtml();
            }
        }

        $text = (new HtmlToText($cleanedHTML))->getText();

        $text = preg_replace('/\n\n+$/s', '', $text);

        return $text;
    }

    /**
     * Generate something can be put into "Subject" field
     */
    protected function getPostTitle(SimpleHtmlDom $post, string $bodyText): string
    {
        $titleWidget = $post->findOneOrFalse("h1 > a");

        $title = "";
        if ($titleWidget !== false) {
            $title = $titleWidget->text();
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
        preg_match("/\/(forum|news)\/([^\/]+)\/([0-9]+)/", $url, $id);
        return $id[1] . "_" . $id[2] . "_" . $id[3];
    }

    protected function getParent(SimpleHtmlDom $node, string $defaultParent): string
    {
        $parent = $node->find("a")[0];
        if ($parent !== null) {
            if (
                preg_match(
                    '/(forum|news)\/([^\/]+)\/([0-9]+)\?cid=([0-9]+)/',
                    $parent->getAttribute("href"),
                    $matches
                )
            ) {
                return $matches[1] . "_" . $matches[2] . "_" . $matches[3] . "#" . $matches[4];
            }
        }

        return $defaultParent;
    }

    /**
     * Try to extract post date. We have at least 2 html formats here. For old
     * topics and new ones. Probably more, so fallback to current date.
     */
    protected function getPostDate(SimpleHtmlDom $post): \DateTime
    {
        $dateWidget = $post->find("div.sign > time")[0];

        if ($dateWidget !== null) {
            return new \DateTime($dateWidget->getAttribute('datetime'));
        } else {
            return new \DateTime();
        }
    }


    protected function getPostId(SimpleHtmlDom $post): string
    {
        preg_match('/\-([0-9]+)/', $post->getAttribute("id"), $id);
        return $id[1];
    }
}
