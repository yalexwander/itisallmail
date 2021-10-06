<?php

namespace ItIsAllMail\Driver\Habr;

use ItIsAllMail\DriverInterface;
use ItIsAllMail\HtmlToText;
use ItIsAllMail\Message;
use ItIsAllMail\Utils\Storage;
use ItIsAllMail\Utils\Debug;
use ItIsAllMail\Utils\MailHeaderProcessor;
use ItIsAllMail\Utils\URLProcessor;
use Symfony\Component\DomCrawler\Crawler;

class ForumhouseDriver implements DriverInterface
{
    protected $crawler;
    protected $driverCode = "forumhouse.ru";

    public function getCode(): string
    {
        return $this->driverCode;
    }

    public function matchURL(string $url): bool
    {
        if (preg_match("/" . $this->getCode() . "/", $url)) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Return array of all posts in thread, including original article
     */
    public function getPosts(array $source): array
    {
        $posts = [];

        $url = URLProcessor::normalizeStartURL($source["url"]);
        $topicPage = 0;
        $threadId = $this->getThreadIdFromURL($url);

        while ($url) {
            Debug::log("Processing $url");

            $html = file_get_contents($url);
            $dom = new Crawler($html);

            foreach ($dom->filter("li.message") as $postNode) {
                $post = new Crawler($postNode);

                $author = $post->filter(".userText")->first();
                if ($author->count()) {
                    $author = $author->text();
                } else {
                    continue;
                }

                $author = MailHeaderProcessor::sanitizeCyrillicAddress($author);

                $parent = $this->getParent($post, $threadId);
                $created = new \DateTime();

                $postId = "";
                if ($parent === $threadId) {
                    $postId = $threadId . "#" .
                        substr($post->filter("li.message")->first()->attr("id"), 5);
                } else {
                    $postId = $threadId . "#" . substr($post->attr("id"), 5);
                }

                $postText = $this->postToText($post->filter(".messageText")->first());
                $title = $this->getPostTitle($postText);

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

                foreach ($post->filter(".messageText a>img.bbCodeImage") as $attachement) {
                    $msg->addAttachement(
                        $attachement->getAttribute("alt"),
                        file_get_contents($attachement->baseURI . $attachement->getAttribute("src"))
                    );
                }

                $posts[] = $msg;
            }

            $nextPage = $dom->filter('div.pageNavLinkGroup a.text')->last();
            if ($nextPage and strstr($nextPage->text(), "Вперёд")) {
                $url = $nextPage->getNode(0)->baseURI . $nextPage->attr("href");
                Debug::debug("New url: $url");
            } else {
                $url = false;
            }
        }

        return $posts;
    }


    /**
     * Convert to text readable by CLI mail client
     */
    protected function postToText(Crawler $node): string
    {
        $text = (new HtmlToText($node->html()))->getText();
        $text = preg_replace('/ \[https\:\/\/www\.forumhouse\.ru\/members\/[0-9]+\/]/', '', $text);

        return $text;
    }

    /**
     * Parse habr.com date representation to DateTime
     */
    protected function parseArticleDate(string $rawDate): \DateTimeInterface
    {
        $preDate = substr($rawDate, 0, 19) . substr($rawDate, 23, 1);

        $finalDate = \DateTime::createFromFormat(\DateTime::ISO8601, $preDate);

        if (! $finalDate) {
            throw new \Exception("Failed to parse date $preDate");
        }

        return $finalDate;
    }

    protected function getThreadIdFromURL(string $url): string
    {
        $id = null;
        preg_match("/\/([0-9]+)\/(comments)*/", $url, $id);
        return $id[1];
    }

    protected function getParent(Crawler $node, string $defaultParent): string
    {
        $parent = $node->filter(".SelectQuoteContainer")->first();
        if ($parent->count()) {
            $parent = $parent->filter("a.AttributionLink");
            if ($parent->count()) {
                if (preg_match('/threads\/([0-9]+)\/[a-z0-9\-]*\#post-([0-9]+)/', $parent->attr("href"), $matches)) {
                    return $matches[1] . "#" . $matches[2];
                }
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

            if (strlen($title) > 512) {
                break;
            }
        }

        return $title;
    }
}
