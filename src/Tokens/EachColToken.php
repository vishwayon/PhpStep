<?php

/**
 * @link http://www.vishwayon.com/
 * @copyright Copyright (c) 2020 Vishwayon Software Pvt Ltd
 * @license As per LICENSE file distributed with this code
 */

namespace PhpStep\Tokens;

/**
 * An instance of EachCol
 * @author girish
 */
class EachColToken {
    
    public array $colCells = [];
    
    public function prop() : string {
        return $this->attrs[0];
    }
    
    public function colSpan() : int {
        if (isset($this->attrs[1])) {
            return $this->attrs[1];
        } else {
            return 1;
        }
    }
}
