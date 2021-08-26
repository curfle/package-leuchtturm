<?php

namespace Leuchtturm\Utilities;

class Str
{
    /**
     * Removes one or more substrings from a haystack.
     *
     * @param string|array $remove
     * @param string $haystack
     * @return string
     */
    public static function removeIn(string|array $remove, string $haystack) : string
    {
        if(!is_array($remove))
            $remove = [$remove];

        foreach($remove as $r){
            $haystack = str_replace($r, "", $haystack);
        }

        return $haystack;
    }
}