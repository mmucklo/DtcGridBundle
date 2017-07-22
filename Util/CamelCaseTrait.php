<?php

namespace Dtc\GridBundle\Util;

trait CamelCaseTrait
{
    /**
     * @param string $str
     *
     * @return string
     */
    protected function fromCamelCase($str)
    {
        $func = function ($str) {
            return ' '.$str[0];
        };

        $value = preg_replace_callback('/([A-Z])/', $func, $str);
        $value = ucfirst($value);

        return trim($value);
    }
}
