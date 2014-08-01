<?php
namespace Core\Models\Generator;
use Core\Cunity;

/**
 * Class Url
 * @package Core\Models\Generator
 */
class Url {

    /**
     * @param $urlString
     * @return string
     * @throws \Exception
     */
    public static function convertUrl($urlString) {
        if (Cunity::get("mod_rewrite")) { //if mod rewrite is enabled!
            $parsedUrl = parse_url($urlString);
            parse_str($parsedUrl['query'], $parsedQuery);                        
            return  Cunity::get("settings")->getSetting("core.siteurl").implode('/', $parsedQuery);
        } else
            return Cunity::get("settings")->getSetting("core.siteurl").$urlString;
    }    
}
