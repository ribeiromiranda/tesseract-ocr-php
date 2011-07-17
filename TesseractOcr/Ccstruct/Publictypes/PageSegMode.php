<?php

namespace TesseractOcr\Ccstruct\Publictypes;

use TesseractOcr\Enum;

require_once 'TesseractOcr/Enum.php';

class PageSegMode extends Enum {

    const PSM_OSD_ONLY = 1,       ///< Orientation and script detection only.
    PSM_AUTO_OSD = 2,       ///< Automatic page segmentation with orientation and
    ///< script detection. (OSD)
    PSM_AUTO_ONLY = 3,      ///< Automatic page segmentation, but no OSD, or OCR.
    PSM_AUTO = 4,           ///< Fully automatic page segmentation, but no OSD.
    PSM_SINGLE_COLUMN = 5,  ///< Assume a single column of text of variable sizes.
    PSM_SINGLE_BLOCK_VERT_TEXT = 6,  ///< Assume a single uniform block of vertically
    ///< aligned text.
    PSM_SINGLE_BLOCK = 7,   ///< Assume a single uniform block of text. (Default.)
    PSM_SINGLE_LINE = 8,    ///< Treat the image as a single text line.
    PSM_SINGLE_WORD = 9,    ///< Treat the image as a single word.
    PSM_CIRCLE_WORD = 10,    ///< Treat the image as a single word in a circle.
    PSM_SINGLE_CHAR = 11,    ///< Treat the image as a single character.

    PSM_COUNT = 12;           ///< Number of enum entries.

    public static function __callstatic($name, $args) {
        return self::_load(__CLASS__, $name);
    }

}