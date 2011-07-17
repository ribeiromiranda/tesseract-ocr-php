<?php
namespace TesseractOcr\Ccutil;

class Helpers {

    public static function ClipToRange(/* const T&  */$x, /* const T&  */$lower_bound, /* const T&  */$upper_bound) {
        if ($x < $lower_bound) {
            return $lower_bound;
        }
        if ($x > $upper_bound) {
            return $upper_bound;
        }
        return $x;
    }

    // Remove newline (if any) at the end of the string.
    public static function chomp_string(&$str) {
        $str = rtrim($str, "\n");
    }
}