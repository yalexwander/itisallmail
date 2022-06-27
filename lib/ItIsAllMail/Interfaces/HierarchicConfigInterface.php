<?php

namespace ItIsAllMail\Interfaces;

interface HierarchicConfigInterface
{
    /**
     * Returns the most suitable option value for given key. We assume we have
     * few sources of data in class implementing this interface
     */
    public function getOpt(string $key)  /* : mixed */;
}
