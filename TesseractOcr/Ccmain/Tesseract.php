<?php

namespace TesseractOcr\Ccmain;

use TesseractOcr\Dict\Matchdefs;
use TesseractOcr\Ccutil\ParamUtils;
use TesseractOcr\Ccutil\TessdataType;
use TesseractOcr\Ccutil\TessdataManager;
use TesseractOcr\Ccutil\Ccutil;
use TesseractOcr\Api\OcrEngineMode;
use TesseractOcr\Ccstruct\Publictypes\PageSegMode;

require_once 'TesseractOcr/Ccstruct/Publictypes/PageSegMode.php';
require_once 'TesseractOcr/Api/OcrEngineMode.php';
require_once 'TesseractOcr/Ccutil/CCUtil.php';
require_once 'TesseractOcr/Ccutil/TessdataManager.php';
require_once 'TesseractOcr/Ccutil/TessdataType.php';
require_once 'TesseractOcr/Ccutil/ParamUtils.php';
require_once 'TesseractOcr/Ccutil/UnicharAmbigs.php';
require_once 'TesseractOcr/Dict/Matchdefs.php';


class Tesseract extends Ccutil {
    /**
     * "Page seg mode: 0=osd only, 1=auto+osd, 2=auto, 3=col, 4=block,"
                    " 5=line, 6=word, 7=char"
                    " (Values from PageSegMode enum in publictypes.h)"
     */
    public $tessedit_pageseg_mode;

    //// Data members ///////////////////////////////////////////////////////
    // TODO(ocr-team): Remove obsolete parameters.
    public $tessedit_resegment_from_boxes = false; //"Take segmentation and labeling from box file");
    public $tessedit_resegment_from_line_boxes = false; //"Conversion of word/line box file to char box file");
    public $tessedit_train_from_boxes = false; //"Generate training data from boxed chars");
    public $tessedit_make_boxes_from_boxes = false; //"Generate more boxes from boxed chars");
    public $tessedit_dump_pageseg_images = false; //"Dump intermediate images made during page segmentation");
    public $tessedit_ocr_engine_mode;
                    //"Which OCR engine(s) to run (Tesseract, Cube, both). Defaults"
                    //" to loading and running only Tesseract (no Cube, no combiner)."
                    //" (Values from OcrEngineMode enum in tesseractclass.h)");
    public $tessedit_char_blacklist = ""; //"Blacklist of chars not to recognize");
    public $tessedit_char_whitelist = ""; //"Whitelist of chars to recognize");
    public $tessedit_ambigs_training = false; //"Perform training for ambiguities");
    //public $pageseg_devanagari_split_strategy,
    //tesseract::ShiroRekhaSplitter::NO_SPLIT,
     //               "Whether to use the top-line splitting process for Devanagari "
     //               "documents while performing page-segmentation.");
    //public $ocr_devanagari_split_strategy,
    //tesseract::ShiroRekhaSplitter::NO_SPLIT,
    //                "Whether to use the top-line splitting process for Devanagari "
    //                "documents while performing ocr.");
    public $tessedit_write_params_to_file = ""; //"Write all parameters to the given file.");
    public $tessedit_adapt_to_char_fragments = true;
                     //"Adapt to words that contain "
                     //" a character composed form fragments");
    public $tessedit_adaption_debug = false;
                     //"Generate and print debug information for adaption");
    public $applybox_debug = 1;// "Debug level");
    public $applybox_page = 0;// "Page number to apply boxes from");
    public $applybox_exposure_pattern = ".exp";
                       //"Exposure value follows this pattern in the image"
                       //" filename. The name of the image files are expected"
                       //" to be in the form [lang].[fontname].exp[num].tif");
    public $applybox_learn_chars_and_char_frags_mode = false;
                     //"Learn both character fragments (as is done in the"
                     //" special low exposure mode) as well as unfragmented"
                     //" characters.");
    public $applybox_learn_ngrams_mode = false;
                     //"Each bounding box is assumed to contain ngrams. Only"
                     //" learn the ngrams whose outlines overlap horizontally.");
    public $tessedit_draw_outwords = false; //"Draw output words");
    public $tessedit_training_tess = false; //"Call Tess to learn blobs");
    public $tessedit_dump_choices = false; //"Dump char choices");
    public $tessedit_fix_fuzzy_spaces = true;
                     //"Try to improve fuzzy spaces");
    public $tessedit_unrej_any_wd = false;
                     //"Dont bother with word plausibility");
    public $tessedit_fix_hyphens = true; // "Crunch double hyphens?");
    public $tessedit_redo_xheight = true; // "Check/Correct x-height");
    public $tessedit_enable_doc_dict = true; //"Add words to the document dictionary");
    public $tessedit_debug_fonts = false; // "Output font info per char");
    public $tessedit_debug_block_rejection = false; // "Block and Row stats");
    public $debug_x_ht_level = 0; //"Reestimate debug");
    public $debug_acceptable_wds = false; // "Dump word pass/fail chk");
    public $chs_leading_punct = "('`\""; // "Leading punctuation");
    public $chs_trailing_punct1 = ").,;:?!"; // "1st Trailing punctuation");
    public $chs_trailing_punct2 = ")'`\""; // "2nd Trailing punctuation");
    public $quality_rej_pc = 0.08; // "good_quality_doc lte rejection limit");
    public $quality_blob_pc = 0.0; // "good_quality_doc gte good blobs limit");
    public $quality_outline_pc = 1.0; // "good_quality_doc lte outline error limit");
    public $quality_char_pc = 0.95; // "good_quality_doc gte good char limit");
    public $quality_min_initial_alphas_reqd = 2; // "alphas in a good word");
    public $tessedit_tess_adapt_to_rejmap = false; // "Use reject map to control Tesseract adaption");
    public $tessedit_tess_adaption_mode = 0x27; //"Adaptation decision algorithm for tess");
    public $tessedit_minimal_rej_pass1 = false; //"Do minimal rejection on pass 1 output");
    public $tessedit_test_adaption = false;/// "Test adaption criteria");
    public $tessedit_matcher_log = false;// "Log matcher activity");
    public $tessedit_test_adaption_mode = 3; //"Adaptation decision algorithm for tess");
    public $save_best_choices = false;
                     //"Save the results of the recognition step"
                     //" (blob_choices) within the corresponding WERD_CHOICE");
    public $test_p = false; // "Test for point");
    public $test_pt_x = 99999.99; //, "xcoord");
    public $test_pt_y = 99999.99; //, "ycoord");
    public $cube_debug_level = 1; // "Print cube debug info.");
    public $outlines_odd = "%| "; // "Non standard number of outlines");
    public $outlines_2 = "ij!?%\":;"; //"Non standard number of outlines");
    public $docqual_excuse_outline_errs = false; //"Allow outline errs in unrejection?");
    public $tessedit_good_quality_unrej = true; //"Reduce rejection on good docs");
    public $tessedit_use_reject_spaces = true; // "Reject spaces?");
    public $tessedit_reject_doc_percent = 65.00; //"%rej allowed before rej whole doc");
    public $tessedit_reject_block_percent = 45.00; //"%rej allowed before rej whole block");
    public $tessedit_reject_row_percent = 40.00; //"%rej allowed before rej whole row");
    public $tessedit_whole_wd_rej_row_percent = 70.00;
                       //"Number of row rejects in whole word rejects"
                       //"which prevents whole row rejection");
    public $tessedit_preserve_blk_rej_perfect_wds = true;
                     //"Only rej partially rejected words in block rejection");
    public $tessedit_preserve_row_rej_perfect_wds = true;
                     //"Only rej partially rejected words in row rejection");
    public $tessedit_dont_blkrej_good_wds = false;
                     //"Use word segmentation quality metric");
    public $tessedit_dont_rowrej_good_wds = false;
                     //"Use word segmentation quality metric");
    public $tessedit_preserve_min_wd_len = 2;
                    //"Only preserve wds longer than this");
    public $tessedit_row_rej_good_docs = true;
                     //"Apply row rejection to good docs");
    public $tessedit_good_doc_still_rowrej_wd = 1.1;
                       //"rej good doc wd if more than this fraction rejected");
    public $tessedit_reject_bad_qual_wds = true;
                     //"Reject all bad quality wds");
    public $tessedit_debug_doc_rejection = false;// "Page stats");
    public $tessedit_debug_quality_metrics = false; //"Output data to debug file");
    public $bland_unrej = false;// "unrej potential with no chekcs");
    public $quality_rowrej_pc = 1.1; // "good_quality_doc gte good char limit");
    public $unlv_tilde_crunching = true; //"Mark v.bad words for tilde crunch");
    public $crunch_early_merge_tess_fails = true; // "Before word crunch?");
    public $crunch_early_convert_bad_unlv_chs = false;// "Take out ~^ early?");
    public $crunch_terrible_rating = 80.0;// "crunch rating lt this");
    public $crunch_terrible_garbage = true;// "As it says");
    public $crunch_poor_garbage_cert = -9.0; //"crunch garbage cert lt this");
    public $crunch_poor_garbage_rate = 60;  //"crunch garbage rating lt this");
    public $crunch_pot_poor_rate = 40;  //"POTENTIAL crunch rating lt this");
    public $crunch_pot_poor_cert = -8.0;  //"POTENTIAL crunch cert lt this");
    public $crunch_pot_garbage = true;  //"POTENTIAL crunch garbage");
    public $crunch_del_rating = 60;  //"POTENTIAL crunch rating lt this");
    public $crunch_del_cert = -10.0;  //"POTENTIAL crunch cert lt this");
    public $crunch_del_min_ht = 0.7;  //"Del if word ht lt xht x this");
    public $crunch_del_max_ht = 3.0;  //"Del if word ht gt xht x this");
    public $crunch_del_min_width = 3.0; //"Del if word width lt xht x this");
    public $crunch_del_high_word = 1.5; //"Del if word gt xht x this above bl");
    public $crunch_del_low_word = 0.5; //"Del if word gt xht x this below bl");
    public $crunch_small_outlines_size = 0.6; // "Small if lt xht x this");
    public $crunch_rating_max = 10; // "For adj length in rating per ch");
    public $crunch_pot_indicators = 1; // "How many potential indicators needed");
    public $crunch_leave_ok_strings = true; // "Dont touch sensible strings");
    public $crunch_accept_ok = true; // "Use acceptability in okstring");
    public $crunch_leave_accept_strings = false; //"Dont pot crunch sensible strings");
    public $crunch_include_numerals = false; //"Fiddle alpha figures");
    public $crunch_leave_lc_strings = 4; //"Dont crunch words with long lower case strings");
    public $crunch_leave_uc_strings = 4; //"Dont crunch words with long lower case strings");
    public $crunch_long_repetitions = 3; //"Crunch words with long repetitions");
    public $crunch_debug = 0; // "As it says");
    public $fixsp_non_noise_limit = 1; //"How many non-noise blbs either side?");
    public $fixsp_small_outlines_size = 0.28; //"Small if lt xht x this");
    public $tessedit_prefer_joined_punct = false;// "Reward punctation joins");
    public $fixsp_done_mode = 1; //"What constitues done for spacing");
    public $debug_fix_space_level = 0; //"Contextual fixspace debug");
    public $numeric_punctuation = ".,"; //"Punct. chs expected WITHIN numbers");
    public $x_ht_acceptance_tolerance = 8; //"Max allowed deviation of blob top outside of font data");
    public $x_ht_min_change = 8;//, "Min change in xht before actually trying it");
    public $tessedit_write_block_separators = false; // "Write block separators in output");
    public $tessedit_write_rep_codes = false; //"Write repetition char code");
    public $tessedit_write_unlv = false; // "Write .unlv output file");
    public $tessedit_create_hocr = false; // "Write .html hOCR output file");
    public $unrecognised_char = "|"; //"Output char for unidentified blobs");
    public $suspect_level = 99; //"Suspect marker level");
    public $suspect_space_level = 100; //"Min suspect level for rejecting spaces");
    public $suspect_short_words = 2; //"Dont Suspect dict wds longer than this");
    public $suspect_constrain_1Il = false;// "UNLV keep 1Il chars rejected");
    public $suspect_rating_per_ch = 999.9;// "Dont touch bad rating limit");
    public $suspect_accept_rating = -999.9;// "Accept good rating limit");
    public $tessedit_minimal_rejection = false;// "Only reject tess failures");
    public $tessedit_zero_rejection = false; //"Dont reject ANYTHING");
    public $tessedit_word_for_word = false; // "Make output have exactly one word per WERD");
    public $tessedit_zero_kelvin_rejection = false; //"Dont reject ANYTHING AT ALL");
    public $tessedit_consistent_reps = true;//"Force all rep chars the same");
    public $tessedit_reject_mode = 0;// "Rejection algorithm");
    public $tessedit_ok_mode = 5;// "Acceptance decision algorithm");
    public $tessedit_rejection_debug = false;//, "Adaption debug");
    public $tessedit_flip_0O = true;//, "Contextual 0O O0 flips");
    public $tessedit_lower_flip_hyphen = 1.5; //"Aspect ratio dot/hyphen test");
    public $tessedit_upper_flip_hyphen = 1.8; //"Aspect ratio dot/hyphen test");
    public $rej_trust_doc_dawg = false; // "Use DOC dawg in 11l conf. detector");
    public $rej_1Il_use_dict_word = false;// "Use dictword test");
    public $rej_1Il_trust_permuter_type = true;// "Dont double check");
    public $rej_use_tess_accepted = true;// "Individual rejection control");
    public $rej_use_tess_blanks = true;// "Individual rejection control");
    public $rej_use_good_perm = true; // "Individual rejection control");
    public $rej_use_sensible_wd = false;// "Extend permuter check");
    public $rej_alphas_in_number_perm = false;// "Extend permuter check");
    public $rej_whole_of_mostly_reject_word_fract = 0.85; //, "if >this fract");
    public $tessedit_image_border = 2; // "Rej blbs near image edge limit");
    public $ok_repeated_ch_non_alphanum_wds = "-?*\075"; //"Allow NN to unrej");
    public $conflict_set_I_l_1 = "Il1[]";// "Il1 conflict set");
    public $min_sane_x_ht_pixels = 8;// "Reject any x-ht lt or eq than this");
    public $tessedit_create_boxfile = false;// "Output text with boxes");
    public $tessedit_page_number = -1;//"-1 -> All pages, else specifc page to process");
    public $tessedit_write_images = false;// "Capture the image from the IPE");
    public $interactive_mode = false;// "Run interactively?");
    public $file_type = ".tif";// "Filename extension");
    public $tessedit_override_permuter = true;//, "According to dict_word");
    public $tessdata_manager_debug_level = 0; //"Debug level for TessdataManager functions.");
    // Min acceptable orientation margin (difference in scores between top and 2nd
    // choice in OSResults::orientations) to believe the page orientation.
    public $min_orientation_margin = 12.0; //"Min acceptable orientation margin");


    // The filename of a backup config file. If not null, then we currently
    // have a temporary debug config file loaded, and backup_config_file_
    // will be loaded, and set to null when debug is complete.
    //const char*
    private $backup_config_file_;
    // The filename of a config file to read when processing a debug word.
    //STRING
    private $word_config_;
    //Pix*
    private $pix_binary_;
    //Pix*
    private $pix_grey_;
    // The shiro-rekha splitter object which is used to split top-lines in
    // Devanagari words to provide a better word and grapheme segmentation.
    //ShiroRekhaSplitter
    private $splitter_;
    // The boolean records if the currently set
    // pix_binary_ member has been modified due to any processing so that this
    // may hurt Cube's recognition phase.
    //bool
    private $orig_image_changed_;
    // Page segmentation/layout
    //Textord
    private $textord_;
    // True if the primary language uses right_to_left reading order.
    //bool
    private $right_to_left_;
    //FCOORD
    private $deskew_;
    //FCOORD
    private $reskew_;
    //TesseractStats
    private $stats_;
    // Cube objects.
    //CubeRecoContext*
    private $cube_cntxt_;
    //TesseractCubeCombiner *
    private $tess_cube_combiner_;

    public function __constuct() {
        $this->tessedit_pageseg_mode = PageSegMode::PSM_SINGLE_BLOCK();
        $this->tessedit_ocr_engine_mode = OcrEngineMode::OEM_TESSERACT_ONLY();
    }
//    ~Tesseract();

    public function Clear() {

    }

    // Simple accessors.
    //const FCOORD&
    public function reskew() {
        return $this->reskew_;
    }
    // Destroy any existing pix and return a pointer to the pointer.
    // return Pix**
    public function  mutable_pix_binary() {
        $this->Clear();
        return $this->pix_binary_;
    }
    // Pix*
    public function pix_binary() {
        return $this->pix_binary_;
    }
    // Pix*
    public function pix_grey() {
        return $this->pix_grey_;
    }
    public function set_pix_grey(Pix $grey_pix) {
        $this->pix_grey_ = $grey_pix;
    }
    // int
    public function ImageWidth() {
        return $this->pixGetWidth($this->pix_binary_);
    }
    // int
    public function ImageHeight() {
        return $this->pixGetHeight($this->pix_binary_);
    }

    //const ShiroRekhaSplitter&
    public function splitter() {
        return $this->splitter_;
    }
    // ShiroRekhaSplitter*
    public function mutable_splitter() {
        return $this->splitter_;
    }
    // const Textord&
    public function textord() {
        return $this->textord_;
    }
    public function mutable_textord() {
        return $this->textord_;
    }

    //bool
    public function right_to_left() {
        return $this->right_to_left_;
    }

    public function SetBlackAndWhitelist() {

    }

    // Perform steps to prepare underlying binary image/other data structures for
    // page segmentation. Uses the strategy specified in the global variable
    // pageseg_devanagari_split_strategy for perform splitting while preparing for
    // page segmentation.
    public function PrepareForPageseg(){

    }

    // Perform steps to prepare underlying binary image/other data structures for
    // Tesseract OCR. The current segmentation is required by this method.
    // Uses the strategy specified in the global variable
    // ocr_devanagari_split_strategy for performing splitting while preparing for
    // Tesseract ocr.
    public function PrepareForTessOCR(/*BLOCK_LIST**/ $block_list,
    Tesseract $osd_tess, OSResults $osr) {

    }

    // Perform steps to prepare underlying binary image/other data structures for
    // Cube OCR.
    public function PrepareForCubeOCR() {

    }

    // int
    public function SegmentPage(/* const STRING* */ $input_file, /* BLOCK_LIST* */ $blocks,
        Tesseract $osd_tess, OSResults $osr) {

    }
    public function SetupWordScripts(/* BLOCK_LIST*  */$blocks) {

    }
    //int
    public function AutoPageSeg(/* int  */$resolution, /* bool  */$single_column,
    /* bool  */$osd, /* bool  */$only_osd,
    /* BLOCK_LIST* */ $blocks, /* TO_BLOCK_LIST* */ $to_blocks,
    Tesseract $osd_tess, OSResults $osr) {

    }

    //// control.h /////////////////////////////////////////////////////////
    //bool
    public function ProcessTargetWord(/* const TBOX& */ $word_box, /* const TBOX&  */$target_word_box,
    /* const char*  */$word_config, /* int  */$pass) {

    }
    /* void  */ public function recog_all_words(/* PAGE_RES*  */$page_res,
    /* ETEXT_DESC*  */$monitor,
    /* const TBOX*  */$target_word_box,
    /* const char*  */$word_config,
    /* int  */$dopasses) {

    }
    public function classify_word_pass1(                 //recog one word
    /* WERD_RES * */$word,  //word to do
    /* ROW * */$row,
    /* BLOCK*  */$block) {

    }
    public function recog_pseudo_word(/* PAGE_RES*  */$page_res,  // blocks to check
    /* TBOX  */$selection_box) {

    }

    public function  fix_rep_char(/* PAGE_RES_IT*  */$page_res_it){

    }
    public function ExplodeRepeatedWord(/* BLOB_CHOICE*  */$best_choice, /* PAGE_RES_IT*  */$page_res_it){

    }

    // Callback helper for fix_quotes returns a double quote if both
    // arguments are quote, otherwise INVALID_UNICHAR_ID.
    //UNICHAR_ID
    public function BothQuotes(/* UNICHAR_ID  */$id1, /* UNICHAR_ID  */$id2) {

    }
    public function fix_quotes(/* WERD_RES*  */$word_res,
    /* BLOB_CHOICE_LIST_CLIST  */$blob_choices) {

    }
    //ACCEPTABLE_WERD_TYPE
    public function acceptable_word_string(/* const char  **/$s,
    /* const char * */$lengths) {

    }
    public function match_word_pass2(                 //recog one word
    /* WERD_RES * */$word,  //word to do
    /* ROW * */$row,
    /* BLOCK*  */$block) {

    }
    public function classify_word_pass2(  //word to do
    /* WERD_RES * */$word,
    /* BLOCK*  */$block,
    /* ROW * */$row) {

    }
    public function  ReportXhtFixResult(/* bool  */$accept_new_word, /* float  */$new_x_ht,
    /* WERD_RES*  */$word, /* WERD_RES*  */$new_word) {

    }
    public function RunOldFixXht(/* WERD_RES * */$word, /* BLOCK*  */$block, /* ROW * */$row){

    }
    public function TrainedXheightFix(/* WERD_RES * */$word, /* BLOCK* */ $block, /* ROW * */$row) {

    }
    // BOOL8
    public function recog_interactive(/* BLOCK* */ $block, /* ROW* */ $row, /* WERD_RES* */ $word_res) {

    }

    // Callback helper for fix_hyphens returns UNICHAR_ID of - if both
    // arguments are hyphen, otherwise INVALID_UNICHAR_ID.
    //UNICHAR_ID
    public function BothHyphens(/* UNICHAR_ID  */$id1, /* UNICHAR_ID  */$id2) {

    }
    // Callback helper for fix_hyphens returns true if box1 and box2 overlap
    // (assuming both on the same textline, are in order and a chopped em dash.)
    //bool
    public function HyphenBoxesOverlap(/* const TBOX&  */$box1, /* const TBOX&  */$box2) {

    }
    public function fix_hyphens(/* WERD_RES*  */$word_res,
    /* BLOB_CHOICE_LIST_CLIST * */$blob_choices) {

    }
    public function set_word_fonts(
    /* WERD_RES * */$word,  // set fonts of this word
    /* BLOB_CHOICE_LIST_CLIST * */$blob_choices)   // detailed results
    {

    }
    public function font_recognition_pass(  //good chars in word
    /* PAGE_RES_IT  */$page_res_it) {

    }
    //BOOL8
    public function check_debug_pt(/* WERD_RES * */$word, /* int */ $location) {

    }

    //// cube_control.cpp ///////////////////////////////////////////////////
    //bool
    public function init_cube_objects(/* bool  */$load_combiner,
    TessdataManager $tessdata_manager) {

    }
    public function run_cube(/* PAGE_RES * */$page_res) {

    }
    public function cube_recognize(CubeObject $cube_obj, /* PAGE_RES_IT * */$page_res_it) {

    }
    public function  fill_werd_res(/* const BoxWord&  */$cube_box_word,
    /* WERD_CHOICE* */ $cube_werd_choice,
    /* const char* */ $cube_best_str,
    /* PAGE_RES_IT * */$page_res_it) {

    }
    //bool
    public function extract_cube_state(CubeObject $cube_obj, /* int*  */$num_chars,
    /* Boxa**  */$char_boxes, /* CharSamp*** */ $char_samples) {

    }
    //bool
    public function create_cube_box_word(/* Boxa * */$char_boxes, /* int  */$num_chars,
    /* TBOX  */$word_box, BoxWord $box_word) {

    }
    //// output.h //////////////////////////////////////////////////////////

    public function output_pass(/* PAGE_RES_IT & */$page_res_it, /* const TBOX * */$target_word_box) {

    }
    public function write_results(/* PAGE_RES_IT & */$page_res_it,  // full info
    /* char  */$newline_type,         // type of newline
    /* BOOL8  */$force_eol            // override tilde crunch?
    ) {

    }
    public function set_unlv_suspects(/* WERD_RES * */$word) {

    }
    //UNICHAR_ID
    public function get_rep_char(/* WERD_RES * */$word)
      // what char is repeated?
    {

    }
    //BOOL8
    public function acceptable_number_string(/* const char * */$s,
    /* const char * */$lengths) {

    }
    //inT16
    public function count_alphanums(/* const WERD_CHOICE & */$word) {

    }
    //inT16
    public function count_alphas(/* const WERD_CHOICE & */$word){

    }
     //// tessedit.h ////////////////////////////////////////////////////////
    public function read_config_file(/* const char * */$filename, /* bool  */$init_only) {

    }
    //int
    public function init_tesseract(/* const char * */$arg0,
    /* const char * */$textbase,
    /* const char * */$language,
    OcrEngineMode $oem,
    /* char ** */$configs,
    /* int  */$configs_size,
    /* const GenericVector<STRING> * */$vars_vec,
    /* const GenericVector<STRING> * */$vars_values,
    /* bool  */$set_only_init_params) {

        if (!$this->init_tesseract_lang_data($arg0, $textbase, $language, $oem, $configs,
        $configs_size, $vars_vec, $vars_values,
        $set_only_init_params)) {
            return -1;
        }
        // If only Cube will be used, skip loading Tesseract classifier's
        // pre-trained templates.
        $init_tesseract_classifier =
        ($this->tessedit_ocr_engine_mode === OcrEngineMode::OEM_TESSERACT_ONLY() ||
        $this->tessedit_ocr_engine_mode === OcrEngineMode::OEM_TESSERACT_CUBE_COMBINED());
        // If only Cube will be used and if it has its own Unicharset,
        // skip initializing permuter and loading Tesseract Dawgs.
        $init_dict =
        !($this->tessedit_ocr_engine_mode == OcrEngineMode::OEM_CUBE_ONLY() &&
        $this->tessdata_manager.SeekToStart(TESSDATA_CUBE_UNICHARSET));
        program_editup(textbase, init_tesseract_classifier, init_dict);
        tessdata_manager.End();
        return 0;                      //Normal exit

    }
//     public function init_tesseract(/*const char **/$datapath,
//     /*const char **/$language,
//     OcrEngineMode $oem) {
//         return $this->init_tesseract($datapath, NULL, $language, $oem,
//         NULL, 0, NULL, NULL, false);
//     }

    public function init_tesseract_lm(/* const char * */$arg0,
    /* const char * */$textbase,
    /* const char * */$language) {

    }

    public function recognize_page($image_name) {

    }
    public function end_tesseract() {
        echo 'end_tesseract: não implementado' . "\n";
    }


    //bool
    public function init_tesseract_lang_data(/* const char * */$arg0,
    /* const char * */$textbase,
    /* const char * */$language,
    OcrEngineMode $oem,
    /* char ** */$configs,
    /* int  */$configs_size,
    /* const GenericVector<STRING> * */$vars_vec,
    /* const GenericVector<STRING> * */$vars_values,
    /* bool  */$set_only_init_params) {
    // Set the basename, compute the data directory.

        $this->main_setup($arg0, $textbase);

        // Set the language data path prefix
        $this->lang = $language != NULL ? $language : "eng";
        $this->language_data_path_prefix = $this->datadir;
        $this->language_data_path_prefix .= "/" . $this->lang;
        $this->language_data_path_prefix .= ".";

        // Initialize TessdataManager.
        $tessdata_path = $this->language_data_path_prefix . TessdataManager::kTrainedDataSuffix;
        if (!$this->tessdata_manager->Init($tessdata_path,
        $this->tessdata_manager_debug_level)) {
            return false;
        }

        // If a language specific config file (lang.config) exists, load it in.
        if ($this->tessdata_manager->SeekToStart(TessdataType::TESSDATA_LANG_CONFIG())) {
            ParamUtils::ReadParamsFromFp(
            $this->tessdata_manager->GetDataFilePtr(),
            $this->tessdata_manager->GetEndOffset(TessdataType::TESSDATA_LANG_CONFIG()),
            false, $this->params());
            if ($this->tessdata_manager_debug_level) {
                tprintf("Loaded language config file\n");
            }
        }

        // Load tesseract variables from config files. This is done after loading
        // language-specific variables from [lang].traineddata file, so that custom
        // config files can override values in [lang].traineddata file.
        for ($i = 0; $i < $configs_size; ++$i) {
            $this->read_config_file($configs[i], $set_only_init_params);
        }

        // Set params specified in vars_vec (done after setting params from config
        // files, so that params in vars_vec can override those from files).
        if ($vars_vec !== NULL && $vars_values !== NULL) {
            /* for ($i = 0; i < $vars_vec->size(); ++$i) {
                if (!ParamUtils::SetParam((*vars_vec)[i].string(),
                (*vars_values)[i].string(),
                set_only_init_params, this->params())) {
                    tprintf("Error setting param %s\n", (*vars_vec)[i].string());
                    exit(1);
                }
            } */
            throw new \Exception("não foi implementado");
        }

        if (strlen($this->tessedit_write_params_to_file) > 0) {
            $params_file = fopen($this->tessedit_write_params_to_file, "w");
            if ($params_file !== NULL) {
                ParamUtils::PrintParams($params_file, $this->params());
                fclose($params_file);
                if ($this->tessdata_manager_debug_level > 0) {
                    tprintf("Wrote parameters to %s\n", $this->tessedit_write_params_to_file);
                }
            } else {
                tprintf("Failed to open %s for writing params.\n", $this->tessedit_write_params_to_file);
            }
        }

        // Determine which ocr engine(s) should be loaded and used for recognition.
        if ($oem !== OcrEngineMode::OEM_DEFAULT()) {
            $this->tessedit_ocr_engine_mode->set_value($oem);
        }

        if ($this->tessdata_manager_debug_level) {
            tprintf("Loading Tesseract/Cube with tessedit_ocr_engine_mode %d\n",
            $this->tessedit_ocr_engine_mode);
        }

        // Load the unicharset
        if (!$this->tessdata_manager->SeekToStart(TessdataType::TESSDATA_UNICHARSET()) ||
        !$this->unicharset->load_from_file($this->tessdata_manager->GetDataFilePtr())) {
            return false;
        }

        if ($this->unicharset->size() > Matchdefs::MAX_NUM_CLASSES) {
            tprintf("Error: Size of unicharset is greater than MAX_NUM_CLASSES\n");
            return false;
        }
        $this->right_to_left_ = $this->unicharset->any_right_to_left();
        if ($this->tessdata_manager_debug_level) {
            tprintf("Loaded unicharset\n");
        }

        if (!$this->tessedit_ambigs_training &&
            $this->tessdata_manager->SeekToStart(TessdataType::TESSDATA_AMBIGS())) {

            $this->unichar_ambigs->LoadUnicharAmbigs(
                $this->tessdata_manager->GetDataFilePtr(),
                $this->tessdata_manager->GetEndOffset(TessdataType::TESSDATA_AMBIGS()),
                $this->ambigs_debug_level,
                $this->use_ambigs_for_adaption, $this->unicharset
            );
            var_dump('asdf');
            exit;
            if ($this->tessdata_manager_debug_level) {
                tprintf("Loaded ambigs\n");
            }
        }

        // Load Cube objects if necessary.
        if ($this->tessedit_ocr_engine_mode === OcrEngineMode::OEM_CUBE_ONLY()) {
            tprintf("Loaded Cube w/out combiner\n");
        } else if ($this->tessedit_ocr_engine_mode === OcrEngineMode::OEM_TESSERACT_CUBE_COMBINED()) {
            if ($this->tessdata_manager_debug_level) {
                tprintf("Loaded Cube with combiner\n");
            }
        }

        return true;
    }
    /*
    //// pgedit.h //////////////////////////////////////////////////////////
    //SVMenuNode
    public function build_menu_new() {

    }
    public function pgeditor_main(int width, int height, PAGE_RES* page_res) {

    }
    public function process_image_event( // action in image win
    const SVEvent &event) {

    }
    //BOOL8
    public function process_cmd_win_event(                 // UI command semantics
    inT32 cmd_event,  // which menu item?
    char *new_value   // any prompt data
    ) {

    }
    public function debug_word(PAGE_RES* page_res, const TBOX &selection_box) {

    }
    public function do_re_display(
    BOOL8 (tesseract::Tesseract::*word_painter)(BLOCK* block,
    ROW* row,
    WERD_RES* word_res)) {

    }
    //BOOL8
    public function word_display(BLOCK* block, ROW* row, WERD_RES* word_res) {

    }
    //BOOL8
    public function word_bln_display(BLOCK* block, ROW* row, WERD_RES* word_res) {

    }
    //BOOL8
    public function word_blank_and_set_display(BLOCK* block, ROW* row, WERD_RES* word_res) {

    }
    //BOOL8
    public function word_set_display(BLOCK* block, ROW* row, WERD_RES* word_res) {

    }
    //BOOL8
    public function word_dumper(BLOCK* block, ROW* row, WERD_RES* word_res) {

    }
    //// reject.h //////////////////////////////////////////////////////////
    public function make_reject_map(            //make rej map for wd //detailed results
    WERD_RES *word,
    BLOB_CHOICE_LIST_CLIST *blob_choices,
    ROW *row,
    inT16 pass  //1st or 2nd?
    ) {

    }
    //BOOL8
    public function one_ell_conflict(WERD_RES *word_res, BOOL8 update_map);
    inT16 first_alphanum_index(const char *word,
    const char *word_lengths) {

    }
    //inT16
    public function first_alphanum_offset(const char *word,
    const char *word_lengths) {

    }
    //inT16
    public function alpha_count(const char *word,
    const char *word_lengths) {

    }
    //BOOL8
    public function word_contains_non_1_digit(const char *word,
    const char *word_lengths) {

    }
    public function dont_allow_1Il(WERD_RES *word) {

    }
    //inT16
    public function count_alphanums(  //how many alphanums
    WERD_RES *word) {

    }
    public function flip_0O(WERD_RES *word) {

    }
    //BOOL8
    public function non_0_digit(UNICHAR_ID unichar_id) {

    }
    //BOOL8
    public function non_O_upper(UNICHAR_ID unichar_id) {

    }
    //BOOL8
    public function repeated_nonalphanum_wd(WERD_RES *word, ROW *row) {

    }
    public function nn_match_word(  //Match a word
    WERD_RES *word,
    ROW *row) {

    }
    public function nn_recover_rejects(WERD_RES *word, ROW *row) {

    }
    //BOOL8
    public function test_ambig_word(  //test for ambiguity
    WERD_RES *word) {

    }
    void set_done(  //set done flag
    WERD_RES *word,
    inT16 pass);
    inT16 safe_dict_word(const WERD_CHOICE  &word);
    void flip_hyphens(WERD_RES *word);
    void reject_I_1_L(WERD_RES *word);
    void reject_edge_blobs(WERD_RES *word);
    void reject_mostly_rejects(WERD_RES *word);
    //// adaptions.h ///////////////////////////////////////////////////////
    BOOL8 word_adaptable(  //should we adapt?
    WERD_RES *word,
    uinT16 mode);

    //// tfacepp.cpp ///////////////////////////////////////////////////////
    void recog_word_recursive(WERD_RES* word,
    BLOB_CHOICE_LIST_CLIST *blob_choices);
    void recog_word(WERD_RES *word,
    BLOB_CHOICE_LIST_CLIST *blob_choices);
    void split_and_recog_word(WERD_RES* word,
    BLOB_CHOICE_LIST_CLIST *blob_choices);
    //// fixspace.cpp ///////////////////////////////////////////////////////
    BOOL8 digit_or_numeric_punct(WERD_RES *word, int char_position);
    inT16 eval_word_spacing(WERD_RES_LIST &word_res_list);
    void match_current_words(WERD_RES_LIST &words, ROW *row, BLOCK* block);
    inT16 fp_eval_word_spacing(WERD_RES_LIST &word_res_list);
    void fix_noisy_space_list(WERD_RES_LIST &best_perm, ROW *row, BLOCK* block);
    void fix_fuzzy_space_list(  //space explorer
    WERD_RES_LIST &best_perm,
    ROW *row,
    BLOCK* block);
    void fix_sp_fp_word(WERD_RES_IT &word_res_it, ROW *row, BLOCK* block);
    void fix_fuzzy_spaces(                      //find fuzzy words
    ETEXT_DESC *monitor,  //progress monitor
    inT32 word_count,     //count of words in doc
    PAGE_RES *page_res);
    void dump_words(WERD_RES_LIST &perm, inT16 score,
    inT16 mode, BOOL8 improved);
    BOOL8 uniformly_spaced(WERD_RES *word);
    BOOL8 fixspace_thinks_word_done(WERD_RES *word);
    inT16 worst_noise_blob(WERD_RES *word_res, float *worst_noise_score);
    float blob_noise_score(TBLOB *blob);
    void break_noisiest_blob_word(WERD_RES_LIST &words);
    //// docqual.cpp ////////////////////////////////////////////////////////
    GARBAGE_LEVEL garbage_word(WERD_RES *word, BOOL8 ok_dict_word);
    BOOL8 potential_word_crunch(WERD_RES *word,
    GARBAGE_LEVEL garbage_level,
    BOOL8 ok_dict_word);
    void tilde_crunch(PAGE_RES_IT &page_res_it);
    void unrej_good_quality_words(  //unreject potential
    PAGE_RES_IT &page_res_it);
    void doc_and_block_rejection(  //reject big chunks
    PAGE_RES_IT &page_res_it,
    BOOL8 good_quality_doc);
    void quality_based_rejection(PAGE_RES_IT &page_res_it,
    BOOL8 good_quality_doc);
    void convert_bad_unlv_chs(WERD_RES *word_res);
    // Callback helper for merge_tess_fails returns a space if both
    // arguments are space, otherwise INVALID_UNICHAR_ID.
    UNICHAR_ID BothSpaces(UNICHAR_ID id1, UNICHAR_ID id2);
    void merge_tess_fails(WERD_RES *word_res);
    void tilde_delete(PAGE_RES_IT &page_res_it);
    inT16 word_blob_quality(WERD_RES *word, ROW *row);
    void word_char_quality(WERD_RES *word, ROW *row, inT16 *match_count,
    inT16 *accepted_match_count);
    void unrej_good_chs(WERD_RES *word, ROW *row);
    inT16 count_outline_errs(char c, inT16 outline_count);
    inT16 word_outline_errs(WERD_RES *word);
    BOOL8 terrible_word_crunch(WERD_RES *word, GARBAGE_LEVEL garbage_level);
    CRUNCH_MODE word_deletable(WERD_RES *word, inT16 &delete_mode);
    inT16 failure_count(WERD_RES *word);
    BOOL8 noise_outlines(TWERD *word);
    //// pagewalk.cpp ///////////////////////////////////////////////////////
    void
    process_selected_words (
    PAGE_RES* page_res, // blocks to check
    //function to call
    TBOX & selection_box,
    BOOL8 (tesseract::Tesseract::*word_processor) (BLOCK* block,
    ROW* row,
    WERD_RES* word_res));
    //// tessbox.cpp ///////////////////////////////////////////////////////
    void tess_add_doc_word(                          //test acceptability
    WERD_CHOICE *word_choice  //after context
    );
    void tess_segment_pass1(WERD_RES *word,
    BLOB_CHOICE_LIST_CLIST *blob_choices);
    void tess_segment_pass2(WERD_RES *word,
    BLOB_CHOICE_LIST_CLIST *blob_choices);
    BOOL8 tess_acceptable_word(                           //test acceptability
    WERD_CHOICE *word_choice,  //after context
    WERD_CHOICE *raw_choice    //before context
    );
    //// applybox.cpp //////////////////////////////////////////////////////
    // Applies the box file based on the image name fname, and resegments
    // the words in the block_list (page), with:
    // blob-mode: one blob per line in the box file, words as input.
    // word/line-mode: one blob per space-delimited unit after the #, and one word
    // per line in the box file. (See comment above for box file format.)
    // If find_segmentation is true, (word/line mode) then the classifier is used
    // to re-segment words/lines to match the space-delimited truth string for
    // each box. In this case, the input box may be for a word or even a whole
    // text line, and the output words will contain multiple blobs corresponding
    // to the space-delimited input string.
    // With find_segmentation false, no classifier is needed, but the chopper
    // can still be used to correctly segment touching characters with the help
    // of the input boxes.
    // In the returned PAGE_RES, the WERD_RES are setup as they would be returned
    // from normal classification, ie. with a word, chopped_word, rebuild_word,
    // seam_array, denorm, box_word, and best_state, but NO best_choice or
    // raw_choice, as they would require a UNICHARSET, which we aim to avoid.
    // Instead, the correct_text member of WERD_RES is set, and this may be later
    // converted to a best_choice using CorrectClassifyWords. CorrectClassifyWords
    // is not required before calling ApplyBoxTraining.
    PAGE_RES* ApplyBoxes(const STRING& fname, bool find_segmentation,
    BLOCK_LIST *block_list);

    // Builds a PAGE_RES from the block_list in the way required for ApplyBoxes:
    // All fuzzy spaces are removed, and all the words are maximally chopped.
    PAGE_RES* SetupApplyBoxes(BLOCK_LIST *block_list);
    // Tests the chopper by exhaustively running chop_one_blob.
    // The word_res will contain filled chopped_word, seam_array, denorm,
    // box_word and best_state for the maximally chopped word.
    void MaximallyChopWord(BLOCK* block, ROW* row, WERD_RES* word_res);
    // Gather consecutive blobs that match the given box into the best_state
    // and corresponding correct_text.
    // Fights over which box owns which blobs are settled by pre-chopping and
    // applying the blobs to box or next_box with the least non-overlap.
    // Returns false if the box was in error, which can only be caused by
    // failing to find an appropriate blob for a box.
    // This means that occasionally, blobs may be incorrectly segmented if the
    // chopper fails to find a suitable chop point.
    bool ResegmentCharBox(PAGE_RES* page_res,
    const TBOX& box, const TBOX& next_box,
    const char* correct_text);
    // Consume all source blobs that strongly overlap the given box,
    // putting them into a new word, with the correct_text label.
    // Fights over which box owns which blobs are settled by
    // applying the blobs to box or next_box with the least non-overlap.
    // Returns false if the box was in error, which can only be caused by
    // failing to find an overlapping blob for a box.
    bool ResegmentWordBox(BLOCK_LIST *block_list,
    const TBOX& box, const TBOX& next_box,
    const char* correct_text);
    // Resegments the words by running the classifier in an attempt to find the
    // correct segmentation that produces the required string.
    void ReSegmentByClassification(PAGE_RES* page_res);
    // Converts the space-delimited string of utf8 text to a vector of UNICHAR_ID.
    // Returns false if an invalid UNICHAR_ID is encountered.
    bool ConvertStringToUnichars(const char* utf8,
    GenericVector<UNICHAR_ID>* class_ids);
    // Resegments the word to achieve the target_text from the classifier.
    // Returns false if the re-segmentation fails.
    // Uses brute-force combination of upto kMaxGroupSize adjacent blobs, and
    // applies a full search on the classifier results to find the best classified
    // segmentation. As a compromise to obtain better recall, 1-1 ambigiguity
    // substitutions ARE used.
    bool FindSegmentation(const GenericVector<UNICHAR_ID>& target_text,
    WERD_RES* word_res);
    // Recursive helper to find a match to the target_text (from text_index
    // position) in the choices (from choices_pos position).
    // Choices is an array of GenericVectors, of length choices_length, with each
    // element representing a starting position in the word, and the
    // GenericVector holding classification results for a sequence of consecutive
    // blobs, with index 0 being a single blob, index 1 being 2 blobs etc.
    void SearchForText(const GenericVector<BLOB_CHOICE_LIST*>* choices,
    int choices_pos, int choices_length,
    const GenericVector<UNICHAR_ID>& target_text,
    int text_index,
    float rating, GenericVector<int>* segmentation,
    float* best_rating, GenericVector<int>* best_segmentation);
    // Counts up the labelled words and the blobs within.
    // Deletes all unused or emptied words, counting the unused ones.
    // Resets W_BOL and W_EOL flags correctly.
    // Builds the rebuild_word and rebuilds the box_word.
    void TidyUp(PAGE_RES* page_res);
    // Logs a bad box by line in the box file and box coords.
    void ReportFailedBox(int boxfile_lineno, TBOX box, const char *box_ch,
    const char *err_msg);
    // Creates a fake best_choice entry in each WERD_RES with the correct text.
    void CorrectClassifyWords(PAGE_RES* page_res);
    // Call LearnWord to extract features for labelled blobs within each word.
    // Features are written to the given filename.
    void ApplyBoxTraining(const STRING& filename, PAGE_RES* page_res);

    //// fixxht.cpp ///////////////////////////////////////////////////////
    // Returns the number of misfit blob tops in this word.
    int CountMisfitTops(WERD_RES *word_res);
    // Returns a new x-height in pixels (original image coords) that is
    // maximally compatible with the result in word_res.
    // Returns 0.0f if no x-height is found that is better than the current
    // estimate.
    float ComputeCompatibleXheight(WERD_RES *word_res);


    //// ambigsrecog.cpp /////////////////////////////////////////////////////////
    FILE *init_recog_training(const STRING &fname);
    void recog_training_segmented(const STRING &fname,
    PAGE_RES *page_res,
    volatile ETEXT_DESC *monitor,
    FILE *output_file);
    void ambigs_classify_and_output(WERD_RES *werd_res,
    ROW_RES *row_res,
    BLOCK_RES *block_res,
    const char *label,
    FILE *output_file);

    inline CubeRecoContext *GetCubeRecoContext() {
        return cube_cntxt_;
    }

*/

}