<?php

namespace ItIsAllMail\Interfaces;

interface CatalogDriverInterface
{
    public function queryCatalog(string $catalogType, array $opts);
}
