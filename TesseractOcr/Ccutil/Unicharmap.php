<?php

namespace TesseractOcr\Ccutil;

require_once 'TesseractOcr/Ccutil/Unichar.php';

class Unicharmap {

    //UNICHARMAP_NODE*
    private $nodes = 0;

    // Insert the given unichar represention in the UNICHARMAP and associate it
    // with the given id. The length of the representation MUST be non-zero.
    public function insert(/* const char* const  */$unichar_repr, /* UNICHAR_ID  */$id) {
        $current_char = str_split($unichar_repr);
        $nodes = $this->nodes;
        $current_nodes_pointer = $nodes;

        //assert(*unichar_repr != '\0');
        //assert(id >= 0);
        $i = 0;
        do {
            if (! $current_nodes_pointer instanceof UnicharmapNode) {
                $current_nodes_pointer = new UnicharmapNode(256);
                foreach ($current_nodes_pointer as $key => $valor) {
                    $current_nodes_pointer[$key] = new UnicharmapNode(0);
                }
                if ($i === 0) {
                    $nodes = $current_nodes_pointer;
                }
                //new UnicharmapNode(); // UNICHARMAP_NODE[256];
            }

            //if (*(current_char + 1) == '\0') {
            if (empty($current_char[1])) {
                $current_nodes_pointer[ord($current_char[0])]->id = $id;
                $this->nodes = $nodes;
                return ;
            }
            reset($current_char);

            $current_nodes_pointer = &$current_nodes_pointer[ord($current_char[0])]->children;
            array_shift($current_char);
            $i++;
        } while (true);
    }

    // Return the id associated with the given unichar representation,
    // this representation MUST exist within the UNICHARMAP.
    // The length of the representation MUST be non-zero.
    //UNICHAR_ID
    public function unichar_to_id(/* const char* const  */$unichar_repr, $length = null) {
        $current_char = str_split($unichar_repr);
        $nodes = $this->nodes;
        $current_nodes = $nodes;

        if ($length === null) {
            do {
                if (empty($current_char[1])) {
                //if (*(current_char + 1) == '\0') {
                    return $current_nodes[ord($current_char[0])]->id;
                }

                $current_nodes = $current_nodes[ord($current_char[0])]->children;
                array_shift($current_char);
            } while (true);

            // Return the id associated with the given unichar representation,
            // this representation MUST exist within the UNICHARMAP. The first
            // length characters (maximum) from unichar_repr are used. The length
            // MUST be non-zero.
            //UNICHAR_ID
        }  else {
//            assert(*unichar_repr != '\0');
//            assert(length > 0 && length <= UNICHAR_LEN);

            do {
                if ($length == 1 || empty($current_char[1])) {
                //if (length == 1 || *(current_char + 1) == '\0') {
                    return $current_nodes[ord($current_char[0])]->id;
                }
                $current_nodes = $current_nodes[ord($current_char[0])]->children;
                ++$current_char;
                --$length;
            } while (true);
        }
    }

    // Return true if the given unichar representation is already present in the
    // UNICHARMAP. The length of the representation MUST be non-zero.
    public function contains(/* const char* const  */$unichar_repr, $length = null) {
        $current_char = str_split($unichar_repr);
        $current_nodes = $this->nodes;

        //assert(*unichar_repr != '\0');
        if ($length === null) {
            while ($current_nodes instanceof UnicharmapNode
                    && !empty($current_char[1])) {
                $current_nodes = $current_nodes[ord($current_char[0])]->children;
                array_shift($current_char);
            }

            return $current_nodes instanceof UnicharmapNode &&
                empty($current_char[1]) &&
                $current_nodes[ord($current_char[0])]->id >= 0;

        // Return true if the given unichar representation is already present in the
        // UNICHARMAP. The first length characters (maximum) from unichar_repr are
        // used. The length MUST be non-zero.
        } else {
            $current_char = $unichar_repr;
            $current_nodes = $this->nodes;

            //assert(*unichar_repr != '\0');
            //assert(length > 0 && length <= UNICHAR_LEN);

            while ($current_nodes != 0 && ($length > 1 && $current_char + 1 != '\0')) {
                $current_nodes =
                $current_nodes[$current_char]->children;
                --$length;
                ++$current_char;
            }


            return $current_nodes instanceof UnicharmapNode &&
                ($length == 1 || empty($current_char[1])) &&
                $current_nodes[ord($current_char[0])]->id >= 0;
        }
    }

    // Return the minimum number of characters that must be used from this string
    // to obtain a match in the UNICHARMAP.
    //int
    public function minmatch(/* const char* const  */$unichar_repr) {

    }

    // Clear the UNICHARMAP. All previous data is lost.
    public function clear() {
        if (! $this->nodes instanceof UnicharmapNode) {
            unset($this->nodes);
            $this->nodes = 0;
        }
    }
}

// The UNICHARMAP is represented as a tree whose nodes are of type
// UNICHARMAP_NODE.
class UnicharmapNode extends \SplFixedArray {

    public $children = 0;
    public $id = -1;

    public function __destruct() {
        if ($this->children instanceof self) {
            unset($this->children);
            $this->children = null;
        }
    }
}

