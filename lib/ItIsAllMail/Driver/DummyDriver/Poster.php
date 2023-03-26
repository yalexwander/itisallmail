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

require_once(__DIR__ . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . "HabrAPI.php");

class HabrPoster extends AbstractPosterDriver implements PosterDriverInterface
{

    protected array $appConfig;
    protected PosterConfig $posterConfig;
    protected string $driverCode = "dummy_post";

    public function post(ParsedMessage $msg, Source $source = null, array $opts = []): array
    {
        $posterConfig = new PosterConfig($this->appConfig, $source, $this);

        return [
            "newId"  => "post id that assigned by the externa service",
            "status" => "result of posting - ok or fail",
            "error" => "error description if needed",
            "response" => "returned data structure"
        ];
    }
}
