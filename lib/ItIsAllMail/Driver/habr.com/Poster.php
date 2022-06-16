<?php

namespace ItIsAllMail\Driver;

use ItIsAllMail\Utils\Browser;
use ItIsAllMail\Utils\Debug;
use ItIsAllMail\Utils\URLProcessor;
use ItIsAllMail\Interfaces\PosterDriverInterface;
use ItIsAllMail\DriverCommon\AbstractPosterDriver;
use ItIsAllMail\PostingQueue;
use ItIsAllMail\Config\FetcherSourceConfig;
use ItIsAllMail\Factory\FetcherDriverFactory;


require_once(__DIR__ . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . "HabrAPI.php");

class HabrPoster extends AbstractPosterDriver implements PosterDriverInterface {

    protected $appConfig;
    protected $posterConfig;
    protected $driverCode = "habr.com";

    public function canProcessMessage(array $msg): bool {
        $toHeader = $msg["referenced_message"]["headers"]["to"] ?? $msg["headers"]["to"];

        if (preg_match('/@habr.com$/', $toHeader)) {
            return true;
        }

        return false;
    }

    public function post(array $msg, array $source = null, array $opts = []) : array {
        $this->assertEmptyMessage($msg);

        $fetcherDriver = (new FetcherDriverFactory($this->appConfig))->getFetchDriverForSource($source);
        $fetcherConfig = new FetcherSourceConfig($this->appConfig, $fetcherDriver, $source);

        $api = new HabrAPI($fetcherConfig->getOpt("poster_credentials"));

        if (! $api->auth()) {
            throw new \Exception("Failed to auth to post");
        }

        $article = $msg["referenced_message"]["headers"]["to"] ?? $msg["headers"]["to"];
        preg_match('/([0-9]+)@/', $article, $article);
        $article = $article[1];

        $parent = $msg["referenced_message"]["headers"]["message-id"] ?? $msg["headers"]["message-id"];
        preg_match('/([0-9]+)@/', $parent, $parent);
        $parent = $parent[1];

        if ($parent === $article) {
            $parent = null;
        }

        $comment = $api->sendComment(
            [
                "article" => $article,
                "parent" => $parent,
                "text" => $msg["body"],
                'source' => $source
            ]
        );

        return [
            "newId"  => $comment["data"]["id"],
            "status" => 1,
            "error" => "",
            "response" => $comment
        ];
    }
}