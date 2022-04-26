<?php

namespace ItIsAllMail\Driver;

use ItIsAllMail\Interfaces\FetchDriverInterface;
use ItIsAllMail\AbstractFetcherDriver;
use ItIsAllMail\HtmlToText;
use ItIsAllMail\Message;
use ItIsAllMail\Utils\Browser;
use ItIsAllMail\Utils\Debug;
use ItIsAllMail\Utils\URLProcessor;
use ItIsAllMail\Utils\MailHeaderProcessor;
use voku\helper\HtmlDomParser;
use voku\helper\SimpleHtmlDom;

class TelegramChannelFetcher extends AbstractFetcherDriver implements FetchDriverInterface
{
    protected $driverCode = "t.me";
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
        $sourceURL = URLProcessor::normalizeStartURL($source["url"]);

        $html = Browser::getAsString($sourceURL);

        $dom = HtmlDomParser::str_get_html($html);

        $threadId = $this->getThreadId($sourceURL);

        $posts = [
            $this->getChannelTopPost($dom->findOneOrFalse("div.tgme_channel_info"), $sourceURL)
        ];

        foreach ($dom->findMulti("div.tgme_widget_message") as $postNode) {
            $author = $postNode->findOneOrFalse(".tgme_widget_message_owner_name")->text();
            $author = MailHeaderProcessor::sanitizeCyrillicAddress($author);

            $postText = $this->getPostText($postNode);

            $postText .= "\n\n[ ";
            $postText .= $postNode->findOneOrFalse("a.tgme_widget_message_date")->getAttribute("href");
            $postText .= " ]\n";

            $title = $this->getPostTitle($postNode, $postText);

            $parent = $threadId;

            $created = $this->getCreated($postNode);

            $postId = $this->getPostId($postNode);

            $msg = new Message(
                [
                    "from" => $author . "@" . $this->getCode(),
                    "subject" => $title,
                    "parent" => $parent . "@" . $this->getCode(),
                    "created" => $created,
                    "id" => $postId . "@" . $this->getCode(),
                    "body" => $postText,
                    "thread" => $postId . "@" . $this->getCode(),
                ]
            );

            if (! $this->messageWithGivenIdAlreadyDownloaded($postId . "@" . $this->getCode())) {
                $this->processPostAttachements($postNode, $msg);
            }

            $posts[] = $msg;
        }

        return $posts;
    }

    /**
     * Convert to text readable by CLI mail client
     */
    protected function postToText(SimpleHtmlDom $node): string
    {
        return (new HtmlToText($node->outerHtml()))->getText();
    }

    public function getChannelTopPost(SimpleHtmlDom $postNode, string $sourceURL): Message
    {
        $author = preg_replace(
            '/@/',
            "",
            $postNode->findOneOrFalse("div.tgme_channel_info_header_username")->text()
        );

        $postText = $this->postToText($postNode);
        $title = $this->postToText($postNode->findOneOrFalse(".tgme_channel_info_header_title"));

        $created = $this->defaultCommentDate;

        $postId = $this->getThreadId($sourceURL);

        $parent = $postId;

        $msg = new Message(
            [
                "from" => $author . "@" . $this->getCode(),
                "subject" => $title,
                "parent" => $parent . "@" . $this->getCode(),
                "created" => $created,
                "id" => $postId . "@" . $this->getCode(),
                "body" => $postText,
                "thread" => $parent . "@" . $this->getCode()
            ]
        );

        return $msg;
    }

    public function getPostText($node): string
    {
        $textNode = $node->findOneOrFalse("div.tgme_widget_message_text");
        if ($textNode) {
            return (new HtmlToText($textNode->innerHtml()))->getText();
        }
        else {
            return "";
        }
    }

    public function getPostTitle($node): string
    {
        $title = preg_replace('/\n/', ' ', $this->getPostText($node));
        return $title;
    }

    protected function getPostId(SimpleHtmlDom $post): string
    {
        return preg_replace('/[^A-Za-z0-9\-_\.]/', '_', $post->getAttribute("data-post"));
    }

    protected function getThreadId(string $url): string
    {
        preg_match('/t.me\/s\/(.+)$/', $url, $id);
        return $id[1];
    }

    protected function getCreated(SimpleHtmlDom $post): \DateTime
    {
        return new \DateTime(
            $post->findOneOrFalse("time.time")->getAttribute('datetime')
        );
    }

    protected function processPostAttachements(SimpleHtmlDom $postNode, Message $msg)
    {
        $attachementsCount = 0;
        foreach ($postNode->findMulti(".tgme_widget_message_photo_wrap") as $attachementNode) {
            $attachementURL = $attachementNode->getAttribute('style');
            preg_match(
                '/background-image:url\(\'(.+)\'\)/',
                $attachementURL,
                $attachementURL
            );

            if (count($attachementURL)) {
                $attachementURL = $attachementURL[1];
                $attachementsCount++;
                Debug::debug("Downloading attachement: " . $attachementURL);

                $pathParts = pathinfo($attachementURL);

                $msg->addAttachement(
                    "attachement_" . $attachementsCount . "." . $pathParts["extension"],
                    Browser::getAsString($attachementURL)
                );
            }

        }
    }

}
