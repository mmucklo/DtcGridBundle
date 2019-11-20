<?php

namespace Dtc\GridBundle\Util;

class CamelCase
{
    /**
     * @param string $str
     *
     * @return string
     */
    public static function fromCamelCase($str)
    {
        $value = preg_replace_callback('/([A-Z])/', function ($str) { return ' '.$str[0]; }, $str);
        $value = ucfirst($value);

        return trim($value);
    }
}
