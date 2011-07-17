<?php
namespace TesseractOcr\Ccutil;

class TessdataManager {

    const kTrainedDataSuffix = 'traineddata';
    const kLangConfigFileSuffix = "config";
    const kUnicharsetFileSuffix = "unicharset";
    const kAmbigsFileSuffix = "unicharambigs";
    const kBuiltInTemplatesFileSuffix = "inttemp";
    const kBuiltInCutoffsFileSuffix = "pffmtable";
    const kNormProtoFileSuffix = "normproto";
    const kPuncDawgFileSuffix = "punc-dawg";
    const kSystemDawgFileSuffix = "word-dawg";
    const kNumberDawgFileSuffix = "number-dawg";
    const kFreqDawgFileSuffix = "freq-dawg";
    const kFixedLengthDawgsFileSuffix = "fixed-length-dawgs";
    const kCubeUnicharsetFileSuffix = "cube-unicharset";
    const kCubeSystemDawgFileSuffix = "cube-word-dawg";

    /**
    * kTessdataFileSuffixes[i] indicates the file suffix for
    * tessdata of type i (from TessdataType enum).
    */
    public static $kTessdataFileSuffixes = array(
      self::kLangConfigFileSuffix,        // 0
      self::kUnicharsetFileSuffix,        // 1
      self::kAmbigsFileSuffix,            // 2
      self::kBuiltInTemplatesFileSuffix,  // 3
      self::kBuiltInCutoffsFileSuffix,    // 4
      self::kNormProtoFileSuffix,         // 5
      self::kPuncDawgFileSuffix,          // 6
      self::kSystemDawgFileSuffix,        // 7
      self::kNumberDawgFileSuffix,        // 8
      self::kFreqDawgFileSuffix,          // 9
      self::kFixedLengthDawgsFileSuffix,  // 10
      self::kCubeUnicharsetFileSuffix,    // 11
      self::kCubeSystemDawgFileSuffix,    // 12
    );

    /**
    * If kTessdataFileIsText[i] is true - the tessdata component
    * of type i (from TessdataType enum) is text, and is binary otherwise.
    */
    public static $kTessdataFileIsText = array(
        true,                         // 0
        true,                         // 1
        true,                         // 2
        false,                        // 3
        true,                         // 4
        true,                         // 5
        false,                        // 6
        false,                        // 7
        false,                        // 8
        false,                        // 9
        false,                        // 10
        true,                         // 11
        false,                        // 12
    );

    /**
     * TessdataType could be updated to contain more entries, however
     * we do not expect that number to be astronomically high.
     * In order to automatically detect endianness TessdataManager will
     * flip the bits if actual_tessdata_num_entries_ is larger than
     * kMaxNumTessdataEntries.
     */
    const kMaxNumTessdataEntries = 1000;


    /**
    * Each offset_table_[i] contains a file offset in the combined data file
     * where the data of TessdataFileType i is stored.
    */
    //inT64
    private $offset_table_ = array();

    /**
    * Actual number of entries in the tessdata table. This value can only be
    * same or smaller than TESSDATA_NUM_ENTRIES, but can never be larger,
    * since then it would be impossible to interpret the type of tessdata at
    * indices same and higher than TESSDATA_NUM_ENTRIES.
    * This parameter is used to allow for backward compatiblity
    * when new tessdata types are introduced.
    */
    //inT32
    private $actual_tessdata_num_entries_;
    //FILE *
    private $data_file_;  ///< pointer to the data file.
    //int
    private $debug_level_;

    public function __construct() {
        $this->data_file_ = NULL;
        $this->actual_tessdata_num_entries_ = 0;
        for ($i = 0; $i < TessdataType::TESSDATA_NUM_ENTRIES()->value(); ++$i) {
            $this->offset_table_[$i] = -1;
        }
    }

    public function DebugLevel() {
        return $this->debug_level_;
    }

    /**
     * Opens the given data file and reads the offset table.
     * Returns true on success.
     */
    public function Init(/* const char * */$data_file_name, /* int  */$debug_level) {
        $i;
        $this->debug_level_ = $debug_level;
        $this->data_file_ = fopen($data_file_name, "rb");

        if ($this->data_file_ === false) {
            tprintf("Error opening data file %s\n", data_file_name);
            return false;
        }

        $this->actual_tessdata_num_entries_ = unpack("i", fread($this->data_file_, 4));
        $this->actual_tessdata_num_entries_ = $this->actual_tessdata_num_entries_[1];

        $swap = ($this->actual_tessdata_num_entries_ > self::kMaxNumTessdataEntries);
        if ($swap) {
            throw new \Exception("não foi testado");
            /**********************************************************************
            * reverse16
            *
            * Byte swap an inT16 or uinT16.
            **********************************************************************/

            $reverse16 = function(            //switch endian
            $num  //number to fix
            ) {
                return (($num & 0xff) << 8) | (($num >> 8) & 0xff);
            };

            $reverse32 = function(            //switch endian
            $num  //number to fix
            ) use ($reverse16) {
                return ($reverse16 (($num & 0xffff)) << 16)
                | $reverse16 ((($num >> 16) & 0xffff));
            };

            $this->actual_tessdata_num_entries_ = $reverse32($this->actual_tessdata_num_entries_);
        }



        for ($i = 0 ; $i < $this->actual_tessdata_num_entries_; ++$i) {
            $this->offset_table_[$i] = unpack("i", fread($this->data_file_, /*sizeof(inT64)*/8));
            $this->offset_table_[$i] = $this->offset_table_[$i][1];
        }

        if ($swap) {
            throw new \Exception("não foi testado");
            for ($i = 0 ; i < $this->actual_tessdata_num_entries_; ++$i) {
                $this->offset_table_[$i] = reverse64($this->offset_table_[$i]);
            }
        }
        if ($this->debug_level_) {
            tprintf("TessdataManager loaded %d types of tesseract data files.\n",
            $this->actual_tessdata_num_entries_);
            for ($i = 0; $i < $this->actual_tessdata_num_entries_; ++$i) {
                tprintf("Offset for type %d is %lld\n", i, $this->offset_table_[$i]);
            }
        }
        return true;
    }

    /** Returns data file pointer. */
    //inline FILE *
    public function GetDataFilePtr() {
        return $this->data_file_;
    }

    /**
     * Returns false if there is no data of the given type.
     * Otherwise does a seek on the data_file_ to position the pointer
     * at the start of the data of the given type.
     */
    //inline bool
     public function SeekToStart(TessdataType $tessdata_type) {
        if ($this->debug_level_) {
            tprintf("TessdataManager: seek to offset %lld - start of tessdata type %d (%s))\n", $this->offset_table_[$tessdata_type->value()],
            $tessdata_type, self::$kTessdataFileSuffixes[$tessdata_type->Value()]);
        }

        if ($this->offset_table_[$tessdata_type->value()] < 0) {
            return false;
        } else {
            return true;
        }
    }
    /** Returns the end offset for the given tesseract data file type. */
    //inline inT64
    public function GetEndOffset(TessdataType $tessdata_type) {
        $index = $tessdata_type->value() + 1;
        while ($index < $this->actual_tessdata_num_entries_ && $this->offset_table_[$index] == -1) {
            ++$index;  // skip tessdata types not present in the combined file
        }

        if ($this->debug_level_) {
            tprintf("TessdataManager: end offset for type %d is %lld\n",
            $tessdata_type->value(),
            ($index == $this->actual_tessdata_num_entries_) ? -1
            : $this->offset_table_[$index]);
        }
        return ($index == $this->actual_tessdata_num_entries_) ? -1 : $this->offset_table_[$index] - 1;
    }
    /** Closes data_file_ (if it was opened by Init()). */
    //inline void
    public function End() {
        if ($this->data_file_ != NULL) {
            fclose($this->data_file_);
            $this->data_file_ = NULL;
        }
    }

    /** Writes the number of entries and the given offset table to output_file. */
    //static void
    //public function WriteMetadata(inT64 *offset_table, FILE *output_file);

    /**
     * Reads all the standard tesseract config and data files for a language
     * at the given path and bundles them up into one binary data file.
     * Returns true if the combined traineddata file was successfully written.
     */
//     static bool CombineDataFiles(const char *language_data_path_prefix,
//     const char *output_filename);

    /**
     * Gets the individual components from the data_file_ with which the class was
     * initialized. Overwrites the components specified by component_filenames.
     * Writes the updated traineddata file to new_traineddata_filename.
     */
//     bool OverwriteComponents(const char *new_traineddata_filename,
//     char **component_filenames,
//     int num_new_components);

    /**
     * Extracts tessdata component implied by the name of the input file from
     * the combined traineddata loaded into TessdataManager.
     * Writes the extracted component to the file indicated by the file name.
     * E.g. if the filename given is somepath/somelang.unicharset, unicharset
     * will be extracted from the data loaded into the TessdataManager and will
     * be written to somepath/somelang.unicharset.
     * @return true if the component was successfully extracted, false if the
     * component was not present in the traineddata loaded into TessdataManager.
     */
//     bool ExtractToFile(const char *filename);

    /**
     * Copies data from the given input file to the output_file provided.
     * If num_bytes_to_copy is >= 0, only num_bytes_to_copy is copied from
     * the input file, otherwise all the data in the input file is copied.
     */
//     static void CopyFile(FILE *input_file, FILE *output_file,
//     bool newline_end, inT64 num_bytes_to_copy);

    /**
     * Fills type with TessdataType of the tessdata component represented by the
     * given file name. E.g. tessdata/eng.unicharset -> TESSDATA_UNICHARSET.
     * Sets *text_file to true if the component is in text format (e.g.
     * unicharset, unichar ambigs, config, etc).
     * @return true if the tessdata component type could be determined
     * from the given file name.
     */
//     static bool TessdataTypeFromFileSuffix(const char *suffix,
//     TessdataType *type,
//     bool *text_file);

    /**
     * Tries to determine tessdata component file suffix from filename,
     * returns true on success.
     */
//     static bool TessdataTypeFromFileName(const char *filename,
//     TessdataType *type,
//     bool *text_file);


    /**
     * Opens the file whose name is a concatenation of language_data_path_prefix
    * and file_suffix. Returns a file pointer to the opened file.
    */
    //static FILE *
//     private function GetFilePtr(const char *language_data_path_prefix,
//     const char *file_suffix, bool text_file);


}