<?php

/**
 * Typical functions for preprocessing URLs from sources
 */

namespace ItIsAllMail\Utils;

class URLProcessor
{
    /**
     * Ensure that URL ends with slah if needed
     */
    public static function normalizeStartURL(string $url, bool $trailingSlashRequred = true): string
    {
        $normalizedURL = $url;
        if (strstr($url, "/") == (strlen($url) - 1)) {
            $normalizedURL .= "/";
        }
        return $normalizedURL;
    }
}
