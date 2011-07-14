<?php

namespace TesseractOcr\Api;

use TesseractOcr\Enum;

class OcrEngineMode extends Enum {

    const OEM_TESSERACT_ONLY = 1,       // Run Tesseract only - fastest
    OEM_CUBE_ONLY = 2,                  // Run Cube only - better accuracy, but slower
    OEM_TESSERACT_CUBE_COMBINED = 3,    // Run both and combine results - best accuracy
    OEM_DEFAULT = 4;                    // Specify this mode when calling init_*(),
                                        // to indicate that any of the above modes
                                        // should be automatically inferred from the
                                        // variables in the language-specific config,
                                        // command-line configs, or if not specified
                                        // in any of the above should be set to the
                                        // default OEM_TESSERACT_ONLY.

    public static function __callstatic($name, $args) {
        return self::_load(__CLASS__, $name);
    }
}