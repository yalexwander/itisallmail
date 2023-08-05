<?php

namespace ItIsAllMail\Driver;

use ItIsAllMail\Interfaces\FetchDriverInterface;
use ItIsAllMail\DriverCommon\AbstractFetcherDriver;
use ItIsAllMail\HtmlToText;
use ItIsAllMail\CoreTypes\SerializationMessage;
use ItIsAllMail\Utils\Debug;
use ItIsAllMail\Utils\Browser;
use ItIsAllMail\Utils\MailHeaderProcessor;
use ItIsAllMail\Utils\URLProcessor;
use voku\helper\HtmlDomParser;
use voku\helper\SimpleHtmlDom;
use voku\helper\SimpleHtmlDomInterface;
use ItIsAllMail\CoreTypes\Source;

class RSSFetcher extends AbstractFetcherDriver implements FetchDriverInterface
{
    protected string $driverCode = "rss";

    /**
     * Return array of all posts in thread, including original article
     */
    public function getPosts(Source $source): array
    {
        $data = simplexml_load_string(Browser::getAsString($source["url"]));
        $from = '"' . ($data->channel->title ?? "news") . '"' . " <rss@feed>";
        $thread = md5($source["url"])  . "@" . $this->getCode();

        $posts = [];
        $posts[] = new SerializationMessage([
            "from" => $from,
            "subject" => $from,
            "parent" => null,
            "created" => new \DateTime("1970-01-01"),
            "id" => $thread,
            "body" => $source["url"],
            "thread" => $thread,
            "uri" => $source["url"]
        ]);

        foreach ($data->channel->item as $item) {
            $body = "";
            foreach (explode("\n", $item->description) as $paragraph) {
                if (mb_strlen($paragraph)) {
                    $body .= (new HtmlToText($paragraph))->getText() . "\n\n";
                }
            }
            $body .= "\n\n[ " . $item->link . " ]";

            $posts[] = new SerializationMessage([
                "from" => $from,
                "subject" => $item->title,
                "parent" => $thread,
                "created" => new \DateTime($item->pubDate),
                "id" => md5($item->link) . "@" . $this->getCode(),
                "body" => $body,
                "thread" => $thread,
                "uri" => $item->link
            ]);
        }

        return $posts;
    }
}
