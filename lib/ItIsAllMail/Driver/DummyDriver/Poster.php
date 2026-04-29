<?php

namespace ItIsAllMail\Driver;

use ItIsAllMail\Utils\Browser;
use ItIsAllMail\Utils\Debug;
use ItIsAllMail\Utils\URLProcessor;
use ItIsAllMail\Interfaces\PosterDriverInterface;
use ItIsAllMail\DriverCommon\AbstractPosterDriver;
use ItIsAllMail\PostingQueue;
use ItIsAllMail\Config\PosterConfig;
use ItIsAllMail\CoreTypes\ParsedMessage;
use ItIsAllMail\CoreTypes\Source;
use ItIsAllMail\CoreTypes\PostResult;
use ItIsAllMail\CoreTypes\PostStatus;


class DummyPoster extends AbstractPosterDriver implements PosterDriverInterface
{
    protected array $appConfig;
    protected PosterConfig $posterConfig;
    protected string $driverCode = "dummy_post";


    public function canProcessMessage(ParsedMessage $msg): bool {
        if (preg_match('/@dummy$/', $msg["headers"]["to"])) {
            return true;
        }

        return false;
    }    

    public function post(ParsedMessage $msg, Source $source = null, array $opts = []): PostResult
    {
        $posterConfig = new PosterConfig($this->appConfig, $source, $this);

        $result = new PostResult(PostStatus::Ok, 1000);

        if (str_contains($msg['body'], 'send_must_fail')) {
            $result = new PostResult(PostStatus::Fail);
        }

        return $result;
    }
}
