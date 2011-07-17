<?php
namespace TesseractOcr\Ccstruct\Publictypes;

use TesseractOcr\Enum;

class PageIteratorLevel extends Enum {

    const RIL_BLOCK = 1,     // Block of text/image/separator line.
    RIL_PARA  = 2,      // Paragraph within a block.
    RIL_TEXTLINE  = 3,  // Line within a paragraph.
    RIL_WORD = 4,      // Word within a textline.
    RIL_SYMBOL = 5;     // Symbol/character within a word.

    public static function __callstatic($name, $args) {
        return self::_load(__CLASS__, $name);
    }

}