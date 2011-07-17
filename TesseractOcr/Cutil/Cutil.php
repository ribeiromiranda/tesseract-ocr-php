<?php
namespace TesseractOcr\Cutil;

use TesseractOcr\Ccutil\Ccutil;

require_once 'TesseractOcr/Ccutil/CCUtil.php';

abstract class Cutil extends Ccutil {

    abstract public function read_variables(/*const char **/$filename, /*bool*/ $global_only);

}