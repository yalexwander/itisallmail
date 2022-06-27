<?php

namespace ItIsAllMail;

use ItIsAllMail\Message;
use ItIsAllMail\Factory\PosterDriverFactory;

class PostingQueue {

    protected $appConfig;

    public function __construct(array $appConfig)
    {
        $this->appConfig = $appConfig;
    }

    public function add(array $msg) : void {
    }

    public function send(array $msg) : array {
        $posterDriverFactory = new PosterDriverFactory($this->appConfig);
        $poster = $posterDriverFactory->findPoster($msg);

        // $result = $poster->post($msg);

        return [];
    }
    
}
