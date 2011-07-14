<?php

namespace TesseractOcr\Api;

class TessBaseAPI {


    // Tesseract
    protected $tesseract_ = null;       ///< The underlying data object.
    // Tesseract*
    protected $osd_tesseract_  = null;   ///< For orientation & script detection.
    // ImageThresholder*
    protected $thresholder_ = null;     ///< Image thresholding module.
    // BLOCK_LIST*
    protected $block_list_ = null;      ///< The page layout.
    // PAGE_RES*
    protected $page_res_ = null;        ///< The page-level data.
    //STRING*
    protected $input_file_ = null;      ///< Name used by training code.
    protected $output_file_ = null;     ///< Name used by debug code.
    protected $datapath_ = null;        ///< Current location of tessdata.
    protected $language_ = null;        ///< Last initialized language.
    // OcrEngineMode
    protected $last_oem_requested_;  ///< Last ocr language mode requested.
    // bool
    protected $recognition_done_ = false;   ///< page_res_ contains recognition data.
    // TruthCallback
    protected $truth_cb_ = null;           /// fxn for setting truth_* in WERD_RES

    /**
    * @defgroup ThresholderParams
    * Parameters saved from the Thresholder. Needed to rebuild coordinates.
    * @return int
    */
    /* @{ */
    protected $rect_left_ = 0;
    protected $rect_top_ = 0;
    protected $rect_width_ = 0;
    protected $rect_height_ = 0;
    protected $image_width_ = 0;
    protected $image_height_ = 0;
    /* @} */

    public function __construct() {
        $this->last_oem_requested_ = OcrEngineMode::OEM_DEFAULT();
    }

    public function __destruct() {
        $this->End();
    }

    /**
     * Returns the version identifier as a static string. Do not delete.
     * @return const char*
     */
    public static function Version() {
        throw new \Exception("Versão indefineda");
        return VERSION;
    }

    /**
     * Set the name of the input file. Needed only for training and
     * reading a UNLV zone file.
     */
    public function SetInputName(/*const char**/ $name) {
        if ($this->input_file_ === null) {
            $this->input_file_ = new STRING($name);
        } else {
            $this->input_file_ = $name;
        }
    }

    /** Set the name of the bonus output files. Needed only for debugging. */
    public function SetOutputName(/*const char**/ $name) {
        if ($this->output_file_ === null) {
            $this->output_file_ = new STRING($name);
        } else {
            $this->output_file_ = $name;
        }
    }

    /**
     * Set the value of an internal "parameter."
     * Supply the name of the parameter and the value as a string, just as
     * you would in a config file.
     * Returns false if the name lookup failed.
     * Eg SetVariable("tessedit_char_blacklist", "xyz"); to ignore x, y and z.
     * Or SetVariable("bln_numericmode", "1"); to set numeric-only mode.
     * SetVariable may be used before Init, but settings will revert to
     * defaults on End().
     * TODO(rays) Add a command-line option to dump the parameters to stdout
     * and add a pointer to it in the FAQ
     *
     * Note: Must be called after Init().
     *
     * @return boolean
     */
    public function SetVariable(/*const char**/ $name, /*const char**/ $value) {
        if ($this->tesseract_ === null) {
            $this->tesseract_ = new Tesseract();
        }
        return ParamUtils::SetParam($name, $value, false, $tesseract_->params());
    }

    // Returns true if the parameter was found among Tesseract parameters.
    // Fills in value with the value of the parameter.
    public function GetIntVariable(/*const char*/ /***/$name, /*int **/&$value) {
        $p = ParamUtils::FindParam/*<IntParam>*/(
        $name, $this->GlobalParams()->int_params, $this->tesseract_->params()->int_params);
        if ($p === null) {
            return false;
        }
        $value = /*(inT32)*/ $p;
        return true;
    }
    public function GetBoolVariable(/*const char **/$name, /*bool **/&$value) {
        $p = ParamUtils::FindParam/*<BoolParam>*/(
        $name, $this->GlobalParams()->bool_params, $this->tesseract_->params()->bool_params);
        if ($p === null) {
            return false;
        }
        $value = /*(BOOL8)*/ $p;
        return true;
    }
    public function GetDoubleVariable(/*const char **/$name, /*double **/$value) {

    }
    // Returns the pointer to the string that represents the value of the
    // parameter if it was found among Tesseract parameters.
    // @return char *
    public function GetStringVariable(/*const char **/$name) {

    }

    // Print Tesseract parameters to the given file.
    public function PrintVariables(/*FILE **/$fp) {

    }
    // Get value of named variable as a string, if it exists.
    // @return boolean
    public function GetVariableAsString(/*const char **/$name, STRING $val) {

    }

    /**
     * Instances are now mostly thread-safe and totally independent,
     * but some global parameters remain. Basically it is safe to use multiple
     * TessBaseAPIs in different threads in parallel, UNLESS:
     * you use SetVariable on some of the Params in classify and textord.
     * If you do, then the effect will be to change it for all your instances.
     *
     * Start tesseract. Returns zero on success and -1 on failure.
     * NOTE that the only members that may be called before Init are those
     * listed above here in the class definition.
     *
     * The datapath must be the name of the parent directory of tessdata and
     * must end in / . Any name after the last / will be stripped.
     * The language is (usually) an ISO 639-3 string or NULL will default to eng.
     * It is entirely safe (and eventually will be efficient too) to call
     * Init multiple times on the same instance to change language, or just
     * to reset the classifier.
     * WARNING: On changing languages, all Tesseract parameters are reset
     * back to their default values. (Which may vary between languages.)
     * If you have a rare need to set a Variable that controls
     * initialization for a second call to Init you should explicitly
     * call End() and then use SetVariable before Init. This is only a very
     * rare use case, since there are very few uses that require any parameters
     * to be set before Init.
     *
     * @return int
     */
    public function Init(/*const char**/ $datapath, /*const char**/ $language, OcrEngineMode $mode,
    /*char **/$configs, /*int*/ $configs_size,
    /*const GenericVector<STRING> **/$vars_vec,
    /*const GenericVector<STRING> **/$vars_values,
    /*bool*/ $set_only_init_params) {

    }
    // @return int
    public function Init(/*const char**/ $datapath, /*const char**/ $language, OcrEngineMode $oem) {
        return Init(datapath, language, oem, NULL, 0, NULL, NULL, false);
    }
    /*public function Init(const char* datapath, const char* language) {
        return Init(datapath, language, OEM_DEFAULT, NULL, 0, NULL, NULL, false);
    }*/

    /**
     * Init only the lang model component of Tesseract. The only functions
     * that work after this init are SetVariable and IsValidWord.
     * WARNING: temporary! This function will be removed from here and placed
     * in a separate API at some future time.
     * @return int
     */
    public function InitLangMod(/*const char**/ $datapath, /*const char**/ $language) {

    }

    // Init only for page layout analysis. Use only for calls to SetImage and
    // AnalysePage. Calls that attempt recognition will generate an error.
    public function InitForAnalysePage() {

    }

    /**
     * Read a "config" file containing a set of variable, value pairs.
     * Searches the standard places: tessdata/configs, tessdata/tessconfigs
     * and also accepts a relative or absolute path name.
     * If init_only is true, only sets the parameters marked with a special
     * INIT flag, which are typically of functional/algorithmic effect
     * rather than debug effect. Used to separate debug settings from
     * working settings.
     */
    public function ReadConfigFile(/*const char**/ $filename, /*bool*/ $init_only) {

    }

    /**
     * Set the current page segmentation mode. Defaults to PSM_SINGLE_BLOCK.
     * The mode is stored as an IntParam so it can also be modified by
     * ReadConfigFile or SetVariable("tessedit_pageseg_mode", mode as string).
     */
    public function  SetPageSegMode(PageSegMode $mode);

    /** Return the current page segmentation mode.
     * @return PageSegMode
     */
    public function GetPageSegMode() {

    }

    /**
     * Recognize a rectangle from an image and return the result as a string.
     * May be called many times for a single Init.
     * Currently has no error checking.
     * Greyscale of 8 and color of 24 or 32 bits per pixel may be given.
     * Palette color images will not work properly and must be converted to
     * 24 bit.
     * Binary images of 1 bit per pixel may also be given but they must be
     * byte packed with the MSB of the first byte being the first pixel, and a
     * 1 represents WHITE. For binary images set bytes_per_pixel=0.
     * The recognized text is returned as a char* which is coded
     * as UTF8 and must be freed with the delete [] operator.
     *
     * Note that TesseractRect is the simplified convenience interface.
     * For advanced uses, use SetImage, (optionally) SetRectangle, Recognize,
     * and one or more of the Get*Text functions below.
     * @return char*
     */
    public function TesseractRect(/*const unsigned char**/ $imagedata,
    /*int*/ $bytes_per_pixel, /*int*/ $bytes_per_line,
    /*int*/ $left, /*int*/ $top, /*int*/ $width, /*int*/ $height) {

    }

    /**
     * Call between pages or documents etc to free up memory and forget
     * adaptive data.
     */
    public function ClearAdaptiveClassifier();

    /**
     * @defgroup AdvancedAPI Advanced API
     * The following methods break TesseractRect into pieces, so you can
     * get hold of the thresholded image, get the text in different formats,
     * get bounding boxes, confidences etc.
     */
    /* @{ */

    /**
     * Provide an image for Tesseract to recognize. Format is as
     * TesseractRect above. Does not copy the image buffer, or take
     * ownership. The source image may be destroyed after Recognize is called,
     * either explicitly or implicitly via one of the Get*Text functions.
     * SetImage clears all recognition results, and sets the rectangle to the
     * full image, so it may be followed immediately by a GetUTF8Text, and it
     * will automatically perform recognition.
     */
    public function SetImage(/*const unsigned char**/ $imagedata, /*int*/ $width, /*int*/ $height,
    /*int*/ $bytes_per_pixel, /*int*/ $bytes_per_line) {

    }

    /**
     * Provide an image for Tesseract to recognize. As with SetImage above,
     * Tesseract doesn't take a copy or ownership or pixDestroy the image, so
     * it must persist until after Recognize.
     * Pix vs raw, which to use?
     * Use Pix where possible. A future version of Tesseract may choose to use Pix
     * as its internal representation and discard IMAGE altogether.
     * Because of that, an implementation that sources and targets Pix may end up
     * with less copies than an implementation that does not.
     */
    public function SetImage(/*const Pix**/ $pix) {

    }

    /**
     * Restrict recognition to a sub-rectangle of the image. Call after SetImage.
     * Each SetRectangle clears the recogntion results so multiple rectangles
     * can be recognized with the same image.
     */
    public function SetRectangle(/*int*/ $left, /*int*/ $top, /*int*/ $width, /*int*/ $height) {

    }

    /**
     * In extreme cases only, usually with a subclass of Thresholder, it
     * is possible to provide a different Thresholder. The Thresholder may
     * be preloaded with an image, settings etc, or they may be set after.
     * Note that Tesseract takes ownership of the Thresholder and will
     * delete it when it it is replaced or the API is destructed.
     */
    public function SetThresholder(ImageThresholder $thresholder) {
        if ($thresholder_ != NULL) {
            $this->thresholder_ = null;
        }
        $this->thresholder_ = $thresholder;
        $this->ClearResults();
    }

    /**
     * Get a copy of the internal thresholded image from Tesseract.
     * Caller takes ownership of the Pix and must pixDestroy it.
     * May be called any time after SetImage, or after TesseractRect.
     * @return Pix
     */
    public function GetThresholdedImage();

    /**
     * Get the result of page layout analysis as a leptonica-style
     * Boxa, Pixa pair, in reading order.
     * Can be called before or after Recognize.
     * @return Boxa
     */
    public function GetRegions(Pixa $pixa) {

    }

    /**
     * Get the textlines as a leptonica-style
     * Boxa, Pixa pair, in reading order.
     * Can be called before or after Recognize.
     * If blockids is not NULL, the block-id of each line is also returned
     * as an array of one element per line. delete [] after use.
     */
    public function GetTextlines(Pixa $pixa, /*int***/ $blockids) {

    }

    /**
     * Get the words as a leptonica-style
     * Boxa, Pixa pair, in reading order.
     * Can be called before or after Recognize.
     * @return Boxa*
     */
    public function GetWords(Pixa $pixa) {

    }

    // Gets the individual connected (text) components (created
    // after pages segmentation step, but before recognition)
    // as a leptonica-style Boxa, Pixa pair, in reading order.
    // Can be called before or after Recognize.
    // Note: the caller is responsible for calling boxaDestroy()
    // on the returned Boxa array and pixaDestroy() on cc array.
    // @return Boxa
    public function GetConnectedComponents(Pixa $cc) {

    }

    // Get the given level kind of components (block, textline, word etc.) as a
    // leptonica-style Boxa, Pixa pair, in reading order.
    // Can be called before or after Recognize.
    // If blockids is not NULL, the block-id of each component is also returned
    // as an array of one element per component. delete [] after use.
    // @return Boxa
    public function GetComponentImages(PageIteratorLevel $level,
    Pixa $pixa, /*int***/ $blockids) {

    }

    /**
     * Dump the internal binary image to a PGM file.
     * @deprecated Use GetThresholdedImage and write the image using pixWrite
     * instead if possible.
     */
    public function DumpPGM(/*const char**/ $filename) {

    }

    // Runs page layout analysis in the mode set by SetPageSegMode.
    // May optionally be called prior to Recognize to get access to just
    // the page layout results. Returns an iterator to the results.
    // Returns NULL on error.
    // The returned iterator must be deleted after use.
    // WARNING! This class points to data held within the TessBaseAPI class, and
    // therefore can only be used while the TessBaseAPI class still exists and
    // has not been subjected to a call of Init, SetImage, Recognize, Clear, End
    // DetectOS, or anything else that changes the internal PAGE_RES.
    // @return PageIterator
    public function AnalyseLayout() {

    }

    /**
     * Recognize the image from SetAndThresholdImage, generating Tesseract
     * internal structures. Returns 0 on success.
     * Optional. The Get*Text functions below will call Recognize if needed.
     * After Recognize, the output is kept internally until the next SetImage.
     */
    public function Recognize(ETEXT_DESC $monitor) {

    }

    /**
     * Methods to retrieve information after SetAndThresholdImage(),
     * Recognize() or TesseractRect(). (Recognize is called implicitly if needed.)
     */

    /** Variant on Recognize used for testing chopper. */
    public function RecognizeForChopTest(ETEXT_DESC $monitor) {

    }

    /**
     * Recognizes all the pages in the named file, as a multi-page tiff or
     * list of filenames, or single image, and gets the appropriate kind of text
     * according to parameters: tessedit_create_boxfile,
     * tessedit_make_boxes_from_boxes, tessedit_write_unlv, tessedit_create_hocr.
     * Calls ProcessPage on each page in the input file, which may be a
     * multi-page tiff, single-page other file format, or a plain text list of
     * images to read. If tessedit_page_number is non-negative, processing begins
     * at that page of a multi-page tiff file, or filelist.
     * The text is returned in text_out. Returns false on error.
     * If non-zero timeout_millisec terminates processing after the timeout on
     * a single page.
     * If non-NULL and non-empty, and some page fails for some reason,
     * the page is reprocessed with the retry_config config file. Useful
     * for interactively debugging a bad page.
     *
     * @return boolean
     */
    public function ProcessPages(/*const char**/ $filename,
    /*const char**/ $retry_config, /*int*/ $timeout_millisec,
    STRING $text_out) {

    }

    /**
     * Recognizes a single page for ProcessPages, appending the text to text_out.
     * The pix is the image processed - filename and page_index are metadata
     * used by side-effect processes, such as reading a box file or formatting
     * as hOCR.
     * If non-zero timeout_millisec terminates processing after the timeout.
     * If non-NULL and non-empty, and some page fails for some reason,
     * the page is reprocessed with the retry_config config file. Useful
     * for interactively debugging a bad page.
     * The text is returned in text_out. Returns false on error.
     *
     * @return boolean
     */
    public function ProcessPage(Pix $pix, /*int*/ $page_index, /*const char**/ $filename,
    /*const char**/ $retry_config, /*int*/ $timeout_millisec,
    STRING $text_out) {

    }

    // Get an iterator to the results of LayoutAnalysis and/or Recognize.
    // The returned iterator must be deleted after use.
    // WARNING! This class points to data held within the TessBaseAPI class, and
    // therefore can only be used while the TessBaseAPI class still exists and
    // has not been subjected to a call of Init, SetImage, Recognize, Clear, End
    // DetectOS, or anything else that changes the internal PAGE_RES.
    // @return ResultIterator
    public function GetIterator() {

    }

    /**
     * The recognized text is returned as a char* which is coded
     * as UTF8 and must be freed with the delete [] operator.
     * @return char*
     */
    public function GetUTF8Text() {

    }

    /**
     * Make a HTML-formatted string with hOCR markup from the internal
     * data structures.
     * page_number is 0-based but will appear in the output as 1-based.
     * @return char*
     */
    public function GetHOCRText(/*int*/ $page_number) {

    }
    /**
     * The recognized text is returned as a char* which is coded in the same
     * format as a box file used in training. Returned string must be freed with
     * the delete [] operator.
     * Constructs coordinates in the original image - not just the rectangle.
     * page_number is a 0-based page index that will appear in the box file.
     * @return char
     */
    public function GetBoxText(/*int*/ $page_number) {

    }

    /**
     * The recognized text is returned as a char* which is coded
     * as UNLV format Latin-1 with specific reject and suspect codes
     * and must be freed with the delete [] operator.
     */
    public function GetUNLVText() {

    }
    /** Returns the (average) confidence value between 0 and 100.
     * @return int
     */
    public function MeanTextConf() {

    }

    /**
     * Returns all word confidences (between 0 and 100) in an array, terminated
     * by -1.  The calling function must delete [] after use.
     * The number of confidences should correspond to the number of space-
     * delimited words in GetUTF8Text.
     * @return int
     */
    public function AllWordConfidences() {

    }

    /**
     * Applies the given word to the adaptive classifier if possible.
     * The word must be SPACE-DELIMITED UTF-8 - l i k e t h i s , so it can
     * tell the boundaries of the graphemes.
     * Assumes that SetImage/SetRectangle have been used to set the image
     * to the given word. The mode arg should be PSM_SINGLE_WORD or
     * PSM_CIRCLE_WORD, as that will be used to control layout analysis.
     * The currently set PageSegMode is preserved.
     * Returns false if adaption was not possible for some reason.
     * @return int
     */
    public function AdaptToWordStr(PageSegMode $mode, /*const char**/ $wordstr) {

    }

    /**
     * Free up recognition results and any stored image data, without actually
     * freeing any recognition data that would be time-consuming to reload.
     * Afterwards, you must call SetImage or TesseractRect before doing
     * any Recognize or Get* operation.
     */
    public function Clear() {

    }

    /**
     * Close down tesseract and free up all memory. End() is equivalent to
     * destructing and reconstructing your TessBaseAPI.
     * Once End() has been used, none of the other API functions may be used
     * other than Init and anything declared above it in the class definition.
     */
    public function End() {

    }

    /**
     * Check whether a word is valid according to Tesseract's language model
     * @return 0 if the word is invalid, non-zero if valid.
     * @warning temporary! This function will be removed from here and placed
     * in a separate API at some future time.
     * @return int
     */
    public function IsValidWord(/*const char **/$word) {

    }

    /**
	 * @return int
     */
    public function GetTextDirection(/*int**/ $out_offset, /*float**/ $out_slope) {

    }

    /** Sets Dict::letter_is_okay_ function to point to the given function. */
    public function SetDictFunc(DictFunc $f) {

    }

    /** Sets Dict::probability_in_context_ function to point to the given
     * function.
     */
    public function SetProbabilityInContextFunc(ProbabilityInContextFunc $f) {

    }

    /**
     * Estimates the Orientation And Script of the image.
     * @return true if the image was processed successfully.
     */
    public function DetectOS(OSResults $o) {

    }

    /** This method returns the features associated with the input image. */
    public function GetFeaturesForBlob(TBLOB $blob, /*const DENORM&*/ DENORM $denorm,
    INT_FEATURE_ARRAY $int_features,
    /*int**/ $num_features, /*int**/ $FeatureOutlineIndex) {

    }

    // This method returns the row to which a box of specified dimensions would
    // belong. If no good match is found, it returns NULL.
    // @return ROW
    static public function FindRowForBox(/*BLOCK_LIST**/ $blocks, /*int*/ $left, /*int*/ $top,
    /*int*/ $right, /*int*/ $bottom) {

    }

    // Method to run adaptive classifier on a blob.
    // It returns at max num_max_matches results.
    public function RunAdaptiveClassifier(TBLOB $blob, /*const DENORM&*/ DENORM $denorm,
    /*int*/ $num_max_matches,
    /*int**/ $unichar_ids,
    /*float**/ $ratings,
    /*int**/ $num_matches_returned) {

    }

    // This method returns the string form of the specified unichar.
    // @return int
    public function GetUnichar(/*int*/ $unichar_id) {

    }

    /** Return the pointer to the i-th dawg loaded into tesseract_ object.
     * @return
     */
    public function GetDawg(/*int*/ $i) {

    }

    /** Return the number of dawgs loaded into tesseract_ object.
     * @return int
     */
    public function NumDawgs() {

    }

    /** Return the language used in the last valid initialization.
     * @return char
     */
    public function GetLastInitLanguage() {

    }

    // Returns a ROW object created from the input row specification.
    // @return ROW
    public static function MakeTessOCRRow(/*float*/ $baseline, /*float*/ $xheight,
    /*float*/ $descender, /*float*/ $ascender) {

    }

    // Returns a TBLOB corresponding to the entire input image.
    // @return TBLOB
    public static function MakeTBLOB(Pix $pix) {

    }

    // This method baseline normalizes a TBLOB in-place. The input row is used
    // for normalization. The denorm is an optional parameter in which the
    // normalization-antidote is returned.
    public static function NormalizeTBLOB(TBLOB $tblob, ROW $row,
    /*bool*/ $numeric_mode, DENORM $denorm) {

    }

    // @return Tesseract
    public function tesseract() {
        return tesseract_;
    }

    public function InitTruthCallback(TruthCallback $cb) {
        $this->truth_cb_ = $cb;
    }

    // Return a pointer to underlying CubeRecoContext object if present.
    // @return CubeRecoContext
    public function GetCubeRecoContext() {

    }

    public function set_min_orientation_margin(/*double*/ $margin) {

    }

    // Return text orientation of each block as determined by an earlier run
    // of layout analysis.
    public function GetBlockTextOrientations(/*int***/ $block_orientation,
    /*bool***/ $vertical_writing) {

    }

    /** Find lines from the image making the BLOCK_LIST.
     * @return BLOCK_LIST
     */
    public function FindLinesCreateBlockList() {

    }

    /**
     * Delete a block list.
     * This is to keep BLOCK_LIST pointer opaque
     * and let go of including the other headers.
     */
    public static function DeleteBlockList(BLOCK_LIST $block_list) {

    }
    /* @} */

    /** Common code for setting the image. Returns true if Init has been called.
     * @return bool
     */
    protected function InternalSetImage() {

    }

    /**
     * Run the thresholder to make the thresholded image. If pix is not NULL,
     * the source is thresholded to pix instead of the internal IMAGE.
     */
    protected function Threshold(Pix $pix) {

    }

    /**
     * Find lines from the image making the BLOCK_LIST.
     * @return 0 on success.
     */
    protected function FindLines() {

    }

    /** Delete the pageres and block list ready for a new page. */
    protected function ClearResults() {

    }

    /**
     * Return the length of the output text string, as UTF8, assuming
     * one newline per line and one per block, with a terminator,
     * and assuming a single character reject marker for each rejected character.
     * Also return the number of recognized blobs in blob_count.
     */
    protected function TextLength(/*int**/ $blob_count) {

    }

    /** @defgroup ocropusAddOns ocropus add-ons */
    /* @{ */

    /**
     * Adapt to recognize the current image as the given character.
     * The image must be preloaded and be just an image of a single character.
     */
    protected function AdaptToCharacter(/*const char **/$unichar_repr,
    /*int*/ $length,
    /*float*/ $baseline,
    /*float*/ $xheight,
    /*float*/ $descender,
    /*float*/ $ascender) {

    }

    /** Recognize text doing one pass only, using settings for a given pass. */
    // @return PAGE_RES
    protected function RecognitionPass1(BLOCK_LIST $block_list) {

    }
    protected function RecognitionPass2(BLOCK_LIST $block_list, PAGE_RES $pass1_result) {

    }

    /**
     * Extract the OCR results, costs (penalty points for uncertainty),
     * and the bounding boxes of the characters.
     */
    protected static function TesseractExtractResult(/*char***/ $text,
    /*int***/ $lengths,
    /*float***/ $costs,
    /*int***/ $x0,
    /*int***/ $y0,
    /*int***/ $x1,
    /*int***/ $y1,
    /*PAGE_RES**/ PAGE_RES $page_res) {

    }

    // @return const PAGE_RES*
    protected function GetPageRes() {
        return $this->page_res_;
    }


}