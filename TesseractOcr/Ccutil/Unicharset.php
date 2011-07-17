<?php
namespace TesseractOcr\Ccutil;

use TesseractOcr\Ccmain\Tesseract;

require_once 'TesseractOcr/Ccutil/Unicharmap.php';
require_once 'TesseractOcr/Ccutil/CharFragment.php';
require_once 'TesseractOcr/Ccutil/Helpers.php';
require_once 'TesseractOcr/Ccutil/Host.php';

// The UNICHARSET class is an utility class for Tesseract that holds the
// set of characters that are used by the engine. Each character is identified
// by a unique number, from 0 to (size - 1).
class Unicharset {

    const ISALPHA_MASK = 0x1;
    const ISLOWER_MASK = 0x2;
    const ISUPPER_MASK = 0x4;
    const ISDIGIT_MASK = 0x8;
    const ISPUNCTUATION_MASK = 0x10;

    // Y coordinate threshold for determining cap-height vs x-height.
    // TODO(rays) Bring the global definition down to the ccutil library level,
    // so this constant is relative to some other constants.
    const kMeanlineThreshold = 220;
    // Let C be the number of alpha chars for which all tops exceed
    // kMeanlineThreshold, and X the number of alpha chars for which all tops
    // are below kMeanlineThreshold, then if X > C * kMinXHeightFraction or
    // more than half the alpha characters have upper or lower case, then
    // the unicharset "has x-height".
    const kMinXHeightFraction = 0.25;

    //UNICHAR_SLOT*
    private $unichars = null;

    /**
     * @var TesseractOcr\Ccutil\Unicharmap
     */
    private $ids;
    //int
    private $size_used = 0;
    //int
    private $size_reserved = 0;
    //char**
    private $script_table = null;
    //int
    private $script_table_size_used = 0;
    //int
    private $script_table_size_reserved;
    //const char*
    private $null_script = "null";
    // True if the unichars have their tops/bottoms set.
    //bool
    private $top_bottom_set_;
    // True if the unicharset has significant upper/lower case chars.
    //bool
    private $script_has_upper_lower_;
    // True if the unicharset has a significant mean-line with significant
    // ascenders above that.
    //bool
    private $script_has_xheight_;

    // A few convenient script name-to-id mapping without using hash.
    // These are initialized when unicharset file is loaded.  Anything
    // missing from this list can be looked up using get_script_id_from_name.
    //int
    private $null_sid_;
    //int
    private $common_sid_;
    //int
    private $latin_sid_;
    //int
    private $cyrillic_sid_;
    //int
    private $greek_sid_;
    //int
    private $han_sid_;
    //int
    private $hiragana_sid_;
    //int
    private $katakana_sid_;
    // The most frequently occurring script in the charset.
    //int
    private $default_sid_;

    public function __construct() {
        $this->ids = new Unicharmap();
        $this->clear();
    }

    public function __destruct() {
        $this->clear();
    }

    // Return the UNICHAR_ID of a given unichar representation within the
    // UNICHARSET.
    //const UNICHAR_ID
    public function unichar_to_id(/* const char* const  */$unichar_repr, $length = null) {
        if ($length === null) {
            return $this->ids->contains($unichar_repr) ?
                $this->ids->unichar_to_id($unichar_repr) : -1; //INVALID_UNICHAR_ID;
        }

        if ($length  > 0 && length <= UNICHAR_LEN) {
            throw new \Exception("");
        }
        return $this->ids->contains($unichar_repr, $length) ?
            $this->ids->unichar_to_id($unichar_repr, $length) : -1; //INVALID_UNICHAR_ID;

    }

    // Return the UNICHAR_ID of a given unichar representation within the
    // UNICHARSET. Only the first length characters from unichar_repr are used.
    //const UNICHAR_ID
    //public function unichar_to_id(/* const char* const  */$unichar_repr,
    //                               /* int  */$length) {

    //}

    // Return the minimum number of bytes that matches a legal UNICHAR_ID,
    // while leaving a legal UNICHAR_ID afterwards. In other words, if there
    // is both a short and a long match to the string, return the length that
    // ensures there is a legal match after it.
    //int
    public function step(/* const char*  */ $str) {

    }

    // Return the unichar representation corresponding to the given UNICHAR_ID
    // within the UNICHARSET.
    //const char* const
    public function id_to_unichar(/* UNICHAR_ID  */$id) {

    }

    /*   // Return a STRING that reformats the utf8 str into the str followed
     // by its hex unicodes.
    static STRING debug_utf8_str(const char* str);

    // Return a STRING containing debug information on the unichar, including
    // the id_to_unichar, its hex unicodes and the properties.
    STRING debug_str(UNICHAR_ID id) const;
    STRING debug_str(const char * unichar_repr) const {
    return debug_str(unichar_to_id(unichar_repr));
    }*/

    // Add a unichar representation to the set.
    public function unichar_insert(/* const char* const  */$unichar_repr) {
        if (!$this->ids->contains($unichar_repr)) {
            if (strlen($unichar_repr) > Unichar::UNICHAR_LEN) {
                throw new \Exception(sprintf("Utf8 buffer too big, size=%d for %s\n",
                int(strlen(unichar_repr)), unichar_repr));

/*                 fprintf(stderr, "Utf8 buffer too big, size=%d for %s\n",
                int(strlen(unichar_repr)), unichar_repr); */
                //return;
            }

            if ($this->size_used == $this->size_reserved) {
                if ($this->size_used == 0) {
                    $this->reserve(8);
                } else {
                    $this->reserve(2 * $this->size_used);
                }
            }

            $this->unichars[$this->size_used]->representation = $unichar_repr;
            $this->set_script($this->size_used, $this->null_script);

            // If the given unichar_repr represents a fragmented character, set
            // fragment property to a pointer to CHAR_FRAGMENT class instance with
            // information parsed from the unichar representation. Use the script
            // of the base unichar for the fragmented character if possible.
            $frag = CharFragment::parse_from_string($unichar_repr);
            $this->unichars[$this->size_used]->properties->fragment = $frag;
            if ($frag !== null && $this->contains_unichar($frag->get_unichar())) {
                $this->unichars[$this->size_used]->properties->script_id =
                $this->get_script($frag->get_unichar());
            }
            $this->unichars[$this->size_used]->properties->enabled = true;
            $this->ids->insert($unichar_repr, $this->size_used);
            ++$this->size_used;
        }
    }


    // Return true if the given unichar id exists within the set.
    // Relies on the fact that unichar ids are contiguous in the unicharset.
    //bool
    public function contains_unichar_id($unichar_id, $length = null) {
	    return $unichar_id != Unichar::INVALID_UNICHAR_ID && $unichar_id < $this->size_used;
    }

    // Return true if the given unichar representation exists within the set.
    public function contains_unichar($unichar_repr, $length = null) {
        if ($length === 0) {
            return false;
        }
        return $this->ids->contains($unichar_repr, $length);
    }

    /*
    // Return true if the given unichar representation corresponds to the given
    // UNICHAR_ID within the set.
    bool eq(UNICHAR_ID unichar_id, const char* const unichar_repr) const;

    // Delete CHAR_FRAGMENTs stored in properties of unichars array.
    void delete_pointers_in_unichars() {
    for (int i = 0; i < size_used; ++i) {
    if (unichars[i].properties.fragment != NULL) {
    delete unichars[i].properties.fragment;
    unichars[i].properties.fragment = NULL;
    }
    }
    }*/

    // Clear the UNICHARSET (all the previous data is lost).
    //void
    public function clear() {
        if ($this->script_table != NULL) {
            unset($this->script_table);
            $this->script_table = null;
            $this->script_table_size_used = 0;
        }
        if ($this->unichars != NULL) {
            unset($this->unichars);
            $this->unichars = null;
        }
        $this->script_table_size_reserved = 0;
        $this->size_reserved = 0;
        $this->size_used = 0;
        $this->ids->clear();
        $this->top_bottom_set_ = false;
        $this->script_has_upper_lower_ = false;
        $this->script_has_xheight_ = false;
        $this->null_sid_ = 0;
        $this->common_sid_ = 0;
        $this->latin_sid_ = 0;
        $this->cyrillic_sid_ = 0;
        $this->greek_sid_ = 0;
        $this->han_sid_ = 0;
        $this->hiragana_sid_ = 0;
        $this->katakana_sid_ = 0;
    }

    // Return the size of the set (the number of different UNICHAR it holds).
    public function size() {
        return $this->size_used;
    }

    // Reserve enough memory space for the given number of UNICHARS
    public function reserve(/* int  */$unichars_number) {
        echo 'verificara necessidade desse alocamento de memoria Unicharset::reverse' . "\n";

        if ($unichars_number > $this->size_reserved) {
            $unichars_new = array(); //new UNICHAR_SLOT[unichars_number];
            for ($i = 0; $i < $this->size_used; ++$i) {
                $unichars_new[$i] = $this->unichars[$i];
            }
            for ($j = $this->size_used; $j < $unichars_number; ++$j) {
                $unichars_new[$j] = new UnicharSlot();
                $unichars_new[$j]->properties->script_id = $this->add_script($this->null_script);
            }

            unset($this->unichars);
            $this->unichars = $unichars_new;
            $this->size_reserved = $unichars_number;
        }
    }

    // Opens the file indicated by filename and saves unicharset to that file.
    // Returns true if the operation is successful.
    // Saves the content of the UNICHARSET to the given file.
    // Returns true if the operation is successful.
    //bool
    public function save_to_file(/* const char * const  */$file) {
        if (is_string($file)) {
            $file = fopen($filename, "w+");
            if ($file === false) {
                return false;
            }
        }

        fprintf($file, "%d\n", $this->size());
        for ($id = 0; $id < $this->size(); ++$id) {
            $min_bottom; $max_bottom; $min_top; $max_top;
            $this->get_top_bottom($id, $min_bottom, $max_bottom, $min_top, max_top);
            $properties = $this->get_properties($id);
            if ($this->id_to_unichar($id) == " ") {
                fprintf($file, "%s %x %s %d\n", "NULL", $properties,
                $this->get_script_from_script_id($this->get_script($id)),
                $this->get_other_case($id));
            } else {
                fprintf($file, "%s %x %d,%d,%d,%d %s %d\t# %s\n",
                    $this->id_to_unichar($id), $properties,
                    $min_bottom, $max_bottom, $min_top, $max_top,
                    $this->get_script_from_script_id($this->get_script($id)),
                    $this->get_other_case($id), $this->debug_str($id));
            }
        }

        fclose($file);

        return true;
    }

    // Opens the file indicated by filename and loads the UNICHARSET
    // from the given file. The previous data is lost.
    // Returns true if the operation is successful.

    // Loads the UNICHARSET from the given file. The previous data is lost.
    // Returns true if the operation is successful.
    // bool
    public function load_from_file(/* FILE * */$file, $skip_fragments = false) {
        $close = is_string($file);
        if (is_string($file)) {
            $file = fopen($file, "r");
            if ($file === false) {
                return false;
            }
        }

        $unicharset_size = 0;
        $buffer = array();

        $this->clear();

        $buffer = fgets($file, 256);
        if ($buffer === false || sscanf($buffer, "%d", $unicharset_size) <= 0) {
            return false;
        }

        $this->reserve($unicharset_size);

        for ($id = 0; $id < $unicharset_size; ++$id) {
            $unichar = '';
            $properties = '';
            $script = array();

            $script = $this->null_script;
            $this->unichars[$id]->properties->other_case = $id;
            $min_bottom = 0;
            $max_bottom = Host::MAX_UINT8; //MAX_UINT8;
            $min_top = 0;
            $max_top = Host::MAX_UINT8; //MAX_UINT8;
            $buffer = fgets($file, 256);

            if ($buffer === false ||
                (sscanf($buffer, "%s %x %d,%d,%d,%d %63s %d", $unichar, $properties,
                              $min_bottom, $max_bottom, $min_top, $max_top,
                              $script, $this->unichars[$id]->properties->other_case) != 8 &&
                sscanf($buffer, "%s %x %63s %d", $unichar, $properties,
                             $script, $this->unichars[$id]->properties->other_case) != 4 &&
                sscanf($buffer, "%s %x %63s", $unichar, $properties, $script) != 3 &&
                sscanf($buffer, "%s %x", $unichar, $properties) != 2)) {

                return false;
            }
            // Skip fragments if needed.
            //CHAR_FRAGMENT
            $frag = null;
            if ($skip_fragments && ($frag = CharFragment::parse_from_string($unichar))) {
                unset($frag);
                continue;
            }

            // Insert unichar into unicharset and set its properties.
            if ($unichar == "NULL") {
                $this->unichar_insert(" ");
            } else {
                $this->unichar_insert($unichar);
            }

            $this->set_isalpha($id, $properties & self::ISALPHA_MASK);
            $this->set_islower($id, $properties & self::ISLOWER_MASK);
            $this->set_isupper($id, $properties & self::ISUPPER_MASK);
            $this->set_isdigit($id, $properties & self::ISDIGIT_MASK);
            $this->set_ispunctuation($id, $properties & self::ISPUNCTUATION_MASK);
            $this->set_isngram($id, false);
            $this->set_script($id, $script);
            $this->unichars[$id]->properties->enabled = true;
            $this->set_top_bottom($id, $min_bottom, $max_bottom, $min_top, $max_top);
        }

        $this->post_load_setup();

        if ($close) {
            fclose($file);
        }
        return true;
    }

    // Sets up internal data after loading the file, based on the char
    // properties. Called from load_from_file, but also needs to be run
    // during set_unicharset_properties.
    public function post_load_setup() {
        // Number of alpha chars with the case property minus those without,
        // in order to determine that half the alpha chars have case.
        $net_case_alphas = 0;
        $x_height_alphas = 0;
        $cap_height_alphas = 0;
        $this->top_bottom_set_ = false;
        for ($id = 0; $id < $this->size_used; ++$id) {
            $min_bottom = 0;
            $max_bottom = Host::MAX_UINT8;
            $min_top = 0;
            $max_top = Host::MAX_UINT8;
            $this->get_top_bottom($id, $min_bottom, $max_bottom, $min_top, $max_top);
            if ($min_top > 0) {
                $this->top_bottom_set_ = true;
            }
            if ($this->get_isalpha($id)) {
                if ($this->get_islower($id) || $this->get_isupper($id)) {
                    ++$net_case_alphas;
                } else {
                    --$net_case_alphas;
                }
                if ($min_top < self::kMeanlineThreshold && $max_top < self::kMeanlineThreshold) {
                    ++$x_height_alphas;
                } else if ($min_top > self::kMeanlineThreshold && $max_top > self::kMeanlineThreshold) {
                    ++$cap_height_alphas;
                }
            }
        }

        $this->script_has_upper_lower_ = $net_case_alphas > 0;
        $this->script_has_xheight_ = $this->script_has_upper_lower_ ||
        $x_height_alphas > $cap_height_alphas * self::kMinXHeightFraction;

        $this->null_sid_ = $this->get_script_id_from_name($this->null_script);
        $this->common_sid_ = $this->get_script_id_from_name("Common");
        $this->latin_sid_ = $this->get_script_id_from_name("Latin");
        $this->cyrillic_sid_ = $this->get_script_id_from_name("Cyrillic");
        $this->greek_sid_ = $this->get_script_id_from_name("Greek");
        $this->han_sid_ = $this->get_script_id_from_name("Han");
        $this->hiragana_sid_ = $this->get_script_id_from_name("Hiragana");
        $this->katakana_sid_ = $this->get_script_id_from_name("Katakana");

        // Compute default script.
        $script_counts = new \SplFixedArray($this->size_used);
        foreach ($script_counts as $key => $value) {
            $script_counts[$key] = 0;
        }
        for ($id = 0; $id < $this->size_used; ++$id) {
            $script_counts[$this->get_script($id)] += 1;
        }


        $this->default_sid_ = 0;
        for ($s = 1; $s < $this->script_table_size_used; ++$s) {
            if ($script_counts[$s] > $script_counts[$this->default_sid_] && $s != $this->common_sid_) {
                $this->default_sid_ = $s;
            }
        }

        unset($script_counts);
    }


    // Returns true if any script entry in the unicharset is for a
    // right_to_left language.
    public function any_right_to_left() {
        for ($id = 0; $id < $this->script_table_size_used; ++$id) {
            if ($this->script_table[$id] == "Arabic" ||
                $this->script_table[$id] == "Hebrew")
            return true;
        }
        return false;
    }

    /*
    // Set a whitelist and/or blacklist of characters to recognize.
    // An empty or NULL whitelist enables everything (minus any blacklist).
    // An empty or NULL blacklist disables nothing.
    // The blacklist overrides the whitelist.
    // Each list is a string of utf8 character strings. Boundaries between
    // unicharset units are worked out automatically, and characters not in
    // the unicharset are silently ignored.
    void set_black_and_whitelist(const char* blacklist, const char* whitelist);
	*/

    // Set the isalpha property of the given unichar to the given value.
    public function set_isalpha($unichar_id, $value) {
        $this->unichars[$unichar_id]->properties->isalpha = $value;
    }

    // Set the islower property of the given unichar to the given value.
    public function set_islower($unichar_id, $value) {
        $this->unichars[$unichar_id]->properties->islower = $value;
    }

    // Set the isupper property of the given unichar to the given value.
    public function set_isupper($unichar_id, $value) {
        $this->unichars[$unichar_id]->properties->isupper = $value;
    }

    // Set the isdigit property of the given unichar to the given value.
    public function set_isdigit($unichar_id, $value) {
        $this->unichars[$unichar_id]->properties->isdigit = $value;
    }

    // Set the ispunctuation property of the given unichar to the given value.
    public function set_ispunctuation($unichar_id, $value) {
        $this->unichars[$unichar_id]->properties->ispunctuation = $value;
    }

    // Set the isngram property of the given unichar to the given value.
    public function set_isngram($unichar_id, $value) {
        $this->unichars[$unichar_id]->properties->isngram = $value;
    }


    // Set the script name of the given unichar to the given value.
    // Value is copied and thus can be a temporary;
    public function set_script($unichar_id, /* const char*  */$value) {
        $this->unichars[$unichar_id]->properties->script_id = $this->add_script($value);
    }

    /*

    // Set other_case unichar id in the properties for the given unichar id.
    void set_other_case(UNICHAR_ID unichar_id, UNICHAR_ID other_case) {
    unichars[unichar_id].properties.other_case = other_case;
    }





    // Return the isdigit property of the given unichar.
    bool get_isdigit(UNICHAR_ID unichar_id) const {
    return unichars[unichar_id].properties.isdigit;
    }

    // Return the ispunctuation property of the given unichar.
    bool get_ispunctuation(UNICHAR_ID unichar_id) const {
    return unichars[unichar_id].properties.ispunctuation;
    }

    // Return the isngram property of the given unichar.
    bool get_isngram(UNICHAR_ID unichar_id) const {
    return unichars[unichar_id].properties.isngram;
    }

    // Returns true if the ids have useful min/max top/bottom values.
    bool top_bottom_useful() const {
    return top_bottom_set_;
    }*/


    // Returns the min and max bottom and top of the given unichar in
    // baseline-normalized coordinates, ie, where the baseline is
    // kBlnBaselineOffset and the meanline is kBlnBaselineOffset + kBlnXHeight
    // (See normalis.h for the definitions).
    public function get_top_bottom($unichar_id,
        &$min_bottom, &$max_bottom,
        &$min_top, &$max_top) {

        $min_bottom = $this->unichars[$unichar_id]->properties->min_bottom;
        $max_bottom = $this->unichars[$unichar_id]->properties->max_bottom;
        $min_top = $this->unichars[$unichar_id]->properties->min_top;
        $max_top = $this->unichars[$unichar_id]->properties->max_top;
    }

    public function set_top_bottom($unichar_id, $min_bottom, $max_bottom, $min_top, $max_top) {
        $this->unichars[$unichar_id]->properties->min_bottom = Helpers::ClipToRange($min_bottom, 0, /*MAX_UINT8*/ 0xff);
        $this->unichars[$unichar_id]->properties->max_bottom = Helpers::ClipToRange($max_bottom, 0, /*MAX_UINT8*/ 0xff);
        $this->unichars[$unichar_id]->properties->min_top = Helpers::ClipToRange($min_top, 0, /*MAX_UINT8*/ 0xff);
        $this->unichars[$unichar_id]->properties->max_top = Helpers::ClipToRange($max_top, 0, /*MAX_UINT8*/ 0xff);
    }

    /*
    // Return the character properties, eg. alpha/upper/lower/digit/punct,
    // as a bit field of unsigned int.
    unsigned int get_properties(UNICHAR_ID unichar_id) const;

    // Return the character property as a single char.  If a character has
    // multiple attributes, the main property is defined by the following order:
    //   upper_case : 'A'
    //   lower_case : 'a'
    //   alpha      : 'x'
    //   digit      : '0'
    //   punctuation: 'p'
    char get_chartype(UNICHAR_ID unichar_id) const;

    // Get other_case unichar id in the properties for the given unichar id.
    UNICHAR_ID get_other_case(UNICHAR_ID unichar_id) const {
    return unichars[unichar_id].properties.other_case;
    }*/

    // Returns UNICHAR_ID of the corresponding lower-case unichar.
    //UNICHAR_ID
    public function to_lower($unichar_id) {
        if ($this->unichars[$unichar_id]->properties->islower) {
            return $unichar_id;
        }
        return $this->unichars[$unichar_id]->properties->other_case;
    }

    /*
    // Returns UNICHAR_ID of the corresponding upper-case unichar.
    UNICHAR_ID to_upper(UNICHAR_ID unichar_id) const {
    if (unichars[unichar_id].properties.isupper) return unichar_id;
    return unichars[unichar_id].properties.other_case;
    }

    // Return a pointer to the CHAR_FRAGMENT class if the given
    // unichar id represents a character fragment.
    const CHAR_FRAGMENT *get_fragment(UNICHAR_ID unichar_id) const {
    return unichars[unichar_id].properties.fragment;
    }*/

    // Return the isalpha property of the given unichar representation.
    // Return the isalpha property of the given unichar.
    public function get_isalpha(/* const char* const  */$unichar_id) {
        if (is_string($unichar_id)) {
            $unichar_id = $this->unichar_to_id($unichar_id);
        }
        //return ;
        return $this->unichars[$unichar_id]->properties->isalpha;
    }


    // Return the islower property of the given unichar representation.

    // Return the islower property of the given unichar.

    // Return the islower property of the given unichar representation.
    // Only the first length characters from unichar_repr are used.
    public function get_islower($unichar_id, $length = null) {
        if (is_string($unichar_id)) {
            $unichar_id = $this->unichar_to_id($unichar_repr, $length);
        }
        return $this->unichars[$unichar_id]->properties->islower;
    }

    // Return the isupper property of the given unichar representation.
    // Only the first length characters from unichar_repr are used.

    // Return the isupper property of the given unichar.

    // Return the isupper property of the given unichar representation.
    public function get_isupper($unichar_id, $length = null) {
        if (is_string($unichar_id)) {
            $unichar_id = $this->unichar_to_id($unichar_repr, $length);
        }
        return $this->unichars[$unichar_id]->properties->isupper;
    }


    // Return the script name of the given unichar representation.
    // Only the first length characters from unichar_repr are used.
    // The returned pointer will always be the same for the same script, it's
    // managed by unicharset and thus MUST NOT be deleted

    // Return the script name of the given unichar representation.
    // The returned pointer will always be the same for the same script, it's
    // managed by unicharset and thus MUST NOT be deleted

    // Return the script name of the given unichar.
    // The returned pointer will always be the same for the same script, it's
    // managed by unicharset and thus MUST NOT be deleted
    public function get_script($unichar_id, $length = null)  {
        if (is_string($unichar_id)) {
            $unichar_id = $this->unichar_to_id($unichar_repr, $length);
        }
        return $this->unichars[$unichar_id]->properties->script_id;
    }


    /*
    // Return the isdigit property of the given unichar representation.
    bool get_isdigit(const char* const unichar_repr) const {
    return get_isdigit(unichar_to_id(unichar_repr));
    }

    // Return the ispunctuation property of the given unichar representation.
    bool get_ispunctuation(const char* const unichar_repr) const {
    return get_ispunctuation(unichar_to_id(unichar_repr));
    }

    // Return the character properties, eg. alpha/upper/lower/digit/punct,
    // of the given unichar representation
    unsigned int get_properties(const char* const unichar_repr) const {
    return get_properties(unichar_to_id(unichar_repr));
    }

    char get_chartype(const char* const unichar_repr) const {
    return get_chartype(unichar_to_id(unichar_repr));
    }



    // Return a pointer to the CHAR_FRAGMENT class struct if the given
    // unichar representation represents a character fragment.
    const CHAR_FRAGMENT *get_fragment(const char* const unichar_repr) const {
    if (unichar_repr == NULL || unichar_repr[0] == '\0' ||
    !ids.contains(unichar_repr)) {
    return NULL;
    }
    return get_fragment(unichar_to_id(unichar_repr));
    }

    // Return the isalpha property of the given unichar representation.
    // Only the first length characters from unichar_repr are used.
    bool get_isalpha(const char* const unichar_repr,
    int length) const {
    return get_isalpha(unichar_to_id(unichar_repr, length));
    }




    // Return the isdigit property of the given unichar representation.
    // Only the first length characters from unichar_repr are used.
    bool get_isdigit(const char* const unichar_repr,
    int length) const {
    return get_isdigit(unichar_to_id(unichar_repr, length));
    }

    // Return the ispunctuation property of the given unichar representation.
    // Only the first length characters from unichar_repr are used.
    bool get_ispunctuation(const char* const unichar_repr,
    int length) const {
    return get_ispunctuation(unichar_to_id(unichar_repr, length));
    }*/

    // Returns the id from the name of the script, or 0 if script is not found.
    // Note that this is an expensive operation since it involves iteratively
    // comparing strings in the script table.  To avoid dependency on STL, we
    // won't use a hash.  Instead, the calling function can use this to lookup
    // and save the ID for relevant scripts for fast comparisons later.
    public function get_script_id_from_name(/* const char*  */$script_name) {
        for ($i = 0; $i < $this->script_table_size_used; ++$i) {
            if ($script_name == $this->script_table[$i]) {
                return $i;
            }
        }
        return 0;  // 0 is always the null_script
    }

/*
    // Return the (current) number of scripts in the script table
    int get_script_table_size() const {
    return script_table_size_used;
    }

    // Return the script string from its id
    const char* get_script_from_script_id(int id) const {
    if (id >= script_table_size_used || id < 0)
    return null_script;
    return script_table[id];
    }



    // Return true if the given script is the null script
    bool is_null_script(const char* script) const {
    return script == null_script;
    }
	*/

    // Uniquify the given script. For two scripts a and b, if strcmp(a, b) == 0,
    // then the returned pointer will be the same.
    // The script parameter is copied and thus can be a temporary.
    public function add_script(/* const char*  */$script) {
        for ($i = 0; $i < $this->script_table_size_used; ++$i) {
            if (isset($this->script_table[$i]) && $script == $this->script_table[$i]) {
                return $i;
            }
        }

        if ($this->script_table_size_reserved == 0) {
            $this->script_table_size_reserved = 8;
            $this->script_table = array(); //new char*[script_table_size_reserved];
        }
        if ($this->script_table_size_used + 1 >= $this->script_table_size_reserved) {
            //$new_script_table = array(); //new char*[script_table_size_reserved * 2];
            $new_script_table = $this->script_table;
            unset($this->script_table);
            $this->script_table = $new_script_table;
            $this->script_table_size_reserved = 2 * $this->script_table_size_reserved;
        }
        $this->script_table[$this->script_table_size_used] = array();//new char[strlen(script) + 1];
        $this->script_table[$this->script_table_size_used] = $script;
        //strcpy(, script);
        return $this->script_table_size_used++;
    }

	/*
    // Return the enabled property of the given unichar.
    bool get_enabled(UNICHAR_ID unichar_id) const {
    return unichars[unichar_id].properties.enabled;
    }


    int null_sid() const { return null_sid_; }
    int common_sid() const { return common_sid_; }
    int latin_sid() const { return latin_sid_; }
    int cyrillic_sid() const { return cyrillic_sid_; }
    int greek_sid() const { return greek_sid_; }
    int han_sid() const { return han_sid_; }
    int hiragana_sid() const { return hiragana_sid_; }
    int katakana_sid() const { return katakana_sid_; }
    int default_sid() const { return default_sid_; }

    // Returns true if the unicharset has the concept of upper/lower case.
    bool script_has_upper_lower() const {
    return script_has_upper_lower_;
    }
    // Returns true if the unicharset has the concept of x-height.
    // script_has_xheight can be true even if script_has_upper_lower is not,
    // when the script has a sufficiently predominant top line with ascenders,
    // such as Devanagari and Thai.
    bool script_has_xheight() const {
    return script_has_xheight_;
    }

    private:

    struct UNICHAR_PROPERTIES {
    UNICHAR_PROPERTIES();
    void Init();

    bool  isalpha;
    bool  islower;
    bool  isupper;
    bool  isdigit;
    bool  ispunctuation;
    bool  isngram;
    bool  enabled;
    // Possible limits of the top and bottom of the bounding box in
    // baseline-normalized coordinates, ie, where the baseline is
    // kBlnBaselineOffset and the meanline is kBlnBaselineOffset + kBlnXHeight
    // (See normalis.h for the definitions).
    uinT8 min_bottom;
    uinT8 max_bottom;
    uinT8 min_top;
    uinT8 max_top;
    int   script_id;
    UNICHAR_ID other_case;  // id of the corresponding upper/lower case unichar

    // Contains meta information about the fragment if a unichar represents
    // a fragment of a character, otherwise should be set to NULL.
    // It is assumed that character fragments are added to the unicharset
    // after the corresponding 'base' characters.
    CHAR_FRAGMENT *fragment;
    };

    struct UNICHAR_SLOT {
    char representation[UNICHAR_LEN + 1];
    UNICHAR_PROPERTIES properties;
    }; */
}

class UnicharSlot {
    public $representation;
    public $properties;

    public function __construct() {
        $this->representation = array();
        $this->properties = new UnicharProperties();
    }
}


class UnicharProperties {
    public $isalpha = false;
    public $islower = false;
    public $isupper = false;
    public $isdigit = false;
    public $ispunctuation = false;
    public $isngram = false;
    public $enabled = false;

    // Possible limits of the top and bottom of the bounding box in
    // baseline-normalized coordinates, ie, where the baseline is
    // kBlnBaselineOffset and the meanline is kBlnBaselineOffset + kBlnXHeight
    // (See normalis.h for the definitions).
    public $min_bottom = 0;
    public $max_bottom = 0xff; //MAX_UINT8
    public $min_top = 0;
    public $max_top = 0xff; //MAX_UINT8
    public $script_id = 0;
    public $other_case = 0;  // id of the corresponding upper/lower case unichar

    // Contains meta information about the fragment if a unichar represents
    // a fragment of a character, otherwise should be set to NULL.
    // It is assumed that character fragments are added to the unicharset
    // after the corresponding 'base' characters.
    public $fragment = null;
}

