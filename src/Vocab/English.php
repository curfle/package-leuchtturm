<?php

namespace Leuchtturm\Vocab;

class English implements Vocab
{

    public function pluralize(string $word): string
    {
        if (str_ends_with($word, "s")) return substr($word, 0, -1) . "es";
        if (str_ends_with($word, "ss")) return substr($word, 0, -2) . "es";
        if (str_ends_with($word, "sh")) return substr($word, 0, -2) . "es";
        if (str_ends_with($word, "ch")) return substr($word, 0, -2) . "es";
        if (str_ends_with($word, "x")) return substr($word, 0, -1) . "es";
        if (str_ends_with($word, "z")) return substr($word, 0, -1) . "es";
        if (str_ends_with($word, "f")) return substr($word, 0, -1) . "ves";
        if (str_ends_with($word, "fe")) return substr($word, 0, -2) . "ves";
        if (str_ends_with($word, "ay")) return substr($word, 0, 0) . "s";
        if (str_ends_with($word, "ey")) return substr($word, 0, 0) . "s";
        if (str_ends_with($word, "iy")) return substr($word, 0, 0) . "s";
        if (str_ends_with($word, "oy")) return substr($word, 0, 0) . "s";
        if (str_ends_with($word, "uy")) return substr($word, 0, 0) . "s";
        if (str_ends_with($word, "y")) return substr($word, 0, -1) . "ies";
        if (str_ends_with($word, "o")) return substr($word, 0, 0) . "es";
        if (str_ends_with($word, "us")) return substr($word, 0, -2) . "i";
        if (str_ends_with($word, "is")) return substr($word, 0, -2) . "es";
        if (str_ends_with($word, "on")) return substr($word, 0, -2) . "a";
        return $word . "s";
    }

    public function operationC(string $word): string
    {
        return "create" . ucwords($word);
    }

    public function operationR(string $word): string
    {
        return $word;
    }

    public function operationU(string $word): string
    {
        return "update" . ucwords($word);
    }

    public function operationD(string $word): string
    {
        return "delete" . ucwords($word);
    }

    public function operationA(string $word): string
    {
        return "all" . ucwords($this->pluralize($word));
    }
}