<?php

namespace TesseractOcr\Ccutil;

use TesseractOcr\Ccmain\Tesseract;

require_once 'TesseractOcr/Ccutil/TessdataManager.php';
require_once 'TesseractOcr/Ccutil/Unicharset.php';

class Ccutil {
    public $datadir;        // dir for data files
    public $imagebasename;  // name of image
    public $lang;
    public $language_data_path_prefix;
    // TessdataManager
    /**
     * @var TesseractOcr\Ccutil\TessdataManager
     */
    public $tessdata_manager;

    /**
     * @var TesseractOcr\Ccutil\Unicharset
     */
    public $unicharset;

    /**
     * @var TesseractOcr\Ccutil\UnicharAmbigs
     */
    public $unichar_ambigs;
    public $imagefile;  // image file name
    public $directory;  // main directory


    //ParamsVectors
    private $params_;


    // Member parameters.
    // These have to be declared and initialized after params_ member, since
    // params_ should be initialized before parameters are added to it.
    public $m_data_sub_dir =  "tessdata/"; //, "Directory for data files");
    public $ambigs_debug_level = 0; //, "Debug level for unichar ambiguities");
    public $use_definite_ambigs_for_classifier = 0; //"Use definite ambiguities when running character classifier");
    public $use_ambigs_for_adaption = 0; //"Use ambigs for deciding whether to adapt to a character");

    public function __construct() {
        $this->tessdata_manager = new TessdataManager();
        $this->unicharset = new Unicharset();
        $this->unichar_ambigs = new UnicharAmbigs();
    }

    // Read the arguments and set up the data path.
    public function main_setup(
        /*const char **/$argv0,        // program name
        /*const char **/$basename      // name of image
    ) {
        $this->imagebasename = $basename;
        $this->params_ = null;
        $this->datadir = realpath(dirname(__FILE__) . '/../tessdata');
    }

    // return ParamsVectors *
    public function params() {
        return $this->params_;
    }
}