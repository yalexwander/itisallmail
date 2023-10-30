<?php

namespace ItIsAllMail\Interfaces;

use ItIsAllMail\SendMailProcessor;
use ItIsAllMail\CoreTypes\ParsedMessage;
use ItIsAllMail\CoreTypes\Source;
use ItIsAllMail\Config\PosterConfig;

interface PosterDriverInterface
{
    public function __construct(array $appConfig, array $driverPosterSectionConfig);

    /**
     * Must return true if you think your driver must handle given message
     */
    public function canProcessMessage(ParsedMessage $msg): bool;

    /**
     * Must return simple code like "habr.com" or "reddit.com", expecting it
     * to be appended as domain to each email. It actually must be a domain to
     * override symfony email validation constraint.
     */
    public function getCode(): string;

    /**
     * Posting to some resource. For now required parameters are something like:
     * @return array - can have keys:
     *  "newId"  => ID of newly created comment in service you post to. Nullable. It must be something short,
     *              like 12345 or new_post_77 etc.
     *  "status" =>
     *    1 - if sending was done.
     *    0 - if not
     *  "error" => Details of error if status = 0
     *  "response" => full service response if needed. Not mandatory at this point.
     *
     * The only "status" field is required
     */
    public function post(ParsedMessage $msg, Source $source = null, array $opts = []): array;

    /**
     * Execute all needed check before posting, to ensure data is correct. exit(1) in case of some problems
     */
    public function checkBeforePost(ParsedMessage $msg, Source $source = null, array $opts = []): void;

    /**
     * Option from "poster" section of "driver.cfg"
     */
    public function getOpt(string $key): mixed;
}
