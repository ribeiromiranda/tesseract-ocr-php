<?php

namespace TesseractOcr;

use TesseractOcr\Api\OcrEngineMode;
use TesseractOcr\Ccstruct\Publictypes\PageSegMode;
use TesseractOcr\Api\TessBaseAPI;

require_once 'TesseractOcr/Api/TessBaseAPI.php';
require_once 'TesseractOcr/Api/OcrEngineMode.php';
require_once 'TesseractOcr/Ccstruct/Publictypes/PageSegMode.php';

class Command {


    public static function main(&$argc, &$argv) {
        $api = new TessBaseAPI();
        $pagesegmode = PageSegMode::PSM_AUTO();
        $lang = 'eng';

        $api->SetOutputName($argv[2]);
        $api->SetPageSegMode($pagesegmode);

        $api->Init($argv[0], $lang, OcrEngineMode::OEM_DEFAULT(),
            null, 0, null, null, false);
    }
}

Command::main($argc, $argv);