<?php

namespace TesseractOcr\Ccutil;



// Utility functions for working with Tesseract parameters.
class ParamUtils {

    // Reads a file of parameter definitions and set/modify the values therein.
    // If the filename begins with a + or -, the BoolVariables will be
    // ORed or ANDed with any current values.
    // Blank lines and lines beginning # are ignored.
    // Values may have any whitespace after the name and are the rest of line.
    public static function ReadParamsFile(
    /* const char * */$file,   // filename to read
    /* bool  */$init_only,     // only set parameters that need to be
    // initialized when Init() is called
    /* ParamsVectors * */$member_params) {

    }

    // Read parameters from the given file pointer (stop at end_offset).
    //static bool
    public static function ReadParamsFromFp(/* FILE * */$fp, /* inT64  */$end_offset, /* bool  */$init_only,
    ParamsVectors $member_params) {
        $line[MAX_PATH];           // input line
        $anyerr = false;           // true if any error
        $foundit;                  // found parameter
        $length;                  // length of line
        $valptr;                  // value field

        while (($end_offset < 0 || ftell($fp) < $end_offset) && fgets($line, MAX_PATH, $fp)) {
            if ($line[0] != '\n' && $line[0] != '#') {
                $length = strlen($line);
                if ($line[$length - 1] == '\n')
                $line[length - 1] = '\0';  // cut newline
                for ($valptr = $line; $valptr && $valptr != ' ' && $valptr != '\t';
                $valptr++);
                if ($valptr) {
                    // found blank
                    $valptr = '\0';          // make name a string
                    do
                    $valptr++;              // find end of blanks
                    while ($valptr == ' ' || $valptr == '\t');
                }
                $foundit = self::SetParam($line, $valptr, $init_only, $member_params);

                if (!$foundit) {
                    $anyerr = true;         // had an error
                    tprintf("read_params_file: parameter not found: %s\n", line);
                    exit(1);
                }
            }
        }
        return $anyerr;
    }

    // Set a parameters to have the given value.
    //static bool
    public static function SetParam(/* const char * */$name, /* const char* */ $value,
    /* bool  */$init_only, ParamsVectors $member_params) {

    }

    // Returns the pointer to the parameter with the given name (of the
    // appropriate type) if it was found in the vector obtained from
    // GlobalParams() or in the given member_params.
/*     template<class T>
    static T *FindParam(const char *name,
    const GenericVector<T *> &global_vec,
    const GenericVector<T *> &member_vec) {
        int i;
        for (i = 0; i < global_vec.size(); ++i) {
            if (strcmp(global_vec[i]->name_str(), name) == 0) return global_vec[i];
        }
        for (i = 0; i < member_vec.size(); ++i) {
            if (strcmp(member_vec[i]->name_str(), name) == 0) return member_vec[i];
        }
        return NULL;
    }
*/
    // Removes the given pointer to the param from the given vector.
    //template<class T>
    //static void
    public static function RemoveParam(/* T * */$param_ptr, /* GenericVector<T *> * */$vec) {
/*         for (int i = 0; i < vec->size(); ++i) {
            if ((*vec)[i] == param_ptr) {
                vec->remove(i);
                return;
            }
        } */
    }
    // Fetches the value of the named param as a STRING. Returns false if not
    // found.
    //static bool
    public static function GetParamAsString(/* const char * */$name,
    /* const ParamsVectors*  */$member_params,
    $value) {

    }

    // Print parameters to the given file.
    //static void
    public static function PrintParams($fp, ParamsVectors $member_params) {

    }
};