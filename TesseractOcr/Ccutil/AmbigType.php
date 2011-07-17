<?php

namespace TesseractOcr\Ccutil;

use TesseractOcr\Enum;

require_once 'TesseractOcr/Enum.php';

class AmbigType extends Enum {

    const NOT_AMBIG = 0,        // the ngram pair is not ambiguous
    REPLACE_AMBIG = 1,    // ocred ngram should always be substituted with correct
    DEFINITE_AMBIG = 2,   // add correct ngram to the classifier results (1-1)
    SIMILAR_AMBIG = 3,    // use pairwise classifier for ocred/correct pair (1-1)
    CASE_AMBIG = 4,       // this is a case ambiguity (1-1)

    AMBIG_TYPE_COUNT = 5;  // number of enum entries

    public static function __callstatic($name, $args) {
        return self::_load(__CLASS__, $name);
    }
}