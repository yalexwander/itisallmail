<?php

namespace ItIsAllMail\Interfaces;

use ItIsAllMail\Interfaces\HierarchicConfigInterface;

/**
 * Idea is to have ability to emit fetched messages to different destination that mailbox
 * For email-first orientation of ItIsAllMail, there are some methods you will probably stub.
 */

interface MessageStorageInterface
{
    public function __construct(HierarchicConfigInterface $sourceConfig);
    public function msgExists(string $id): bool;
    public function mergeMessages(array $messages): array;
    public function getLabel(): string;
}
