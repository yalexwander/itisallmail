<?php

/**
 * If resource is very closed for all sort of scripts, fetchers and spiders, curl-impersonated can be used. This is a wrapper for this CLI utility. It works only if you installed 
 */

namespace ItIsAllMail\Utils;

class CurlImpersonatedBrowser
{   
    public static function getAsString(string $url, array $headers = [], $curlBinary): ?string
    {
        $headersString = '';
        foreach ($headers as $hk => $hv) {
            if (strpos($hv, "\\'") !== false) {
                throw new \Exception("Check escaped single quotes in header $hk : $hv");
            }
            $headersString .= sprintf(' -H \'%s: %s\' ', $hk, preg_replace("/'/", "\\'", $hv));
        }
        $execString = $curlBinary . " '" . $url . "' " . $headersString;

        $fp = popen($execString, "r");
        if ($fp === false) {
            throw new \Exception("Failed to run curl");
        }

        $result = "";
        while (!feof($fp)) {
            $result .= fgets($fp, 4096);
        }
        pclose($fp);
        
        return $result;
    }
}
