<?php

namespace ItIsAllMail\Driver;

use ItIsAllMail\Interfaces\FetchDriverInterface;
use ItIsAllMail\DriverCommon\AbstractFetcherDriver;
use ItIsAllMail\HtmlToText;
use ItIsAllMail\SerializationMessage;
use ItIsAllMail\Utils\Debug;
use ItIsAllMail\Utils\Browser;
use ItIsAllMail\Utils\MailHeaderProcessor;
use ItIsAllMail\Utils\URLProcessor;
use voku\helper\HtmlDomParser;
use voku\helper\SimpleHtmlDom;
use voku\helper\SimpleHtmlDomInterface;

class ForumhouseRuFetcher extends AbstractFetcherDriver implements FetchDriverInterface
{
    protected $crawler;
    protected $driverCode = "forumhouse.ru";

    /**
     * Return array of all posts in thread, including original article
     */
    public function getPosts(array $source): array
    {
        $posts = [];

        $startUrl = URLProcessor::normalizeStartURL($source["url"]);
        $threadId = $this->getThreadIdFromURL($startUrl);
        $rootMessageId = $this->getRootMessage($threadId);

        $url = $this->getLastURLVisited($threadId) ?? $startUrl;

        while ($url) {
            Debug::log("Processing $url");

            $html = Browser::getAsString($url);
            $dom = HtmlDomParser::str_get_html($html);

            foreach ($dom->findMulti("li.message") as $post) {
                $author = $post->findOneOrFalse(".userText");
                if ($author) {
                    $author = $author->text();
                } else {
                    continue;
                }

                $author = MailHeaderProcessor::sanitizeNonLatinAddress($author);

                $postId = $threadId . "#" . substr($post->getAttribute("id"), 5);

                // current post becomes root only if we didn't find one during previos fetches
                if ($rootMessageId === null) {
                    $rootMessageId = $threadId;
                    $postId = $rootMessageId;
                    $this->setRootMessage($threadId, $rootMessageId);
                }

                $parent = $this->getParent($post, $rootMessageId);
                $created = $this->extractDateFromPost($post);

                $postText = $this->postToText($post->findOne(".messageText"));

                $title = $this->getPostTitle($postText);

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

                foreach ($post->findMulti("ul.attachmentList a > img") as $attachement) {
                    $attachementURL =
                        URLProcessor::getNodeBaseURI($dom, $url) . "/" . $attachement->getAttribute("src");
                    Debug::debug("Downloading attachement: $attachementURL");
                    $msg->addAttachement(
                        $attachement->getAttribute("alt"),
                        Browser::getAsString($attachementURL)
                    );
                }

                $posts[] = $msg;
            }

            $nextPage = $dom->findMulti('div.pageNavLinkGroup a.text');
            $nextPage = $nextPage->count() ? $nextPage->offsetGet($nextPage->count() - 1) : false;

            if ($nextPage and strstr($nextPage->text(), "Вперёд")) {
                $url = URLProcessor::getNodeBaseURI($dom, $url) . "/" . $nextPage->getAttribute("href");
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
    protected function postToText(SimpleHtmlDomInterface $node): string
    {
        $text = (new HtmlToText($node->innerHtml()))->getText();
        $text = preg_replace('/ \[https\:\/\/www\.forumhouse\.ru\/members\/[0-9]+\/]/', '', $text);

        return $text;
    }

    protected function getThreadIdFromURL(string $url): string
    {
        $id = null;
        preg_match("/\/([0-9]+)\/(comments)*/", $url, $id);
        return $id[1];
    }

    protected function getParent(SimpleHtmlDomInterface $node, string $defaultParent): string
    {
        $parent = $node->findOneOrFalse(".SelectQuoteContainer a.AttributionLink");
        if ($parent) {
            if (
                preg_match(
                    '/threads\/([0-9]+)\/[a-z0-9\-]*\#post-([0-9]+)/',
                    $parent->getAttribute("href"),
                    $matches
                )
            ) {
                return $matches[1] . "#" . $matches[2];
            }
        }

        return $defaultParent;
    }

    /**
     * Generate something can be put into "Subject" field
     */
    protected function getPostTitle(string $post): string
    {
        $title = "";
        $lines = explode("\n", $post);
        foreach ($lines as $line) {
            // skip quote headers
            if (strpos($line, "сказал(а):")) {
                continue;
            }

            // skip quoted lines
            if (strpos($line, ">") !== 0) {
                $title .= $line . " ";
            }

            if (mb_strlen($title) > 512) {
                break;
            }
        }

        return $title;
    }

    /**
     * Try to extract post date. We have at least 2 html formats here. For old
     * topics and new ones. Probably more, so fallback to current date.
     */
    protected function extractDateFromPost(SimpleHtmlDomInterface $post): \DateTime
    {
        $dateWidget = $post->findOneOrFalse("a.datePermalink>span");
        $dateString = "";
        $postDate = null;

        if ($dateWidget) {
            $dateString = $dateWidget->getAttribute('title');
        } else {
            $dateWidget = $post->findOne("a.datePermalink>abbr");
            $dateString = $dateWidget->innerHtml();
        }

        $dateString = preg_replace('/в /', "", $dateString);
        $postDate = \DateTime::createFromFormat("d.m.y H:i", $dateString);

        return $postDate;
    }
}
