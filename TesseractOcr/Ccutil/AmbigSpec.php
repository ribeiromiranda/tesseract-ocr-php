<?php
namespace TesseractOcr\Ccutil;

require_once 'TesseractOcr/Ccutil/Unichar.php';
require_once 'TesseractOcr/Ccutil/AmbigType.php';

// AMBIG_SPEC_LIST stores a list of dangerous ambigs that
// start with the same unichar (e.g. r->t rn->m rr1->m).
class AmbigSpec { //extends ELIST_LINK {

    public $wrong_ngram = array(); //[MAX_AMBIG_SIZE + 1];
    public $correct_fragments = array(); //[MAX_AMBIG_SIZE + 1];
    public $correct_ngram_id;

    /**
     * @var TesseractOcr\Ccutil\AmbigType
     */
    public $type;

    /**
     * @var int
     */
    public $wrong_ngram_size;

    public function __construct() {
        $this->wrong_ngram[0] = Unichar::INVALID_UNICHAR_ID;
        $this->correct_fragments[0] = Unichar::INVALID_UNICHAR_ID;
        $this->correct_ngram_id = Unichar::INVALID_UNICHAR_ID;
        $this->type = AmbigType::NOT_AMBIG;
        $this->wrong_ngram_size = 0;
    }

    public function __destruct() {
    }

    // Comparator function for sorting AmbigSpec_LISTs. The lists will
    // be sorted by their wrong_ngram arrays. Example of wrong_ngram vectors
    // in a a sorted AmbigSpec_LIST: [9 1 3], [9 3 4], [9 8], [9, 8 1].
    //static int
    public static function compare_ambig_specs(/* const void * */$spec1, /* const void * */$spec2) {
        /* const AmbigSpec * */
        ///$s1 = *reinterpret_cast<const AmbigSpec * const *>(spec1);
        /* const AmbigSpec * */
        //$s2 = *reinterpret_cast<const AmbigSpec * const *>(spec2);
        return UnicharIdArrayUtils::compare($s1->wrong_ngram, $s2->wrong_ngram);
    }


};