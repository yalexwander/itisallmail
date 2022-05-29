<?php

namespace ItIsAllMail\Interfaces;

interface CatalogDriverInterface
{
    public function queryCatalog(string $catalogType, array $opts = []) : array;

    public function canHandleQuery(string $query, array $opts = []) : bool;

    /**
     * This code not required to exactly match the site name driver created
     * for. Because catalog is only for internal usage of IAM, not for
     * creating for direct replies. You can have different codes for different
     * catalog types for example "users.reddit.com" or "posts.reddit.com"
     */
    public function getCode(string $catalogType = null): string;
}
