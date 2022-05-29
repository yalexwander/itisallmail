<?php

namespace ItIsAllMail;

class AbstractCatalogDriver
{
    protected $config;
    
    public function canHandleQuery(string $query, array $opts = []) : bool
    {
        return false;
    }
}
