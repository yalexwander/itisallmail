<?php

namespace ItIsAllMail\Driver;

use ItIsAllMail\Utils\Browser;
use ItIsAllMail\Utils\Debug;
use ItIsAllMail\Utils\URLProcessor;
use ItIsAllMail\Interfaces\PosterDriverInterface;
use ItIsAllMail\DriverCommon\AbstractPosterDriver;
use ItIsAllMail\PostingQueue;

require_once(__DIR__ . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . "HabrAuth.php");

class HabrPoster extends AbstractPosterDriver implements PosterDriverInterface {

    protected $config;
    protected $driverCode = "habr.com";

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function canProcessMessage(array $msg): bool {
        if (preg_match('/@habr.com$/',$msg["headers"]["to"])) {
            return true;
        }

        return false;
    }


    public function post(array $msg, array $opts = []) : array {
        
    }
}
