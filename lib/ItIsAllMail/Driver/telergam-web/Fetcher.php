<?php

namespace ItIsAllMail\Driver;

use ItIsAllMail\Interfaces\FetchDriverInterface;
use ItIsAllMail\DriverCommon\AbstractFetcherDriver;
use ItIsAllMail\Config\FetcherSourceConfig;
use ItIsAllMail\HtmlToText;
use ItIsAllMail\CoreTypes\SerializationMessage;
use ItIsAllMail\Utils\Browser;
use ItIsAllMail\Utils\Debug;
use ItIsAllMail\Utils\URLProcessor;
use ItIsAllMail\Utils\MailHeaderProcessor;
use voku\helper\HtmlDomParser;
use voku\helper\SimpleHtmlDom;

class TelegramWebFetcher extends AbstractFetcherDriver implements FetchDriverInterface
{
    protected $driverCode = "t.me";
    protected $defaultCommentDate;

    public function __construct(array $appConfig, array $opts)
    {
        parent::__construct($appConfig, $opts);

        $this->defaultCommentDate = new \DateTime('2000-01-01');
    }

    /**
     * Return array of all posts in thread, including original article
     */
    public function getPosts(array $source): array
    {
        $sourceURL = URLProcessor::normalizeStartURL($source["url"]);

        $sourceConfig = new FetcherSourceConfig($this->appConfig, $this, $source);

        $html = Browser::getAsString($sourceURL);

        $dom = HtmlDomParser::str_get_html($html);

        $threadId = $this->getThreadId($sourceURL);

        $topPostContainer = $dom->findOneOrFalse("div.tgme_channel_info");

        if (! $topPostContainer) {
            throw new \Exception("Seems {$sourceURL} is closed for web view");
        }

        $posts = [
            $this->getChannelTopPost($topPostContainer, $sourceURL)
        ];

        foreach ($dom->findMulti("div.tgme_widget_message") as $postNode) {
            $author = $postNode->findOne(".tgme_widget_message_owner_name")->text();
            $author = MailHeaderProcessor::sanitizeNonLatinAddress($author);

            $postText = $this->getPostText($postNode);
            $title = $this->getPostTitle($postText);

            $parent = $threadId;

            $created = $this->getCreated($postNode);

            $postId = $this->getPostId($postNode);

            $msg = new SerializationMessage(
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
                $this->processPostAttachements($postNode, $msg, $sourceConfig);
            }

            $msg->setBody(
                $msg->getBody() . "\n\n[ " .
                $this->getPostUrl($postNode) .
                " ]\n"
            );

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

    public function getChannelTopPost(SimpleHtmlDom $postNode, string $sourceURL): SerializationMessage
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

        $msg = new SerializationMessage(
            [
                "from" => $author . "@" . $this->getCode(),
                "subject" => $title,
                "parent" => null,
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
            $rawHtml = $textNode->innerHtml();

            // fix emojis and other underscored text
            $rawHtml = preg_replace('/(<i [^>]*>)|(<\/i>)/', '', $rawHtml);

            return (new HtmlToText($rawHtml))->getText();
        } else {
            return "";
        }
    }

    public function getPostTitle(string $postText): string
    {
        $title = preg_replace('/((\r\n)+)|(\n+)/', ' ', $postText);
        $title = preg_replace('/\[http.+\]/', '', $title);

        return $title;
    }

    protected function getPostId(SimpleHtmlDom $post): string
    {
        return preg_replace('/[^A-Za-z0-9\-_\.]/', '_', $post->getAttribute("data-post"));
    }

    protected function getThreadId(string $url): string
    {
        preg_match('/t\.me\/s\/(.+)$/', $url, $id);
        return $id[1];
    }

    protected function getCreated(SimpleHtmlDom $post): \DateTime
    {
        return new \DateTime(
            $post->findOneOrFalse("time.time")->getAttribute('datetime')
        );
    }

    protected function processPostAttachements(
        SimpleHtmlDom $postNode,
        SerializationMessage $msg,
        FetcherSourceConfig $sourceConfig
    ): void {
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

                $pathParts = pathinfo($attachementURL);
                $attachementTitle = "attachement_" . $attachementsCount . "." . $pathParts["extension"];

                if ($sourceConfig->getOpt('download_attachements') !== "none") {
                    Debug::debug("Downloading attachement: " . $attachementURL);

                    $msg->addAttachement(
                        $attachementTitle,
                        Browser::getAsString($attachementURL)
                    );
                }

                $msg->addAttachementLink($attachementTitle, $attachementURL);
            }
        }

        $video = $postNode->findOneOrFalse(".tgme_widget_message_video_wrap");
        if ($video) {
            $videoNotUpportedInBrowser = $video->findOneOrFalse(".message_media_not_supported");
            $videoURL = $videoNotUpportedInBrowser ? "Unsupported in browser" : $this->getPostUrl($postNode);
            $msg->addAttachementLink("video", $videoURL);
            $msg->setBody(
                $msg->getBody() . "\n[ VIDEO {$videoURL} ]\n"
            );
        }
    }

    /**
     * It assumes, that telegram message ID just increment, so if checked ID
     * is greater, than last saved one, the message was already downloaded,
     * even if it doesn't exists in mailbox
     */
    protected function messageWithGivenIdAlreadyDownloaded(string $id): bool
    {
        return $this->getMailbox()->msgExists($id);
    }

    protected function getPostUrl(SimpleHtmlDom $postNode) : string
    {
        return $postNode->findOneOrFalse("a.tgme_widget_message_date")->getAttribute("href");
    }
}
