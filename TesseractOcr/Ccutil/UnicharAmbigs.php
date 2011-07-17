<?php
namespace TesseractOcr\Ccutil;

require_once 'TesseractOcr/Ccutil/AmbigType.php';
require_once 'TesseractOcr/Ccutil/AmbigSpec.php';

class UnicharAmbigs {

    const MAX_AMBIG_SIZE = 10;

    const kUnigramAmbigsBufferSize = 1000;
    public static $kAmbigNgramSeparator = array(' ', '\0' );
    const kAmbigDelimiters = "\t ";
    const kIllegalMsg = "Illegal ambiguity specification on line %d\n";
    const kIllegalUnicharMsg = "Illegal unichar %s in ambiguity specification\n";

    //UnicharAmbigsVector
    private $dang_ambigs_ = array();

    //UnicharAmbigsVector
    private $replace_ambigs_ = array();

    //GenericVector<UnicharIdVector *>
    private $one_to_one_definite_ambigs_;

    //GenericVector<UnicharIdVector *>
    private $ambigs_for_adaption_;

    //GenericVector<UnicharIdVector *>
    private $reverse_ambigs_for_adaption_;

    public function __construct() {
    }
    public function __destruct() {
        /* replace_ambigs_.delete_data_pointers();
        dang_ambigs_.delete_data_pointers();
        one_to_one_definite_ambigs_.delete_data_pointers(); */
    }

    //const UnicharAmbigsVector &
    public function dang_ambigs() {
        return $this->dang_ambigs_;
    }
    //const UnicharAmbigsVector &
    public function replace_ambigs() {
        return $this->replace_ambigs_;
    }

    // Fills in two ambiguity tables (replaceable and dangerous) with information
    // read from the ambigs file. An ambiguity table is an array of lists.
    // The array is indexed by a class id. Each entry in the table provides
    // a list of potential ambiguities which can start with the corresponding
    // character. For example the ambiguity "rn -> m", would be located in the
    // table at index of unicharset.unichar_to_id('r').
    // In 1-1 ambiguities (e.g. s -> S, 1 -> I) are recorded in
    // one_to_one_definite_ambigs_. This vector is also indexed by the class id
    // of the wrong part of the ambiguity and each entry contains a vector of
    // unichar ids that are ambiguous to it.
    public function LoadUnicharAmbigs(/* FILE * */$AmbigFile, /* inT64  */$end_offset, /* int  */$debug_level,
    /* bool  */$use_ambigs_for_adaption,  Unicharset $unicharset) {

        $i; $j;
        /* UnicharIdVector * */
        $adaption_ambigs_entry = array();
        for ($i = 0; $i < $unicharset->size(); ++$i) {
            $this->replace_ambigs_[] = null;
            $this->dang_ambigs_[] = null;
            $this->one_to_one_definite_ambigs_[] = null;
            if ($use_ambigs_for_adaption) {
                $this->ambigs_for_adaption_[] = null;
                $this->reverse_ambigs_for_adaption_[] = null;
            }
        }

        if ($debug_level) {
            tprintf("Reading ambiguities\n");
        }


        $TestAmbigPartSize;
        $ReplacementAmbigPartSize;
        // Maximum line size:
        //   10 for sizes of ambigs, tabs, abmig type and newline
        //   UNICHAR_LEN * (MAX_AMBIG_SIZE + 1) for each part of the ambig
        // The space for buffer is allocated on the heap to avoid
        // GCC frame size warning.
        $kMaxAmbigStringSize = Unichar::UNICHAR_LEN * (self::MAX_AMBIG_SIZE + 1);
        $kBufferSize = 10 + 2 * $kMaxAmbigStringSize;
        $buffer = new \SplFixedArray($kBufferSize);
        $ReplacementString = array(); //[kMaxAmbigStringSize];
        $TestUnicharIds = array(); //[MAX_AMBIG_SIZE + 1];
        $line_num = 0;
        $type = AmbigType::NOT_AMBIG();

        // Determine the version of the ambigs file.
        $version = 0;
        $buffer = fgets($AmbigFile, $kBufferSize); //!= NULL && strlen(buffer) > 0

        if ($buffer === false) {
            throw new \Excetion("AmbigFile");
        }
        //ASSERT_HOST(fgets(buffer, kBufferSize, AmbigFile) != NULL && strlen(buffer) > 0);

        if ($buffer[0] == 'v') {
            $version = intval($buffer[1]);
            ++$line_num;
        } else {
            rewind($AmbigFile);
        }



        while (($end_offset < 0 || ftell($AmbigFile) < $end_offset) &&
            ($buffer = fgets($AmbigFile, $kBufferSize)) !== false) {
            Helpers::chomp_string($buffer);
            if ($debug_level > 2) {
                tprintf("read line %s\n", buffer);
            }
            ++$line_num;

            if (!$this->ParseAmbiguityLine($line_num, $version, $debug_level, $unicharset,
                $buffer, $TestAmbigPartSize, $TestUnicharIds,
                $ReplacementAmbigPartSize,
                $ReplacementString, $type)) {
                continue;
            }

            // Construct AmbigSpec and add it to the appropriate AmbigSpec_LIST.
            $ambig_spec = new AmbigSpec();
            if ($type === AmbigType::REPLACE_AMBIG()) {
                $param = &$this->replace_ambigs_;
            } else {
                $param = &$this->dang_ambigs_;
            }

            $this->InsertIntoTable($param,
                $TestAmbigPartSize, $TestUnicharIds,
                $ReplacementAmbigPartSize, $ReplacementString, $type,
                $ambig_spec, $unicharset);
            exit;




            // Update one_to_one_definite_ambigs_.
            if (TestAmbigPartSize == 1 &&
            $ReplacementAmbigPartSize == 1 && $type === AmbigType::DEFINITE_AMBIG()) {
                if (empty($this->one_to_one_definite_ambigs_[$TestUnicharIds[0]])) {
                    $this->one_to_one_definite_ambigs_[$TestUnicharIds[0]] = new UnicharIdVector();
                }
                $this->one_to_one_definite_ambigs_[$TestUnicharIds[0]][] = $ambig_spec->correct_ngram_id;
            }
            // Update ambigs_for_adaption_.
            if ($use_ambigs_for_adaption) {
                for ($i = 0; $i < $TestAmbigPartSize; ++$i) {
                    if (empty($this->ambigs_for_adaption_[$TestUnicharIds[$i]])) {
                        $this->ambigs_for_adaption_[$TestUnicharIds[$i]] = new UnicharIdVector();
                    }
                    $adaption_ambigs_entry = $this->ambigs_for_adaption_[$TestUnicharIds[$i]];
                    $tmp_ptr = $ReplacementString;
                    throw new \Exception("ponteiro somar");
                    $tmp_ptr_end = $ReplacementString + strlen($ReplacementString);
                    $step = $unicharset->step($tmp_ptr);
                    while ($step > 0) {
                        $id_to_insert = $unicharset->unichar_to_id(tmp_ptr, step);
                        //ASSERT_HOST(id_to_insert != INVALID_UNICHAR_ID);
                        // Add the new unichar id to adaption_ambigs_entry (only if the
                        // vector does not already contain it) keeping it in sorted order.
                        for ($j = 0; $j < $adaption_ambigs_entry->size() &&
                            $adaption_ambigs_entry[$j] > $id_to_insert; ++$j);
                        if ($j < $adaption_ambigs_entry->size()) {
                            if ($adaption_ambigs_entry[$j] != $id_to_insert) {
                                $adaption_ambigs_entry->insert($id_to_insert, j);
                            }
                        } else {
                            $adaption_ambigs_entry[] = $id_to_insert;
                        }
                        // Update tmp_ptr and step.
                        $tmp_ptr += $step;
                        $step = $tmp_ptr < $tmp_ptr_end ? $unicharset->step($tmp_ptr) : 0;
                    }
                }
            }
        }
        unset($buffer);

        // Fill in reverse_ambigs_for_adaption from ambigs_for_adaption vector.
        if ($use_ambigs_for_adaption) {
            for ($i = 0; $i < count($this->ambigs_for_adaption_); ++$i) {
                $adaption_ambigs_entry = $this->ambigs_for_adaption_[$i];
                if ($adaption_ambigs_entry == NULL) {
                    continue;
                }
                for ($j = 0; $j < $adaption_ambigs_entry->size(); ++$j) {
                    $ambig_id = $adaption_ambigs_entry[$j];
                    if (empty($this->reverse_ambigs_for_adaption_[$ambig_id])) {
                        $this->reverse_ambigs_for_adaption_[$ambig_id] = array();//new UnicharIdVector();
                    }
                    $this->reverse_ambigs_for_adaption_[$ambig_id][] = $i;
                }
            }
        }

        // Print what was read from the input file.
        if ($debug_level > 1) {
            for ($tbl = 0; $tbl < 2; ++$tbl) {
                $print_table = ($tbl == 0) ? $this->replace_ambigs_ : $this->dang_ambigs_;
                for ($i = 0; $i < $print_table.size(); ++$i) {
                    $lst = $print_table[$i];
                    if ($lst == NULL) {
                        continue;
                    }
                    if (!$lst->empty()) {
                        tprintf("%s Ambiguities for %s:\n",
                        ($tbl == 0) ? "Replaceable" : "Dangerous", $unicharset->debug_str($i));
                    }
                    throw new \Exception("verificar lst_it");
                    //AmbigSpec_IT lst_it(lst);
                    for ($lst_it.mark_cycle_pt(); !$lst_it.cycled_list(); $lst_it.forward()) {
                        $ambig_spec = $lst_it.data();
                        tprintf("wrong_ngram:");
                        UnicharIdArrayUtils::printt($ambig_spec->wrong_ngram, $unicharset);
                        tprintf("correct_fragments:");
                        UnicharIdArrayUtils::printt($ambig_spec->correct_fragments, $unicharset);
                    }
                }
            }
            if ($use_ambigs_for_adaption) {
                for ($vec_id = 0; $vec_id < 2; ++$vec_id) {
                    $vec = ($vec_id == 0) ?
                        $this->ambigs_for_adaption_ : $this->reverse_ambigs_for_adaption_;
                    for ($i = 0; $i < $vec.size(); ++$i) {
                        $adaption_ambigs_entry = $vec[$i];
                        if ($adaption_ambigs_entry != NULL) {
                            tprintf("%sAmbigs for adaption for %s:\n",
                            ($vec_id == 0) ? "" : "Reverse ",
                                $unicharset->debug_str($i));
                            for ($j = 0; $j < $adaption_ambigs_entry->size(); ++$j) {
                                tprintf("%s ", $unicharset->debug_str(
                                $adaption_ambigs_entry[$j]));
                            }
                            tprintf("\n");
                        }
                    }
                }
            }
        }
    }

    // Returns definite 1-1 ambigs for the given unichar id.
    //inline const UnicharIdVector *
    public function OneToOneDefiniteAmbigs($unichar_id) {
        if (empty($this->one_to_one_definite_ambigs_)) {
            return null;
        }
        return $this->one_to_one_definite_ambigs_[$unichar_id];
    }

    // Returns a pointer to the vector with all unichar ids that appear in the
    // 'correct' part of the ambiguity pair when the given unichar id appears
    // in the 'wrong' part of the ambiguity. E.g. if DangAmbigs file consist of
    // m->rn,rn->m,m->iii, UnicharAmbigsForAdaption() called with unichar id of
    // m will return a pointer to a vector with unichar ids of r,n,i.
    //inline const UnicharIdVector *
    public function AmbigsForAdaption($unichar_id) {
        if (empty($this->ambigs_for_adaption_)) {
            return NULL;
        }
        return $this->ambigs_for_adaption_[$unichar_id];
    }

    // Similar to the above, but return the vector of unichar ids for which
    // the given unichar_id is an ambiguity (appears in the 'wrong' part of
    // some ambiguity pair).
    //inline const UnicharIdVector *
    public function ReverseAmbigsForAdaption($unichar_id) {
        if (empty($this->reverse_ambigs_for_adaption_)) {
            return NULL;
        }
        return $this->reverse_ambigs_for_adaption_[$unichar_id];
    }

    //bool
    private function ParseAmbiguityLine(/* int */ $line_num, /* int */ $version, /* int  */$debug_level,
        Unicharset $unicharset, /* char * */$buffer,
    /* int * */&$TestAmbigPartSize, /* UNICHAR_ID * */&$TestUnicharIds,
    /* int * */&$ReplacementAmbigPartSize,
    /* char * */&$ReplacementString, AmbigType &$type) {
        $i;
        $token;
        $next_token;
        $token = strtok($buffer, self::kAmbigDelimiters);
        if ($token === false ||
            !sscanf($token, "%d", $TestAmbigPartSize) || $TestAmbigPartSize <= 0) {
            if ($debug_level) {
                tprintf(self::kIllegalMsg, $line_num);
            }
            return false;
        }

        if ($TestAmbigPartSize > self::MAX_AMBIG_SIZE) {
            tprintf("Too many unichars in ambiguity on line %d\n");
            return false;
        }

        for ($i = 0; $i < $TestAmbigPartSize; ++$i) {
            if (($token = strtok(self::kAmbigDelimiters)) === false) {
                break;
            }

            if (!$unicharset->contains_unichar($token)) {
                if ($debug_level) {
                    tprintf(self::kIllegalUnicharMsg, $token);
                }
                break;
            }
            $TestUnicharIds[$i] = $unicharset->unichar_to_id($token);
        }

        $TestUnicharIds[$i] = Unichar::INVALID_UNICHAR_ID;



        if ($i != $TestAmbigPartSize ||
            ($token = strtok(self::kAmbigDelimiters)) === false ||
            !sscanf($token, "%d", $ReplacementAmbigPartSize) ||
            $ReplacementAmbigPartSize <= 0) {

            if ($debug_level) {
                tprintf(kIllegalMsg, line_num);
            }
            return false;
        }

        if ($ReplacementAmbigPartSize > self::MAX_AMBIG_SIZE) {
            tprintf("Too many unichars in ambiguity on line %d\n");
            return false;
        }

        $ReplacementString = '';
        for ($i = 0; $i < $ReplacementAmbigPartSize; ++$i) {
            if (($token = strtok(self::kAmbigDelimiters)) === false) {
                break;
            }

            $ReplacementString .= $token;
            if (!$unicharset->contains_unichar($token)) {
                if ($debug_level) {
                    tprintf(kIllegalUnicharMsg, token);
                }
                break;
            }
        }

        if ($i != $ReplacementAmbigPartSize) {
            if ($debug_level) {
                tprintf($kIllegalMsg, $line_num);
            }
            return false;
        }
        if ($version > 0) {
            // The next field being true indicates that the abiguity should
            // always be substituted (e.g. '' should always be changed to ").
            // For such "certain" n -> m ambigs tesseract will insert character
            // fragments for the n pieces in the unicharset. AmbigsFound()
            // will then replace the incorrect ngram with the character
            // fragments of the correct character (or ngram if m > 1).
            // Note that if m > 1, an ngram will be inserted into the
            // modified word, not the individual unigrams. Tesseract
            // has limited support for ngram unichar (e.g. dawg permuter).
            if (($token = strtok(self::kAmbigDelimiters)) === false ||
                !sscanf($token, "%d", $type)) {
                if ($debug_level) {
                    tprintf(self::kIllegalMsg, $line_num);
                }
                return false;
            }

            $type = AmbigType::getName('\TesseractOcr\Ccutil\AmbigType', $type);
            $type = AmbigType::$type();
        }

        return true;
    }

    private function InsertIntoTable(/* UnicharAmbigsVector & */&$table,
    /* int  */$TestAmbigPartSize, /* UNICHAR_ID * */$TestUnicharIds,
    /* int  */$ReplacementAmbigPartSize,
    /* const char * */$ReplacementString, /* int  */$type,
    /* AmbigSpec * */$ambig_spec, Unicharset $unicharset) {

        $ambig_spec->type = $type;
        if ($TestAmbigPartSize == 1 && $ReplacementAmbigPartSize == 1 &&
            $unicharset->to_lower($TestUnicharIds[0]) ==
            $unicharset->to_lower($unicharset->unichar_to_id($ReplacementString))) {

            $ambig_spec->type = AmbigType::CASE_AMBIG();
        }

        $ambig_spec->wrong_ngram = $TestUnicharIds;
        $ambig_spec->wrong_ngram_size = count($TestUnicharIds);

        // Since we need to maintain a constant number of unichar positions in
        // order to construct ambig_blob_choices vector in NoDangerousAmbig(), for
        // each n->m ambiguity we will have to place n character fragments of the
        // correct ngram into the corresponding positions in the vector (e.g. given
        // "vvvvw" and vvvv->ww we will place v and |ww|0|4 into position 0, v and
        // |ww|1|4 into position 1 and so on. The correct ngram is reconstructed
        // from fragments by dawg_permute_and_select().

        // Insert the corresponding correct ngram into the unicharset.
        // Unicharset code assumes that the "base" ngram is inserted into
        // the unicharset before fragments of this ngram are inserted.
        $unicharset->unichar_insert($ReplacementString);
        $ambig_spec->correct_ngram_id = $unicharset->unichar_to_id($ReplacementString);
        if ($ReplacementAmbigPartSize > 1) {
            $unicharset->set_isngram($ambig_spec->correct_ngram_id, true);
        }
        // Add the corresponding fragments of the wrong ngram to unicharset.

        for ($i = 0; $i < $TestAmbigPartSize; ++$i) {
            if ($TestAmbigPartSize == 1) {
                $unichar_id = $ambig_spec->correct_ngram_id;
            } else {
                $frag_str = CharFragment::sto_string(
                    $ReplacementString, $i, $TestAmbigPartSize);
                $unicharset->unichar_insert($frag_str);
                $unichar_id = $unicharset->unichar_to_id($frag_str);
            }
            $ambig_spec->correct_fragments[$i] = $unichar_id;
        }

        $ambig_spec->correct_fragments[$i] = Unichar::INVALID_UNICHAR_ID;

        // Add AmbigSpec for this ambiguity to the corresponding AmbigSpec_LIST.
        // Keep AmbigSpec_LISTs sorted by AmbigSpec.wrong_ngram.
        if (empty($table[$TestUnicharIds[0]])) {
            $table[$TestUnicharIds[0]] = array();
        }
        $table[$TestUnicharIds[0]]->add_sorted(
            AmbigSpec::compare_ambig_specs, false, $ambig_spec);
    }
}