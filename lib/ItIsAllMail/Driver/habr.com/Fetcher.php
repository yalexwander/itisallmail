<?php

namespace ItIsAllMail\Driver;

use ItIsAllMail\Interfaces\FetchDriverInterface;
use ItIsAllMail\DriverCommon\AbstractFetcherDriver;
use ItIsAllMail\Factory\CatalogDriverFactory;
use ItIsAllMail\HtmlToText;
use ItIsAllMail\CoreTypes\SerializationMessage;
use ItIsAllMail\Utils\Browser;
use ItIsAllMail\Utils\Debug;
use ItIsAllMail\Utils\URLProcessor;
use voku\helper\HtmlDomParser;
use voku\helper\SimpleHtmlDom;
use voku\helper\SimpleHtmlDomInterface;
use ItIsAllMail\CoreTypes\Source;

require_once(__DIR__ . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . "HabrDateParser.php");

class HabrFetcherDriver extends AbstractFetcherDriver implements FetchDriverInterface
{
    protected string $driverCode = "habr.com";
    protected \DateTimeInterface $defaultCommentDate;

    public function __construct(array $appConfig, array $opts)
    {
        parent::__construct($appConfig, $opts);

        $this->defaultCommentDate = new \DateTime('2000-01-01');
    }

    /**
     * Return array of all posts in thread, including original article
     */
    public function getPosts(Source $source): array
    {
        $posts = [];

        if ($this->isCatalogQuery($source)) {
            $catalog = (new CatalogDriverFactory($this->appConfig))
                ->getCatalogDriver($source["url"], [ 'source' => $source]);

            $posts = $catalog->queryCatalog($source["url"]);
        } else {
            if (null === $this->getLastURLVisited($source["url"])) {
                $posts[] = $this->getFirstPost($source);
            }

            $posts = array_merge($posts, $this->getComments($source));
        }

        return $posts;
    }
    /**
     * Make post from the article itself
     */
    public function getFirstPost(Source $source): SerializationMessage
    {
        $html = Browser::getAsString($source["url"]);
        Debug::debug("Downloaded post page");
        $dom = HtmlDomParser::str_get_html($html);

        $postContainer = $dom->findOne(".tm-article-presenter__body");
        $author = $postContainer->findOne("a.tm-user-info__userpic")->getAttribute("title");
        $postText = $this->postToText(
            $postContainer->findOne(".tm-article-body")
        );
        $postDate = $postContainer->findOne(".tm-article-datetime-published > time")->getAttribute("datetime");
        $postTitle = $postContainer->findOne(".tm-article-snippet__title")->text();
        $postId = $this->getThreadIdFromURL($source["url"]);

        $this->defaultCommentDate = HabrDateParser::parseArticleDate($postDate);

        $msg = new SerializationMessage([
            "from" => $author . "@" . $this->getCode(),
            "subject" => $postTitle,
            "parent" => null,
            "created" => $this->defaultCommentDate,
            "id" => $postId . "@" . $this->getCode(),
            "body" => $postText,
            "thread" => $postId . "@" . $this->getCode(),
            "uri" => $source["url"]
        ]);

        if (! empty($html)) {
            $this->setLastURLVisited($source["url"], $source["url"]);
        }

        return $msg;
    }

    /**
     * Parse comments to array
     */
    public function getComments(Source $source): array
    {
        $commentsURL = URLProcessor::normalizeStartURL($source["url"]);
        $commentsURL .= "comments/";

        $threadId = $this->getThreadIdFromURL($commentsURL);

        $html = Browser::getAsString($commentsURL);
        Debug::debug("Downloaded comments page $commentsURL");
        $dom = HtmlDomParser::str_get_html($html);

        $defaultCommentDate = new \DateTime('2000-01-01');
        $comments = [];
        $visitedMap = [];
        foreach ($dom->findMulti("article.tm-comment-thread__comment") as $node) {
            $commentTextWidget = $node->findOneOrFalse(".tm-comment__body-content");

            $postId = $this->getCommentIdFromLink(
                $node->findOne("a")->getAttribute("name")
            );

            if (isset($visitedMap[$postId])) {
                continue;
            }
            $visitedMap[$postId] = true;

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
                $commentDate = HabrDateParser::parseCommentDate(
                    $node->findOne("time")->getAttribute("datetime")
                );
            }

            $score = $node->findOne("div.tm-votes-meter title")->text();
            if (preg_match('/↑([0-9]+).*?↓([0-9]+)/u', $score, $score)) {
                $score = [ intval($score[1]), intval($score[2]) ];
            } else {
                $score = null;
            }

            $comments[] = new SerializationMessage([
                "from" => $commentAuthor . "@" . $this->getCode(),
                "subject" => $commentTitle,
                "parent" => $parent . "@" . $this->getCode(),
                "created" => $commentDate,
                "id" => $postId . "@" . $this->getCode(),
                "body" => $commentBody,
                "thread" => $threadId  . "@" . $this->getCode(),
                "uri" => $source["url"] . "#comment_" . $postId,
                "score" => $score
            ]);
        }

        return $comments;
    }

    /**
     * Convert to text readable by CLI mail client
     */
    protected function postToText(SimpleHtmlDomInterface $node): string
    {
        return (new HtmlToText($node->outerHtml()))->getText();
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

    protected function isCatalogQuery(Source $source): bool
    {
        if (preg_match('/\/[0-9]+\/$/', $source["url"])) {
            return false;
        } else {
            return true;
        }
    }
}
