<?php

namespace ItIsAllMail;

use ItIsAllMail\CoreTypes\SerializationMessage;
use ItIsAllMail\Factory\PosterDriverFactory;
use ItIsAllMail\CoreTypes\ParsedMessage;

class PostingQueue
{
    protected array $appConfig;

    public function __construct(array $appConfig)
    {
        $this->appConfig = $appConfig;
    }

    public function add(ParsedMessage $msg): void
    {
    }

    public function send(ParsedMessage $msg): array
    {
        $posterDriverFactory = new PosterDriverFactory($this->appConfig);
        $poster = $posterDriverFactory->findPoster($msg);

        // $result = $poster->post($msg);

        return [];
    }
}
