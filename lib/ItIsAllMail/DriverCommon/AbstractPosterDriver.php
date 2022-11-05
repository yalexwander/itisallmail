<?php

namespace ItIsAllMail\DriverCommon;

use ItIsAllMail\Interfaces\PosterDriverInterface;
use ItIsAllMail\Config\FetcherSourceConfig;
use ItIsAllMail\Factory\FetcherDriverFactory;
use ItIsAllMail\CoreTypes\ParsedMessage;
use ItIsAllMail\CoreTypes\Source;

class AbstractPosterDriver implements PosterDriverInterface
{

    protected $appConfig;
    protected $posterConfig;
    protected $code;

    public function __construct(array $appConfig, array $posterConfig)
    {
        $this->appConfig = $appConfig;
        $this->posterConfig = $posterConfig;
    }

    public function canProcessMessage(ParsedMessage $msg): bool
    {
        return false;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function post(ParsedMessage $msg, Source $source = null, array $opts = []): array
    {
        throw new \Exception("Not implemented");
    }

    /**
     * For handling situation when blank message was sent by mistake
     */
    protected function assertEmptyMessage(ParsedMessage $msg): void
    {
        if (empty($msg["body"])) {
            throw new \Exception("Can not send blank message");
        }
    }

    /**
     * For handling situation when message is uuencoded
     */
    protected function assertUUEncodedMessage(ParsedMessage $msg): void
    {
        if (preg_match('/=[A-Z0-9]{2}/', $msg["body"])) {
            throw new \Exception("Probably wrong UUE encoded message: {$msg["body"]}");
        }
    }

    public function checkBeforePost(ParsedMessage $msg, Source $source = null, array $opts = []): void
    {
        $this->assertEmptyMessage($msg);
        $this->assertUUEncodedMessage($msg);
    }

    public function getOpt(string $key)  /* : mixed */
    {
        return $this->posterConfig[$key] ?? null;
    }
}
