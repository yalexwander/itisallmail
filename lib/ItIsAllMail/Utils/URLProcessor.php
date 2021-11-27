<?php

/**
 * Typical functions for preprocessing URLs from sources
 */

namespace ItIsAllMail\Utils;

use voku\helper\HtmlDomParser;

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

    /**
     * Tries to get base URL by $pageDom and then by the $url. Always returns URL without trailing slash.
     * When $preferHost is set to true, the shortest base URL is choosen.
     */
    public static function getNodeBaseURI(HtmlDomParser $pageDom, string $url, bool $preferHost = true): string
    {
        $baseTag = $pageDom->findOneOrFalse("base");
        $baseURL = "";

        if ($baseTag) {
            $baseURL = $baseTag->getAttribute("href");
        } else {
            $parsedURL = parse_url($url);
            $baseURL = $parsedURL["scheme"] . "://" . $parsedURL["host"];

            if (! $preferHost) {
                $baseURL .= $parsedURL["path"];
            }
        }

        return $baseURL;
    }
}
