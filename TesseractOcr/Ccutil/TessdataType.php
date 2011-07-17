<?php

namespace TesseractOcr\Ccutil;

use TesseractOcr\Enum;

require_once 'TesseractOcr/Enum.php';

class TessdataType extends Enum {

    const TESSDATA_LANG_CONFIG = 0,         // 0
    TESSDATA_UNICHARSET = 1,          // 1
    TESSDATA_AMBIGS = 2,              // 2
    TESSDATA_INTTEMP = 3,             // 3
    TESSDATA_PFFMTABLE = 4,           // 4
    TESSDATA_NORMPROTO = 5,           // 5
    TESSDATA_PUNC_DAWG = 6,           // 6
    TESSDATA_SYSTEM_DAWG = 7,         // 7
    TESSDATA_NUMBER_DAWG = 8,         // 8
    TESSDATA_FREQ_DAWG = 9,           // 9
    TESSDATA_FIXED_LENGTH_DAWGS = 10,  // 10
    TESSDATA_CUBE_UNICHARSET = 11,     // 11
    TESSDATA_CUBE_SYSTEM_DAWG = 12,    // 12

    TESSDATA_NUM_ENTRIES = 13;

    public static function __callstatic($name, $args) {
        return self::_load(__CLASS__, $name);
    }

}