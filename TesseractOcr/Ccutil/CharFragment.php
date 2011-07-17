<?php
namespace TesseractOcr\Ccutil;

require_once 'TesseractOcr/Ccutil/Unichar.php';

class CharFragment {

    // Minimum number of characters used for fragment representation.
    const kMinLen = 6;
    // Maximum number of characters used for fragment representation.
    const kMaxLen =  29; // UNICHAR_LEN  //3 + UNICHAR_LEN + 2;
    // Special character used in representing character fragments.
    const kSeparator = '|';
    // Maximum number of fragments per character.
    const kMaxChunks = 3;


    private $unichar;// [UNICHAR_LEN + 1];
    private $pos;    // fragment position in the character
    private $total;  // total number of fragments in the character

    // Setters and Getters.
    public function set_all(/* const char * */$unichar, /* int  */$pos, /* int  */$total) {
        $this->set_unichar($unichar);
        $this->set_pos($pos);
        $this->set_total($total);
    }
    public function set_unichar(/* const char * */$uch) {
        $this->unichar = $uch;
        $this->unichar[Unichar::UNICHAR_LEN] = '\0';
    }
    public function set_pos($p) {
        $this->pos = $p;
    }
    public function set_total($t) {
        $this->total = $t;
    }
    //inline const char*
    public function get_unichar() {
        return $this->unichar;
    }
    //inline int
    public function get_pos() {
        return $this->pos;
    }
    //inline int
    public function get_total() {
        return $this->total;
    }

    // Returns the string that represents a fragment
    // with the given unichar, pos and total.
    //static STRING
    public static function sto_string(/* const char * */$unichar, /* int  */$pos, /* int  */$total) {
        if ($total == 1) {
            return $unichar;
        }
        $result = "";
        $result .= self::kSeparator;
        $result .= $unichar;
        $buffer = sprintf("%s%d%s%d", self::kSeparator, $pos, self::kSeparator, $total);
        if (strlen($buffer) > 29) {
            throw new \Exception("Maior que 29");
        }
        $buffer = substr($buffer, 0, self::kMaxLen);
        $result .= $buffer;
        return $result;
    }
    // Returns the string that represents this fragment.
    public function to_string() {
        return self::sto_string($this->unichar, $this->pos, $this->total);
    }

    // Checks whether a fragment has the same unichar,
    // position and total as the given inputs.
    //inline bool
    public function equals(/* const char * */$other_unichar,
    /* int  */$other_pos = null, /* int  */$other_total = null) {
        if ($other_pos === null) {
            $other_pos = $this->get_pos();
        }
        if ($other_total === null) {
            $other_total = $this->get_pos();
        }
        return ($this->unichar == $other_unichar &&
            $this->pos == $other_pos && $this->total == $other_total);
    }

    // Checks whether a given fragment is a continuation of this fragment.
    // Assumes that the given fragment pointer is not NULL.
    //inline bool
    public function is_continuation_of(/* const CHAR_FRAGMENT * */$fragment) {
        return $this->unichar === $fragment->get_unichar() &&
        $this->total == $fragment->get_total() &&
        $this->pos == $fragment->get_pos() + 1;
    }

    // Returns true if this fragment is a beginning fragment.
    //inline bool
    public function is_beginning() {
        return $this->pos == 0;
    }

    // Returns true if this fragment is an ending fragment.
    //inline bool
    public function is_ending() {
        return $this->pos == $this->total-1;
    }

    // Parses the string to see whether it represents a character fragment
    // (rather than a regular character). If so, allocates memory for a new
    // CHAR_FRAGMENT instance and fills it in with the corresponding fragment
    // information. Fragments are of the form:
    // |m|1|2, meaning chunk 1 of 2 of character m.
    //
    // If parsing succeeded returns the pointer to the allocated CHAR_FRAGMENT
    // instance, otherwise (if the string does not represent a fragment or it
    // looks like it does, but parsing it as a fragment fails) returns NULL.
    //
    // Note: The caller is responsible for deallocating memory
    // associated with the returned pointer.
    //static CHAR_FRAGMENT *
    public static function parse_from_string(/* const char * */$string) {
        $ptr = str_split($string);
        $len = count($string);
        if ($len < self::kMinLen || $ptr[0] != self::kSeparator) {
            return null;  // this string can not represent a fragment
        }
        throw new Exception("Movitando pelo ponteiro");
        array_shift($ptr);
        $ptr++;  // move to the next character
        $step = 0;
        while (($ptr + $step) < ($string + $len) && /* * */($ptr + $step) != self::kSeparator) {
            $step += Unichar::utf8_step($ptr + $step);
        }
        if ($step == 0 || $step > Unichar::UNICHAR_LEN) {
            return NULL;  // no character for unichar or the character is too long
        }
        $unichar = $ptr; //[Unichar::UNICHAR_LEN + 1];
        //strncpy(unichar, ptr, step);
        $unichar[$step] = '\0';  // null terminate unichar
        $ptr += $step;  // move to the next fragment separator
        $pos = 0;
        $total = 0;
        $end_ptr = NULL;
        for ($i = 0; $i < 2; $i++) {
            if ($ptr > $string + $len || /* * */$ptr != self::kSeparator) {
                return NULL;  // failed to parse fragment representation
            }
            $ptr++;  // move to the next character
            $i == 0 ? $pos = strtol($ptr, $end_ptr, 10)
            : $total = strtol($ptr, $end_ptr, 10);
            $ptr = $end_ptr;
        }
        if ($ptr != $string + $len) {
            return NULL;  // malformed fragment representation
        }
        $fragment = new CharFragment();
        $fragment->set_all($unichar, $pos, $total);
        return $fragment;
    }
}