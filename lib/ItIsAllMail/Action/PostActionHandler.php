<?php

namespace ItIsAllMail\Action;

use ItIsAllMail\PosterDriverFactory;
use ItIsAllMail\PostingQueue;

class PostActionHandler {
    protected $config;
    protected $posterDriverFactory;

    public function __construct($config)
    {
        $this->config = $config;
        $this->posterDriverFactory = new PosterDriverFactory($config);
    }

    public function process(string $arg, array $msg) : int {
        $poster = $this->posterDriverFactory->findPoster($msg);

        $result = 1;
        if (! empty($this->config["use_posting_queue"])) {
            $queue = new PostingQueue($this->config);
            $result = $queue->add($msg);
        } else {       
            $result = $poster->post($msg, [ "arg" => $arg ]);
        }

        exit(1);
        return $result;
    }

}
