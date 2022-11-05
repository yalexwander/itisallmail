<?php

namespace ItIsAllMail\Driver;

use ItIsAllMail\Interfaces\FetchDriverInterface;
use ItIsAllMail\DriverCommon\AbstractFetcherDriver;
use ItIsAllMail\HtmlToText;
use ItIsAllMail\CoreTypes\SerializationMessage;
use ItIsAllMail\Utils\Storage;
use ItIsAllMail\Utils\Browser;
use ItIsAllMail\Utils\Debug;
use ItIsAllMail\Utils\MailHeaderProcessor;
use ItIsAllMail\Utils\URLProcessor;
use voku\helper\HtmlDomParser;
use voku\helper\SimpleHtmlDom;
use voku\helper\SimpleHtmlDomInterface;
use ItIsAllMail\CoreTypes\Source;

class LinuxOrgRuFetcher extends AbstractFetcherDriver implements FetchDriverInterface
{
    protected $crawler;
    protected $driverCode = "linux.org.ru";

    protected $threadMessageMap;

    /**
     * Return array of all posts in thread, including original article
     */
    public function getPosts(Source $source): array
    {
        $posts = [];

        $startUrl = URLProcessor::normalizeStartURL($source["url"]);
        $threadId = $this->getThreadIdFromURL($startUrl);
        $rootMessageId = $this->getRootMessage($threadId);

        $url = "";
        if (! empty($source["ignore_last_page"])) {
            $url = $startUrl;
        } else {
            $url = $this->getLastURLVisited($threadId) ?? $startUrl;
        }

        $this->threadMessageMap = [];

        while ($url) {
            $html = Browser::getAsString($url);

            $dom = HtmlDomParser::str_get_html($html);

            foreach ($dom->findMulti("article.msg") as $postNode) {
                $author = $postNode->findOneOrFalse(".msg_body div.sign > a");

                if ($author !== false) {
                    $author = $author->text();
                } else {
                    continue;
                }

                $author = MailHeaderProcessor::sanitizeNonLatinAddress($author);

                $created = $this->getPostDate($postNode);

                $postId = $threadId . "#" . $this->getPostId($postNode);
                $this->threadMessageMap[$postId] = true;

                $isStartPost = $postNode->findOneOrFalse(".msg_body > footer > div.sign > a");
                if ($isStartPost) {
                    $postId = $threadId;
                }

                if ($rootMessageId === null) {
                    $rootMessageId = $threadId;
                    $postId = $rootMessageId;
                    $this->setRootMessage($threadId, $rootMessageId);
                }
                $parent = $this->getParent($postNode, $rootMessageId);

                $postText = $this->getPostText($postNode);
                $title = $this->getPostTitle($postNode, $postText);

                $msg = new SerializationMessage(
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
            $nextPage = $nextPage->count() ? $nextPage->offsetGet($nextPage->count() - 1) : false;

            if (($nextPage !== null) and strstr($nextPage->text(), "→")) {
                $url = URLProcessor::getNodeBaseURI($dom, $url) . $nextPage->getAttribute("href");
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
    protected function getPostTitle(SimpleHtmlDomInterface $post, string $bodyText): string
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

    protected function getParent(SimpleHtmlDomInterface $node, string $defaultParent): string
    {
        $parent = $node->findOneOrFalse("a");
        $parentId = null;

        if ($parent) {
            if (
                preg_match(
                    '/(forum|news)\/([^\/]+)\/([0-9]+)\?cid=([0-9]+)/',
                    $parent->getAttribute("href"),
                    $matches
                )
            ) {
                $parentId =  $matches[1] . "_" . $matches[2] . "_" . $matches[3] . "#" . $matches[4];
            }
        }

        // prevent from linking to other threads
        if (empty($this->threadMessageMap[$parentId])) {
            $parentId = $defaultParent;
        }

        return $parentId;
    }

    /**
     * Try to extract post date. We have at least 2 html formats here. For old
     * topics and new ones. Probably more, so fallback to current date.
     */
    protected function getPostDate(SimpleHtmlDomInterface $post): \DateTime
    {
        $dateWidget = $post->find("div.sign > time")[0];

        if ($dateWidget !== null) {
            return new \DateTime($dateWidget->getAttribute('datetime'));
        } else {
            return new \DateTime();
        }
    }


    protected function getPostId(SimpleHtmlDomInterface $post): string
    {
        preg_match('/\-([0-9]+)/', $post->getAttribute("id"), $id);
        return $id[1];
    }
}
