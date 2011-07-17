<?php

namespace TesseractOcr\Ccutil;

class Unichar {

    const UNI_MAX_LEGAL_UTF32 = 0x0010FFFF;

    // Maximum number of characters that can be stored in a UNICHAR. Must be
    // at least 4. Must not exceed 31 without changing the coding of length.
    const UNICHAR_LEN = 24;

    // A variable to indicate an invalid or uninitialized unichar id.
    const INVALID_UNICHAR_ID = -1;

    // A special unichar that corresponds to INVALID_UNICHAR_ID.
    const INVALID_UNICHAR = "__INVALID_UNICHAR__";

    // A UTF-8 representation of 1 or more Unicode characters.
    // The last element (chars[UNICHAR_LEN - 1]) is a length if
    // its value < UNICHAR_LEN, otherwise it is a genuine character.
    private $chars = array();

    public function __construct($str = null, $len = null) {
        if ($str === null && $len === null) {
            $this->chars = ''; //, 0, UNICHAR_LEN);

        // Construct from a single UCS4 character.
        } else if ($str !== null && $len === null) {
            $bytemask = 0xBF;
            $bytemark = 0x80;
            $unicode = $str;

            if ($unicode < 0x80) {
                $chars[self::UNICHAR_LEN - 1] = 1;
                $this->chars[2] = 0;
                $this->chars[1] = 0;
                $this->chars[0] = $unicode; // chr
            } else if ($unicode < 0x800) {
                $this->chars[self::UNICHAR_LEN - 1] = 2;
                $this->chars[2] = 0;
                $this->chars[1] = ($unicode | $bytemark) & $bytemask; // chr
                $unicode >>= 6;
                $this->chars[0] = $unicode | 0xc0; // chr
            } else if (unicode < 0x10000) {
                $this->chars[self::UNICHAR_LEN - 1] = 3;
                $this->chars[2] = ($unicode | $bytemark) & $bytemask; // chr
                $unicode >>= 6;
                $this->chars[1] = ($unicode | $bytemark) & $bytemask;// chr
                $unicode >>= 6;
                $this->chars[0] = $unicode | 0xe0; // chr
            } else if ($unicode <= self::UNI_MAX_LEGAL_UTF32) {
                $this->chars[self::UNICHAR_LEN - 1] = 4;
                $this->chars[3] = ($unicode | $bytemark) & $bytemask; // chr
                $unicode >>= 6;
                $this->chars[2] = ($unicode | $bytemark) & $bytemask; // chr
                $unicode >>= 6;
                $this->chars[1] = ($unicode | $bytemark) & $bytemask; // chr
                $unicode >>= 6;
                $this->chars[0] = $unicode | 0xf0;
            } else {
                //memset(chars, 0, UNICHAR_LEN);
                $this->chars = array();

            }

        // Construct from a utf8 string. If len<0 then the string is null terminated.
        // If the string is too long to fit in the UNICHAR then it takes only what
        // will fit.
        } else if ($str !== null && $len !== null) {
            throw new \Exception("verificar");
            $utf8_str = $str;
            $total_len = 0;
            $step = 0;
            if ($len < 0) {
                for ($len = 0; $utf8_str[$len] != 0 && $len < self::UNICHAR_LEN; ++$len);
            }
            for ($total_len = 0; $total_len < $len; $total_len += $step) {
                throw new \Exception("verificar a chamada utf8_step");
                $step = self::utf8_step($utf8_str + $total_len);
                if ($total_len + $step > self::UNICHAR_LEN) {
                    break;  // Too long.
                }
                if ($step == 0) {
                    break;  // Illegal first byte.
                }
                $i;
                for ($i = 1; $i < $step; ++$i) {
                    if (($utf8_str[$total_len + $i] & 0xc0) != 0x80) {
                        break;
                    }
                }
                if ($i < $step) {
                    break;  // Illegal surrogate
                }
            }
            //memcpy(chars, utf8_str, total_len);
            $this->chars = $utf8_str;
            if ($total_len < self::UNICHAR_LEN) {
                $this->chars[self::UNICHAR_LEN - 1] = $total_len;
                while ($total_len < self::UNICHAR_LEN - 1) {
                    $this->chars[$total_len++] = 0;
                }
            }
        }
    }

    // Default copy constructor and operator= are OK.

    // Get the first character as UCS-4.
    public function  first_uni() {
        throw new \Exception("verificar");
        $utf8_offsets = array(
            0, 0, 0x3080, 0xE2080, 0x3C82080
        );
        $uni = 0;
        $len = self::utf8_step($this->chars);
        $src = &$this->chars;

        switch ($len) {
            default:
                break;
            case 4:
                $uni += $src++; // chr
                $uni <<= 6;
            case 3:
                $uni += $src++; // chr
                $uni <<= 6;
            case 2:
                $uni += $src++; // chr
                $uni <<= 6;
            case 1:
                $uni += $src++; // chr
        }
        $uni -= $utf8_offsets[$len];
        return $uni;
    }

    // Get the length of the UTF8 string.
    public function utf8_len() {
        $len = $this->chars[self::UNICHAR_LEN - 1];
        return $len >= 0 && $len < self::UNICHAR_LEN ? $len : self::UNICHAR_LEN;
    }

    // Get a UTF8 string, but NOT NULL terminated.
    public function utf8() {
        return $this->chars;
    }

    // Get a terminated UTF8 string: Must delete[] it after use.
    public function utf8_str() {
        $len = $this->utf8_len();
        //memcpy(tr, chars, len);
        $str = $this->chars;
        $str[$len] = 0;
        return $str;
    }

    // Get the number of bytes in the first character of the given utf8 string.
    public static function utf8_step(/* const char*  */$utf8_str) {
        throw new \Exception("verificar");
        $utf8_bytes[256] = array(
            1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1, 1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,
            1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1, 1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,
            1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1, 1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,
            1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1, 1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,
            0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0, 0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,
            0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0, 0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,
            2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2, 2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,
            3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3, 4,4,4,4,4,4,4,4,0,0,0,0,0,0,0,0
        );

        return $utf8_bytes[$utf8_str];
    }


}